<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AIController;
use Illuminate\Http\Request;

class AiApiController extends Controller
{
    /**
     * AI Chat & Product Recommendation API
     */
    public function chat(Request $request)
    {
        $aiController = app(AIController::class);
        return $aiController->chat($request);
    }
}
