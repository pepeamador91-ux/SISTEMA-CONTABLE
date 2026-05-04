<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;
use Exception;

class SatController extends Controller
{
public function conectarSat()
{
    $url = "https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/DescargaMasivaService.svc?wsdl";

    try {
        // Configuramos un contexto para que PHP no bloquee la conexión HTTPS localmente
        $opts = [
            'http' => [
                'header' => "Content-Type: text/xml; charset=utf-8",
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];
        $context = stream_context_create($opts);

        // Pasamos el contexto al SoapClient
        $client = new SoapClient($url, [
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'stream_context' => $context // <--- Esto resuelve el error de la imagen_131.png
        ]);

        return response()->json([
            'status' => 'success',
            'mensaje' => 'Conexión establecida con el Web Service del SAT'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'mensaje' => 'Error de conexión: ' . $e->getMessage()
        ], 500);
    }
}}
