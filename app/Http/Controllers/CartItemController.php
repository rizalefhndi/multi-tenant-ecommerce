<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class CartItemController extends Controller
{
    /**
     * Increment item quantity
     */
    public function increment(CartItem $cartItem): RedirectResponse
    {
        // Verify cart belongs to current user
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check stock
        if ($cartItem->product->stock < $cartItem->quantity + 1) {
            return back()->with('error', 'Insufficient stock!');
        }

        $cartItem->incrementQuantity();

        return back()->with('success', 'Quantity updated!');
    }

    /**
     * Decrement item quantity
     */
    public function decrement(CartItem $cartItem): RedirectResponse
    {
        // Verify cart belongs to current user
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // decrementQuantity akan auto-delete jika quantity jadi 0
        $cartItem->decrementQuantity();

        return back()->with('success', 'Quantity updated!');
    }

    /**
     * Update quantity directly
     */
    public function updateQuantity(Request $request, CartItem $cartItem): RedirectResponse
    {
        // Verify cart belongs to current user
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Check stock
        if ($cartItem->product->stock < $validated['quantity']) {
            return back()->with('error', 'Insufficient stock!');
        }

        $cartItem->updateQuantity($validated['quantity']);

        return back()->with('success', 'Quantity updated!');
    }

    /**
     * Remove item from cart
     */
    public function destroy(CartItem $cartItem): RedirectResponse
    {
        // Verify cart belongs to current user
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart!');
    }

    /**
     * AJAX endpoints untuk real-time updates
     */

    /**
     * Increment via AJAX
     */
    public function ajaxIncrement(CartItem $cartItem): JsonResponse
    {
        if ($cartItem->cart->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($cartItem->product->stock < $cartItem->quantity + 1) {
            return response()->json(['error' => 'Insufficient stock'], 400);
        }

        $cartItem->incrementQuantity();
        $cartItem->load('product');

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $cartItem->id,
                'quantity' => $cartItem->quantity,
                'subtotal' => $cartItem->subtotal,
                'formatted_subtotal' => $cartItem->formatted_subtotal,
            ],
            'cart' => [
                'total_items' => $cartItem->cart->total_items,
                'total_price' => $cartItem->cart->total_price,
                'formatted_total' => $cartItem->cart->formatted_total,
            ],
        ]);
    }

    /**
     * Decrement via AJAX
     */
    public function ajaxDecrement(CartItem $cartItem): JsonResponse
    {
        if ($cartItem->cart->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deleted = !$cartItem->decrementQuantity();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'deleted' => true,
                'cart' => [
                    'total_items' => $cartItem->cart->total_items,
                    'total_price' => $cartItem->cart->total_price,
                    'formatted_total' => $cartItem->cart->formatted_total,
                ],
            ]);
        }

        $cartItem->load('product');

        return response()->json([
            'success' => true,
            'deleted' => false,
            'item' => [
                'id' => $cartItem->id,
                'quantity' => $cartItem->quantity,
                'subtotal' => $cartItem->subtotal,
                'formatted_subtotal' => $cartItem->formatted_subtotal,
            ],
            'cart' => [
                'total_items' => $cartItem->cart->total_items,
                'total_price' => $cartItem->cart->total_price,
                'formatted_total' => $cartItem->cart->formatted_total,
            ],
        ]);
    }
}
