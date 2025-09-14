<?php

namespace App\Service;

use DeepSeek\DeepSeekClient;
use DeepSeek\Enums\Models;

class ApiService
{
    public function run(string $text): array
    {
        $client = DeepSeekClient::build(apiKey:'sk-83ee5c70b6784b34b98aac2647f23fb8', baseUrl:'https://api.deepseek.com/v3', timeout:30, clientType:'guzzle');

        $response = $client
            ->withModel(Models::CODER->value)
            ->setTemperature(1.0)
            ->setResponseFormat('json_object')
            ->query('as json,' . $text)
            ->run();
        $data = json_decode($response, true);

        return json_decode($data['choices'][0]['message']['content'], true);
    }
}
