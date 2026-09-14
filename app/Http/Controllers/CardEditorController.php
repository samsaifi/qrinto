<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * CardEditorController
 * --------------------
 * Standalone, self-contained card editor (v2) inspired by the
 * Greetings Island customizer: HTML/CSS overlay engine with
 * percentage-positioned elements, on-canvas rich-text editing and
 * multi-page support.
 *
 * This controller is completely independent from QuickFlowController and
 * the existing Fabric.js customizer. It does not touch any existing
 * blade, JS or route logic. Safe to remove without side effects.
 */
class CardEditorController extends Controller
{
    /**
     * Render the new editor.
     *
     * Optionally accepts a product slug/id purely to pre-fill the canvas
     * size; falls back to a sensible default 5x7 card when none is given.
     */
    public function index(Request $request, $product = null)
    {
        $found = null;
        if ($product) {
            $found = Product::where('slug', $product)
                ->orWhere('id', $product)
                ->first();
        }

        // Default card geometry (inches). Overridden by product if present.
        $size = [
            'width'  => 5.0,
            'height' => 7.0,
            'unit'   => 'in',
        ];

        return view('card-editor.index', [
            'product' => $found,
            'size'    => $size,
        ]);
    }
}
