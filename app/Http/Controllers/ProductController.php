<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     *
     */
    public function index(Request $request): Response
    {
        // Query builder untuk products
        $query = Product::query();

        // Search functionality (case-insensitive, whole word match)
        if ($request->has('search') && $request->search) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                // Word boundary search: match whole words only
                // Pattern: word at start, middle (spaces), or end
                $q->where(function ($subQ) use ($search) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', ["{$search}%"]) // starts with
                         ->orWhereRaw('LOWER(name) LIKE ?', ["% {$search}%"]) // word in middle/end
                         ->orWhereRaw('LOWER(name) = ?', [$search]); // exact match
                })->orWhere(function ($subQ) use ($search) {
                    $subQ->whereRaw('LOWER(description) LIKE ?', ["{$search}%"])
                         ->orWhereRaw('LOWER(description) LIKE ?', ["% {$search}%"])
                         ->orWhereRaw('LOWER(description) = ?', [$search]);
                });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by stock
        if ($request->has('stock')) {
            if ($request->stock === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($request->stock === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            }
        }

        // Get products with pagination
        $products = $query->latest()
            ->paginate(12)
            ->withQueryString(); // Preserve query parameters

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'status', 'stock']),
        ]);
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): Response
    {
        return Inertia::render('Products/Create');
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Set default is_active jika tidak ada
        $validated['is_active'] = $validated['is_active'] ?? true;

        // Create product
        $product = Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): Response
    {
        $user = auth()->user();
        
        // Customer view
        if ($user && $user->isCustomer()) {
            // Get related products (same category or random)
            $relatedProducts = Product::where('id', '!=', $product->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit(4)
                ->get();

            return Inertia::render('Customer/ProductDetail', [
                'product' => $product,
                'relatedProducts' => $relatedProducts,
            ]);
        }

        // Admin view
        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Update product
        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Check if product has cart items
        if ($product->cartItems()->exists()) {
            return back()->with('error', 'Cannot delete product that exists in carts!');
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * Toggle product active status
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update([
            'is_active' => !$product->is_active,
        ]);

        $status = $product->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Product {$status} successfully!");
    }
}
