<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponApiController extends Controller
{
    /**
     * Validate Coupon Code
     */
    public function validateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'     => 'required|string',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid or inactive coupon code.'], 404);
        }

        $now = now();
        if ($coupon->start_date && $now->lt($coupon->start_date)) {
            return response()->json(['success' => false, 'message' => 'Coupon is not active yet.'], 400);
        }
        if ($coupon->end_date && $now->gt($coupon->end_date)) {
            return response()->json(['success' => false, 'message' => 'Coupon has expired.'], 400);
        }

        $subtotal = (float) $request->input('subtotal', 0);
        if ($coupon->min_spend && $subtotal < $coupon->min_spend) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum spend of ' . CurrencyService::format($coupon->min_spend) . ' required to use this coupon.',
            ], 400);
        }

        $discount = 0;
        if ($coupon->discount_type === 'percentage') {
            $discount = ($subtotal * $coupon->discount_amount) / 100;
            if ($coupon->max_discount && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        } else {
            $discount = min($subtotal, $coupon->discount_amount);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon is valid.',
            'data'    => [
                'code'               => $coupon->code,
                'discount_type'      => $coupon->discount_type,
                'discount_value'     => (float) $coupon->discount_amount,
                'calculated_discount'=> (float) $discount,
                'formatted_discount' => CurrencyService::format($discount),
            ],
        ]);
    }
}
