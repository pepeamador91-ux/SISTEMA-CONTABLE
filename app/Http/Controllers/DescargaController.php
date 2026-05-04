<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Services\CfdiParserService; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class DescargaController extends Controller
{
    public function index()
    {
        // Carga de empresas para el select/buscador profesional
        $empresas = Empresa::orderBy('razon_social', 'asc')->get();
        $usuario = auth()->user();
        
        return view('descargas.index', compact('empresas', 'usuario'));
    }

    public function procesar(Request $request, CfdiParserService $parser)
    {    
        // 2. Validación de parámetros obligatorios
        if (!$request->empresa_id || !$request->tipo_comprobante) {
            return response()->json([
                'success' => false,
                'error' => 'Faltan parámetros obligatorios (Empresa o Tipo de Comprobante).'
            ], 422);
        }

        try {
            $empresa = Empresa::findOrFail($request->empresa_id);
            $metodo = $request->input('metodo_auth'); // 'ciec' o 'fiel'
            $rfc = strtoupper($empresa->rfc);

            // 3. Lógica de Autenticación Automática (Archivos y DB)
            if ($metodo === 'fiel') {
                // 3.1 Obtener contenidos (Prioridad: Archivo en Request > Archivo en Storage)
                $cerContent = null;
                $keyContent = null;

                // Procesar Certificado (.cer)
                if ($request->hasFile('fiel_cer')) {
                    $cerContent = file_get_contents($request->file('fiel_cer')->getRealPath());
                } elseif ($empresa->fiel_cer_path) {
                    $cerContent = Storage::disk('public')->get("certificados/{$rfc}/{$empresa->fiel_cer_path}");
                }

                // Procesar Llave (.key)
                if ($request->hasFile('fiel_key')) {
                    $keyContent = file_get_contents($request->file('fiel_key')->getRealPath());
                } elseif ($empresa->fiel_key_path) {
                    $keyContent = Storage::disk('public')->get("certificados/{$rfc}/{$empresa->fiel_key_path}");
                }
                
                if (empty($cerContent) || empty($keyContent)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'No se encontraron los archivos .cer o .key registrados para esta empresa.'
                    ], 422);
                }

                $passwordFiel = trim($request->input('fiel_password') ?: $empresa->fiel_password);

                if (empty($passwordFiel)) {
                    return response()->json(['success' => false, 'error' => 'La contraseña de la e.Firma no está registrada en la base de datos.'], 422);
                }

                // 3.2 Obtención de Token mediante Sellado Digital (Sin depender de WSDL)
                try {
                    $token = $this->obtenerTokenAutenticacion($cerContent, $keyContent, $passwordFiel);
                    Log::info("Token SAT obtenido con éxito para RFC: {$rfc}");
                    
                    // En producción, aquí se llamaría a los servicios de Solicitud, Verificación y Descarga.
                    return response()->json([
                        'success' => true,
                        'message' => "Conexión con el SAT exitosa. Autenticación completada para " . $rfc
                    ]);

                } catch (\Exception $soapEx) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Error de conexión con el WS del SAT: ' . $soapEx->getMessage()
                    ], 500);
                }

            } else {
                // Autenticación por CIEC (Password de la base de datos)
                $passwordCiec = $request->input('ciec_pass_field') ?: $empresa->ciec;

                if (empty($passwordCiec)) {
                    return response()->json(['success' => false, 'error' => 'La contraseña CIEC no está registrada en la base de datos.'], 422);
                }

                // Aquí procedería la conexión al SAT usando $rfc y $passwordCiec
                Log::info("Iniciando descarga masiva con CIEC para: {$rfc}");
            }

            // 4. Lógica para descarga masiva (Simulación)
            $xmlsDescargados = []; // Variable para guardar los XMLs obtenidos

            // Simulación de procesamiento de archivos descargados
            foreach ($xmlsDescargados as $xmlRaw) {
                $parser->importarXml($xmlRaw);
            }

            return response()->json([
                'success' => true,
                'message' => "El proceso para {$empresa->razon_social} finalizó correctamente."
            ]);

        } catch (\Exception $e) {
            Log::error("Error en descarga masiva: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error en el proceso masivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Realiza el sellado y la petición manual al WS de Autenticación del SAT.
     * Basado en el estándar de seguridad WS-Security que requiere el SAT.
     */
    private function obtenerTokenAutenticacion($cerContent, $keyContent, $passwordFiel)
    {
        $cerB64 = base64_encode($cerContent);
        $created = gmdate('Y-m-d\TH:i:s.v\Z');
        $expires = gmdate('Y-m-d\TH:i:s.v\Z', time() + 300);
        $uuid = bin2hex(random_bytes(16));

        // 1. Normalizar la llave privada (Convertir DER a PEM si es necesario)
        // El SAT entrega el .key en formato binario DER; PHP requiere formato PEM
        if (strpos($keyContent, '-----BEGIN') === false) {
            $keyContent = "-----BEGIN ENCRYPTED PRIVATE KEY-----\n" 
                        . chunk_split(base64_encode($keyContent), 64, "\n") 
                        . "-----END ENCRYPTED PRIVATE KEY-----";
        }

        $privateKey = openssl_pkey_get_private($keyContent, $passwordFiel);
        if (!$privateKey) {
            $sslError = openssl_error_string();
            throw new Exception("No se pudo abrir la llave privada. Verifique la contraseña de la e.Firma. (OpenSSL: $sslError)");
        }

        // 2. Crear el bloque de Timestamp para firmar
        $toSign = '<u:Timestamp xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" u:Id="_0"><u:Created>'.$created.'</u:Created><u:Expires>'.$expires.'</u:Expires></u:Timestamp>';
        $digest = base64_encode(sha1($toSign, true));

        // 3. Preparar el bloque SignedInfo para la firma RSA
        $sigInfo = '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#"><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></CanonicalizationMethod><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"></SignatureMethod><Reference URI="#_0"><Transforms><Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></Transform></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"></DigestMethod><DigestValue>'.$digest.'</DigestValue></Reference></SignedInfo>';
        
        openssl_sign($sigInfo, $signature, $privateKey, OPENSSL_ALGO_SHA1);
        $sigB64 = base64_encode($signature);

        // 4. Construir el sobre SOAP manualmente (Evita errores de WSDL)
        $xml = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><s:Header><o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><u:Timestamp u:Id="_0"><u:Created>'.$created.'</u:Created><u:Expires>'.$expires.'</u:Expires></u:Timestamp><o:BinarySecurityToken u:Id="uuid-'.$uuid.'-1" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$cerB64.'</o:BinarySecurityToken><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/><Reference URI="#_0"><Transforms><Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/><DigestValue>'.$digest.'</DigestValue></Reference></SignedInfo><SignatureValue>'.$sigB64.'</SignatureValue><KeyInfo><o:SecurityTokenReference><o:Reference ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" URI="#uuid-'.$uuid.'-1"/></o:SecurityTokenReference></KeyInfo></Signature></o:Security></s:Header><s:Body><Autentica xmlns="http://TempUri.org/"/></s:Body></s:Envelope>';

        // 5. Enviar mediante CURL (Más robusto en Laragon para HTTPS/TLS)
        $ch = curl_init("https://autenticacion.clouda.sat.gob.mx/Autenticacion.svc");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://TempUri.org/IAutenticacion/Autentica"',
            'Accept: text/xml',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (defined('CURL_SSLVERSION_TLSv1_2')) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        }
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) throw new Exception("Error de red (CURL): " . curl_error($ch));
        curl_close($ch);

        if ($httpCode !== 200) throw new Exception("El SAT respondió con error HTTP {$httpCode}.");

        // 6. Extraer el token del resultado SOAP
        if (preg_match('/<AutenticaResult>(.*)<\/AutenticaResult>/', $response, $matches)) {
            return $matches[1];
        }

        throw new Exception("Respuesta inesperada del SAT. No se encontró el token de autenticación.");
    }
}