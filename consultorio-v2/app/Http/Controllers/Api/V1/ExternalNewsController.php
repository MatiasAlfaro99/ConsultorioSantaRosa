<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExternalNewsController extends Controller
{
    public function minsal(): JsonResponse
    {
        $xmlContent = null;

        // 1. INTENTO ONLINE (Con esteroides para evitar bloqueo)
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
                'Referer' => 'https://www.minsal.cl/'
            ])->timeout(5)->get('https://www.minsal.cl/category/noticias/feed/');

            if ($response->successful()) {
                $xmlContent = $response->body();
            }
        } catch (\Exception $e) {
            Log::warning("Fallo conexión Minsal Online, usando respaldo local.");
        }

        // 2. PLAN B: SI FALLA ONLINE, USAR ARCHIVO LOCAL (El que tú subiste)
        if (!$xmlContent) {
            if (Storage::exists('minsal_feed.xml')) {
                $xmlContent = Storage::get('minsal_feed.xml');
            } else {
                // Si no hay archivo ni internet, devolvemos vacío
                return response()->json([]);
            }
        }

        // 3. PARSEO DEL XML (Ajustado a tu archivo)
        try {
            // LIBXML_NOCDATA es vital para leer tu archivo que tiene <![CDATA[...]]> 
            $rss = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
            $namespaces = $rss->getNamespaces(true); // Necesario para leer content:encoded
            
            $noticias = [];
            $contador = 0;

            foreach ($rss->channel->item as $item) {
                if ($contador >= 6) break;

                // Extraer HTML del content:encoded (donde vi que están las fotos en tu archivo)
                $htmlContent = '';
                if (isset($namespaces['content']) && isset($item->children($namespaces['content'])->encoded)) {
                    $htmlContent = (string)$item->children($namespaces['content'])->encoded; // 
                }
                
                // Si no hay content, usar description [cite: 2]
                if (empty($htmlContent)) {
                    $htmlContent = (string)$item->description;
                }

                // Buscar la imagen dentro del HTML
                $imagen = null;
                if (preg_match('/<img.+?src=[\'"](?P<src>.+?)[\'"].*?>/i', $htmlContent, $match)) {
                    $imagen = $match['src']; // [cite: 4]
                }

                // Limpiar el resumen
                $resumen = strip_tags((string)$item->description);
                $resumen = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $resumen)), 100);

                $noticias[] = [
                    'titulo'  => (string)$item->title, // 
                    'link'    => (string)$item->link,  // 
                    'fecha'   => date('d/m/Y', strtotime((string)$item->pubDate)), // 
                    'resumen' => $resumen,
                    'imagen'  => $imagen 
                ];
                $contador++;
            }

            return response()->json($noticias);

        } catch (\Exception $e) {
            Log::error("Error parseando XML: " . $e->getMessage());
            return response()->json([]);
        }
    }
}
