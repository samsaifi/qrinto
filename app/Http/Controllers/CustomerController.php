<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Address;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Middleware 'auth' is applied via route group in web.php

    public function dashboard()
    {
        $user = auth()->user();
        $recentOrders = $user->orders()->with('items')->latest()->take(5)->get();
        $totalOrders = $user->orders()->count();
        $totalSpent = $user->orders()->where('payment_status', 'paid')->sum('total');
        $pendingOrders = $user->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count();

        return view('customer.dashboard', compact('user', 'recentOrders', 'totalOrders', 'totalSpent', 'pendingOrders'));
    }

    public function orders()
    {
        $orders = auth()->user()->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function orderDetail_pdf(Order $order) {
         if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product', 'payments');

        return view('customer.order-detail', compact('order'));
    }
    public function orderDetail(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product', 'payments');

        return view('customer.order-detail', compact('order'));
    }

    public function profile()
    {
        $user = auth()->user();
        $addresses = $user->addresses;

        return view('customer.profile', compact('user', 'addresses'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        auth()->user()->update($request->only('name', 'phone'));

        return redirect()->route('customer.profile')->with('success', 'Profile updated!');
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
        ]);

        $address = auth()->user()->addresses()->create($request->all());

        if ($request->is_default) {
            auth()->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        return redirect()->route('customer.profile')->with('success', 'Address added!');
    }

    public function deleteAddress(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->delete();

        return redirect()->route('customer.profile')->with('success', 'Address removed!');
    }
}
