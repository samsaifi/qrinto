<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        if (session()->has('active_store_id')) {
            return redirect()->route('flow.index');
        }
        
        return app(\App\Http\Controllers\QuickFlowController::class)->findStore($request);
    }
}
