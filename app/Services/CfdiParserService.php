<?php

namespace App\Services;

use App\Models\Comprobante;
use Exception;
use Illuminate\Support\Facades\DB;

class CfdiParserService
{
    public function importarXml($xmlContent)
    {
        try {
            $xml = simplexml_load_string($xmlContent);
            $ns = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('cfdi', $ns['cfdi']);
            $tfd = $xml->xpath('//tfd:TimbreFiscalDigital');

            return DB::transaction(function () use ($xml, $tfd, $xmlContent) {
                // 1. Extraer Datos Generales (Cabecera)
                $comprobante = Comprobante::create([
                    'uuid' => (string)$tfd[0]['UUID'],
                    'serie' => (string)$xml['Serie'],
                    'folio' => (string)$xml['Folio'],
                    'fecha_emision' => (string)$xml['Fecha'],
                    'fecha_certificacion' => (string)$tfd[0]['FechaTimbrado'],
                    'rfc_emisor' => (string)$xml->children($ns['cfdi'])->Emisor['Rfc'],
                    'nombre_emisor' => (string)$xml->children($ns['cfdi'])->Emisor['Nombre'],
                    'regimen_fiscal_emisor' => (string)$xml->children($ns['cfdi'])->Emisor['RegimenFiscal'],
                    'rfc_receptor' => (string)$xml->children($ns['cfdi'])->Receptor['Rfc'],
                    'nombre_receptor' => (string)$xml->children($ns['cfdi'])->Receptor['Nombre'],
                    'uso_cfdi' => (string)$xml->children($ns['cfdi'])->Receptor['UsoCFDI'],
                    'tipo_comprobante' => (string)$xml['TipoDeComprobante'],
                    'metodo_pago' => (string)$xml['MetodoPago'],
                    'forma_pago' => (string)$xml['FormaPago'],
                    'subtotal' => (float)$xml['SubTotal'],
                    'descuento' => (float)$xml['Descuento'] ?? 0,
                    'total' => (float)$xml['Total'],
                    'moneda' => (string)$xml['Moneda'],
                    'xml_storage' => $xmlContent, // Guardamos el XML puro
                ]);

                // 2. Procesar Conceptos
                foreach ($xml->children($ns['cfdi'])->Conceptos->Concepto as $con) {
                    $comprobante->conceptos()->create([
                        'clave_prod_serv' => (string)$con['ClaveProdServ'],
                        'cantidad' => (float)$con['Cantidad'],
                        'clave_unit' => (string)$con['ClaveUnidad'],
                        'descripcion' => (string)$con['Descripcion'],
                        'valor_unitario' => (float)$con['ValorUnitario'],
                        'importe' => (float)$con['Importe'],
                    ]);
                }

                // 3. Procesar Impuestos (Traslados y Retenciones)
                if (isset($xml->children($ns['cfdi'])->Impuestos)) {
                    $impuestosGlobales = $xml->children($ns['cfdi'])->Impuestos;
                    
                    // Traslados
                    if (isset($impuestosGlobales->Traslados)) {
                        foreach ($impuestosGlobales->Traslados->Traslado as $tras) {
                            $comprobante->impuestos()->create([
                                'tipo' => 'Traslado',
                                'impuesto' => (string)$tras['Impuesto'],
                                'tipo_factor' => (string)$tras['TipoFactor'],
                                'tasa_o_cuota' => (float)$tras['TasaOCuota'],
                                'importe' => (float)$tras['Importe'],
                            ]);
                        }
                    }
                }

                // 4. Procesar UUIDs Relacionados
                if (isset($xml->children($ns['cfdi'])->CfdiRelacionados)) {
                    $comprobante->tipo_relacion = (string)$xml->children($ns['cfdi'])->CfdiRelacionados['TipoRelacion'];
                    $comprobante->save();

                    foreach ($xml->children($ns['cfdi'])->CfdiRelacionados->CfdiRelacionado as $rel) {
                        $comprobante->relacionados()->create([
                            'uuid_relacionado' => (string)$rel['UUID']
                        ]);
                    }
                }

                return $comprobante;
            });
        } catch (Exception $e) {
            throw new Exception("Error procesando XML: " . $e->getMessage());
        }
    }
}