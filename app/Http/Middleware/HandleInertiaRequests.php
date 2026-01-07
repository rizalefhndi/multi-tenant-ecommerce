<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'cart' => fn () => $this->getCartData($request),
            // Override Ziggy URL to use current host for proper tenant routing
            'ziggy' => fn () => [
                ...(new \Tighten\Ziggy\Ziggy)->toArray(),
                'url' => $request->getSchemeAndHttpHost(),
            ],
        ];
    }

    /**
     * Get cart data for the authenticated user
     */
    protected function getCartData(Request $request): ?array
    {
        if (!$request->user()) {
            return null;
        }

        try {
            $cart = \App\Models\Cart::where('user_id', $request->user()->id)
                ->with('items')
                ->first();

            if (!$cart) {
                return [
                    'total_items' => 0,
                    'subtotal' => 0,
                ];
            }

            return [
                'total_items' => $cart->items->sum('quantity'),
                'subtotal' => $cart->items->sum(function ($item) {
                    return $item->quantity * $item->price;
                }),
            ];
        } catch (\Exception $e) {
            return [
                'total_items' => 0,
                'subtotal' => 0,
            ];
        }
    }
}
