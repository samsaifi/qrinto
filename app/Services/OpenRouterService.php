<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenRouterService
{
    public function chat(array $messages)
    {
        $response = Http::timeout(120)
            ->withToken(config('services.openrouter.api_key'))
            ->withHeaders([
                'HTTP-Referer' => config('app.url'),
                'X-OpenRouter-Title' => config('app.name'),
            ])
            ->post(config('services.openrouter.base_url') . '/chat/completions', [
                'model' => config('services.openrouter.model'),
                'messages' => $messages,
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }
}