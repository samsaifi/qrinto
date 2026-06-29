<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCart(): Cart
    {
        if (Auth::check()) {
            $cart = Cart::with('items.product', 'coupon')
                ->firstOrCreate(['user_id' => Auth::id()]);

            // Merge session cart if exists
            $sessionId = Session::getId();
            $sessionCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
            if ($sessionCart) {
                foreach ($sessionCart->items as $item) {
                    $item->update(['cart_id' => $cart->id]);
                }
                $sessionCart->delete();
            }
        } else {
            $cart = Cart::with('items.product', 'coupon')
                ->firstOrCreate(['session_id' => Session::getId()]);
        }

        return $cart->fresh(['items.product', 'coupon']);
    }

    public function mergeGuestCart(string $oldSessionId): void
    {
        if (!Auth::check()) return;
        
        $sessionCart = Cart::where('session_id', $oldSessionId)->whereNull('user_id')->first();
        if ($sessionCart) {
            $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            foreach ($sessionCart->items as $item) {
                // don't duplicate items if they exist
                $exists = $userCart->items()
                    ->where('product_id', $item->product_id)
                    ->where('selected_options', json_encode($item->selected_options))
                    ->first();
                if ($exists) {
                    $exists->update([
                        'quantity' => $exists->quantity + $item->quantity
                    ]);
                    $item->delete();
                } else {
                    $item->update(['cart_id' => $userCart->id]);
                }
            }
            if ($sessionCart->coupon_id && !$userCart->coupon_id) {
                $userCart->update(['coupon_id' => $sessionCart->coupon_id]);
            }
            $sessionCart->delete();
        }
    }

    public function addItem(?int $productId, int $quantity, float $unitPrice, ?array $customizationData = null, ?array $selectedOptions = null): CartItem
    {
        $cart = $this->getCart();

        // Only deduplicate if product_id is set (regular products)
        if ($productId !== null) {
            $existingItem = $cart->items()
                ->where('product_id', $productId)
                ->where('selected_options', json_encode($selectedOptions))
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $quantity,
                    'unit_price' => $unitPrice,
                ]);
                return $existingItem;
            }
        }

        return $cart->items()->create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'customization_data' => $customizationData,
            'selected_options' => $selectedOptions,
        ]);
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $cart = $this->getCart();
        $item = $cart->items()->findOrFail($itemId);

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }
    }

    public function removeItem(int $itemId): void
    {
        $cart = $this->getCart();
        $cart->items()->where('id', $itemId)->delete();
    }

    public function applyCoupon(string $code): array
    {
        $cart = $this->getCart();
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid coupon code.'];
        }

        if (!$coupon->isValid($cart->subtotal)) {
            return ['success' => false, 'message' => 'This coupon is not valid or has expired.'];
        }

        $cart->update(['coupon_id' => $coupon->id]);

        return [
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'discount' => $coupon->calculateDiscount($cart->subtotal),
        ];
    }

    public function removeCoupon(): void
    {
        $this->getCart()->update(['coupon_id' => null]);
    }

    public function clearCart(): void
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        $cart->update(['coupon_id' => null]);
    }
}
