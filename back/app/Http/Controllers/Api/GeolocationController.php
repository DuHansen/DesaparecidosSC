<?php

namespace App\Http\Controllers\Api;

// Importe a classe Controller
use App\Http\Controllers\Controller; // Adicione esta linha

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeolocationController extends Controller
{
public function getCidadeByCoordinates(Request $request)
{
    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');

    // API externa para geocodificação
    $apiKey = '602c5b74757b4039bd6e0533a0b7e8c7';
    $url = "https://api.opencagedata.com/geocode/v1/json?q=$latitude+$longitude&key=$apiKey&language=pt&no_annotations=1";

    $response = Http::get($url);
    $data = $response->json();

    // Verifique a resposta completa da API
    \Log::info('OpenCage Response:', $data);  // Registra a resposta no log para depuração

    // Verifique se há resultados e retorne a cidade
    if (isset($data['results'][0]['components']['city'])) {
        return response()->json([
            'cidade' => $data['results'][0]['components']['city']
        ], 200);
    }

    // Caso a cidade não esteja em "city", vamos tentar procurar na chave "_normalized_city" ou outros locais
    if (isset($data['results'][0]['components']['_normalized_city'])) {
        return response()->json([
            'cidade' => $data['results'][0]['components']['_normalized_city']
        ], 200);
    }

    return response()->json([
        'error' => 'Cidade não encontrada',
        'api_response' => $data
    ], 400);
}

   public function getCidadeByIp(Request $request)
{
    // Usando uma API externa para geolocalização por IP
    $url = 'https://ipapi.co/json/';  // Ou substitua com outra API de sua escolha
    $response = Http::get($url);
    $data = $response->json();

    // Registra a resposta completa para depuração
    \Log::info('Resposta da API de Geolocalização IP:', $data);

    // Verifica se há o campo 'city' na resposta
    if (isset($data['city'])) {
        return response()->json([
            'cidade' => $data['city']
        ], 200);
    }

    // Caso 'city' não esteja presente, tenta outras possibilidades
    if (isset($data['region'])) {
        return response()->json([
            'cidade' => $data['region']
        ], 200);
    }

    // Retorna um erro detalhado se 'cidade' ou 'region' não for encontrado
    return response()->json([
        'error' => 'Cidade não encontrada',
        'api_response' => $data
    ], 400);
}

}
