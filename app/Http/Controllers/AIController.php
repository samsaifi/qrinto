<?php

namespace App\Http\Controllers;

use App\Services\OpenRouterService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function chat(Request $request, OpenRouterService $ai)
    {
        $result = $ai->chat([
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => 'user',
                'content' => $request->message
            ]
        ]);

        return response()->json([
            'reply' => $result['choices'][0]['message']['content']
        ]);
    }
}