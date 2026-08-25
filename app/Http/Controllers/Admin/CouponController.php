<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    private function scopedQuery()
    {
        $query = Coupon::latest();

        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    private function authorizeCoupon(Coupon $coupon): void
    {
        if (!Auth::user()->isAdmin() && $coupon->user_id !== Auth::id()) {
            abort(403);
        }
    }

    public function index()
    {
        $coupons = $this->scopedQuery()->with(['user', 'store'])->paginate(20);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $stores = Auth::user()->isAdmin() ? Store::orderBy('store_name')->get() : collect();
        return view('admin.coupons.form', compact('stores'));
    }

    public function store(Request $request)
    {
        $rules = [
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ];

        if (Auth::user()->isAdmin()) {
            $rules['store_id'] = 'nullable|exists:stores,id';
        }

        $request->validate($rules);

        $data = $request->only(['code', 'type', 'value', 'min_order_amount', 'usage_limit', 'expires_at']);
        $data['code'] = strtoupper($data['code']);
        $data['name'] = $data['code'];
        $data['is_active'] = $request->boolean('is_active', true);
        $data['used_count'] = 0;

        if (Auth::user()->isAdmin()) {
            $data['store_id'] = $request->input('store_id');
        } else {
            $data['user_id'] = Auth::id();
            $data['store_id'] = Auth::user()->store_id;
        }

        Coupon::create($data);

        $route = request()->is('store*') ? 'storepanel_cat.coupons.index' : 'admin.coupons.index';
        return redirect()->route($route)
            ->with('success', 'Coupon created!');
    }

    public function edit(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);

        $stores = Auth::user()->isAdmin() ? Store::orderBy('store_name')->get() : collect();
        return view('admin.coupons.form', compact('coupon', 'stores'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);

        $rules = [
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ];

        if (Auth::user()->isAdmin()) {
            $rules['store_id'] = 'nullable|exists:stores,id';
        }

        $request->validate($rules);

        $data = $request->only(['code', 'type', 'value', 'min_order_amount', 'usage_limit', 'expires_at']);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);

        if (Auth::user()->isAdmin()) {
            $data['store_id'] = $request->input('store_id');
        }

        $coupon->update($data);

        $route = request()->is('store*') ? 'storepanel_cat.coupons.index' : 'admin.coupons.index';
        return redirect()->route($route)
            ->with('success', 'Coupon updated!');
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);

        $coupon->delete();
        $route = request()->is('store*') ? 'storepanel_cat.coupons.index' : 'admin.coupons.index';
        return redirect()->route($route)
            ->with('success', 'Coupon deleted.');
    }
}
