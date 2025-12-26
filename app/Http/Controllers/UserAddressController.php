<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserAddressController extends Controller
{
    /**
     * Display a listing of user addresses.
     */
    public function index(): Response
    {
        $addresses = UserAddress::where('user_id', auth()->id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Addresses/Index', [
            'addresses' => $addresses,
            'labels' => UserAddress::getLabels(),
        ]);
    }

    /**
     * Show the form for creating a new address.
     */
    public function create(): Response
    {
        return Inertia::render('Addresses/Create', [
            'labels' => UserAddress::getLabels(),
        ]);
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        // Jika ini alamat pertama user, jadikan default
        if (!UserAddress::where('user_id', auth()->id())->exists()) {
            $validated['is_default'] = true;
        }

        $address = UserAddress::create($validated);

        // Jika is_default true, unset alamat lain
        if ($address->is_default) {
            UserAddress::where('user_id', auth()->id())
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        return redirect()
            ->route('addresses.index')
            ->with('success', 'Alamat berhasil ditambahkan');
    }

    /**
     * Display the specified address.
     */
    public function show(UserAddress $address): Response
    {
        // Check ownership
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Addresses/Show', [
            'address' => $address,
        ]);
    }

    /**
     * Show the form for editing the specified address.
     */
    public function edit(UserAddress $address): Response
    {
        // Check ownership
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Addresses/Edit', [
            'address' => $address,
            'labels' => UserAddress::getLabels(),
        ]);
    }

    /**
     * Update the specified address in storage.
     */
    public function update(UpdateAddressRequest $request, UserAddress $address): RedirectResponse
    {
        $validated = $request->validated();

        $address->update($validated);

        // Jika is_default true, unset alamat lain
        if ($address->is_default) {
            UserAddress::where('user_id', auth()->id())
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        return redirect()
            ->route('addresses.index')
            ->with('success', 'Alamat berhasil diperbarui');
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy(UserAddress $address): RedirectResponse
    {
        // Check ownership
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        // Jangan hapus jika ini satu-satunya alamat
        $addressCount = UserAddress::where('user_id', auth()->id())->count();

        // Jika ini alamat default dan ada alamat lain, set alamat lain sebagai default
        if ($address->is_default && $addressCount > 1) {
            $newDefault = UserAddress::where('user_id', auth()->id())
                ->where('id', '!=', $address->id)
                ->first();

            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $address->delete();

        return redirect()
            ->route('addresses.index')
            ->with('success', 'Alamat berhasil dihapus');
    }

    /**
     * Set address as default.
     */
    public function setDefault(UserAddress $address): RedirectResponse
    {
        // Check ownership
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->setAsDefault();

        return back()->with('success', 'Alamat default berhasil diubah');
    }

    /**
     * API: Get user addresses (untuk AJAX/Vue)
     */
    public function apiIndex()
    {
        $addresses = UserAddress::where('user_id', auth()->id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'label' => $address->label,
                    'label_icon' => $address->label_icon,
                    'recipient_name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'full_address' => $address->full_address,
                    'short_address' => $address->short_address,
                    'is_default' => $address->is_default,
                ];
            });

        return response()->json([
            'addresses' => $addresses,
        ]);
    }

    /**
     * API: Store address (untuk modal di checkout)
     */
    public function apiStore(StoreAddressRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        // Jika ini alamat pertama user, jadikan default
        if (!UserAddress::where('user_id', auth()->id())->exists()) {
            $validated['is_default'] = true;
        }

        $address = UserAddress::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan',
            'address' => [
                'id' => $address->id,
                'label' => $address->label,
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'full_address' => $address->full_address,
                'is_default' => $address->is_default,
            ],
        ]);
    }
}
