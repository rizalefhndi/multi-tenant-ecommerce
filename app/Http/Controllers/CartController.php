<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    /**
     * Display the user's cart
     */
    public function index(): Response
    {
        // Get or create cart untuk user yang sedang login
        $cart = Auth()->user()->getOrCreateCart();

        // Load cart items dengan product relationship
        $cart->load(['items.product']);

        return Inertia::render('Carts/Index', [
            'cart' => [
                'id' => $cart->id,
                'status' => $cart->status,
                'total_items' => $cart->total_items,
                'total_price' => $cart->total_price,
                'formatted_total' => $cart->formatted_total,
                'items' => $cart->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'formatted_price' => $item->formatted_price,
                        'formatted_subtotal' => $item->formatted_subtotal,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'description' => $item->product->description,
                            'image' => $item->product->image,
                            'stock' => $item->product->stock,
                            'is_active' => $item->product->is_active,
                        ],
                    ];
                }),
            ],
        ]);
    }

    /**
     * Add product to cart
     */
    public function addItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Get product
        $product = Product::findOrFail($validated['product_id']);

        // Check if product is available
        if (!$product->isAvailable()) {
            return back()->with('error', 'Product is not available!');
        }

        // Check stock
        if ($product->stock < $validated['quantity']) {
            return back()->with('error', 'Insufficient stock!');
        }

        // Get or create cart
        $cart = auth()->user()->getOrCreateCart();

        // Add product to cart
        $cart->addProduct($product, $validated['quantity']);

        return back()->with('success', 'Product added to cart!');
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, $itemId): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Get cart
        $cart = auth()->user()->getOrCreateCart();

        // Find cart item
        $cartItem = $cart->items()->findOrFail($itemId);

        // Check stock
        if ($cartItem->product->stock < $validated['quantity']) {
            return back()->with('error', 'Insufficient stock!');
        }

        // Update quantity
        $cartItem->updateQuantity($validated['quantity']);

        return back()->with('success', 'Cart updated!');
    }

    /**
     * Remove item from cart
     */
    public function removeItem($itemId): RedirectResponse
    {
        // Get cart
        $cart = auth()->user()->getOrCreateCart();

        // Find and delete cart item
        $cartItem = $cart->items()->findOrFail($itemId);
        $cartItem->delete();

        return back()->with('success', 'Item removed from cart!');
    }

    /**
     * Clear all items from cart
     */
    public function clear(): RedirectResponse
    {
        // Get cart
        $cart = auth()->user()->getOrCreateCart();

        // Clear all items
        $cart->clear();

        return back()->with('success', 'Cart cleared!');
    }

    /**
     * Checkout - Mark cart as completed
     */
    public function checkout(): RedirectResponse
    {
        // Get cart
        $cart = auth()->user()->getOrCreateCart();

        // Validate cart has items
        if ($cart->items->isEmpty()) {
            return back()->with('error', 'Cart is empty!');
        }

        // Check stock untuk semua items
        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                return back()->with('error', "Insufficient stock for {$item->product->name}!");
            }
        }

        // Decrease stock untuk semua products
        foreach ($cart->items as $item) {
            $item->product->decreaseStock($item->quantity);
        }

        // Mark cart as completed
        $cart->markAsCompleted();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Checkout successful! Your order has been placed.');
    }

    /**
     * Get cart summary (untuk API / AJAX)
     */
    public function summary()
    {
        $cart = auth()->user()->getOrCreateCart();
        $cart->load(['items.product']);

        return response()->json([
            'total_items' => $cart->total_items,
            'total_price' => $cart->total_price,
            'formatted_total' => $cart->formatted_total,
        ]);
    }
}
