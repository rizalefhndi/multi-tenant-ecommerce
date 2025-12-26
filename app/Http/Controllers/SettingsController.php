<?php

namespace App\Http\Controllers;

use App\Models\TenantSetting;
use App\Services\TenantThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    protected TenantThemeService $themeService;

    public function __construct(TenantThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * Display settings index/overview
     */
    public function index(): Response
    {
        return Inertia::render('Settings/Index', [
            'sections' => [
                ['key' => 'theme', 'title' => 'Tampilan Toko', 'description' => 'Logo, warna, font'],
                ['key' => 'store', 'title' => 'Informasi Toko', 'description' => 'Nama, alamat, kontak'],
                ['key' => 'payment', 'title' => 'Pembayaran', 'description' => 'Rekening, payment gateway'],
                ['key' => 'shipping', 'title' => 'Pengiriman', 'description' => 'Kurir, asal pengiriman'],
            ],
        ]);
    }

    /**
     * Display theme settings
     */
    public function theme(): Response
    {
        return Inertia::render('Settings/Theme', [
            'settings' => $this->themeService->getThemeSettings(),
            'fonts' => $this->themeService->getAvailableFonts(),
        ]);
    }

    /**
     * Update theme settings
     */
    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'font_family' => 'nullable|string|max:50',
            'dark_mode' => 'nullable|boolean',
        ]);

        $this->themeService->updateTheme($validated);

        return back()->with('success', 'Tema berhasil diperbarui');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        // Delete old logo if exists
        $oldLogo = TenantSetting::getValue('logo_url', null, TenantSetting::GROUP_THEME);
        if ($oldLogo) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogo));
        }

        // Store new logo
        $path = $request->file('logo')->store('logos', 'public');
        TenantSetting::setValue('logo_url', '/storage/' . $path, TenantSetting::GROUP_THEME);

        return back()->with('success', 'Logo berhasil diupload');
    }

    /**
     * Upload favicon
     */
    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:png,ico|max:512',
        ]);

        $path = $request->file('favicon')->store('favicons', 'public');
        TenantSetting::setValue('favicon_url', '/storage/' . $path, TenantSetting::GROUP_THEME);

        return back()->with('success', 'Favicon berhasil diupload');
    }

    /**
     * Upload banner
     */
    public function uploadBanner(Request $request)
    {
        $request->validate([
            'banner' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $path = $request->file('banner')->store('banners', 'public');
        TenantSetting::setValue('banner_url', '/storage/' . $path, TenantSetting::GROUP_THEME);

        return back()->with('success', 'Banner berhasil diupload');
    }

    /**
     * Display store settings
     */
    public function store(): Response
    {
        $defaults = TenantSetting::getDefaults()[TenantSetting::GROUP_STORE];
        $saved = TenantSetting::getByGroup(TenantSetting::GROUP_STORE);

        return Inertia::render('Settings/Store', [
            'settings' => array_merge($defaults, $saved),
        ]);
    }

    /**
     * Update store settings
     */
    public function updateStore(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:100',
            'store_description' => 'nullable|string|max:500',
            'store_tagline' => 'nullable|string|max:100',
            'store_email' => 'nullable|email|max:100',
            'store_phone' => 'nullable|string|max:20',
            'store_address' => 'nullable|string|max:300',
            'store_city_id' => 'nullable|integer',
            'store_province_id' => 'nullable|integer',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            'whatsapp' => 'nullable|string|max:20',
            'twitter' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
        ]);

        TenantSetting::setMultiple($validated, TenantSetting::GROUP_STORE);

        return back()->with('success', 'Informasi toko berhasil diperbarui');
    }

    /**
     * Display payment settings
     */
    public function payment(): Response
    {
        $defaults = TenantSetting::getDefaults()[TenantSetting::GROUP_PAYMENT];
        $saved = TenantSetting::getByGroup(TenantSetting::GROUP_PAYMENT);

        return Inertia::render('Settings/Payment', [
            'settings' => array_merge($defaults, $saved),
            'paymentMethods' => [
                'bank_transfer' => 'Transfer Bank Manual',
                'virtual_account' => 'Virtual Account',
                'credit_card' => 'Kartu Kredit',
                'gopay' => 'GoPay',
                'shopeepay' => 'ShopeePay',
                'qris' => 'QRIS',
            ],
        ]);
    }

    /**
     * Update payment settings
     */
    public function updatePayment(Request $request)
    {
        $validated = $request->validate([
            'enabled_payment_methods' => 'nullable|array',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_account_holder' => 'nullable|string|max:100',
            'midtrans_enabled' => 'nullable|boolean',
        ]);

        TenantSetting::setMultiple($validated, TenantSetting::GROUP_PAYMENT);

        return back()->with('success', 'Pengaturan pembayaran berhasil diperbarui');
    }

    /**
     * Display shipping settings
     */
    public function shipping(): Response
    {
        $defaults = TenantSetting::getDefaults()[TenantSetting::GROUP_SHIPPING];
        $saved = TenantSetting::getByGroup(TenantSetting::GROUP_SHIPPING);

        return Inertia::render('Settings/Shipping', [
            'settings' => array_merge($defaults, $saved),
            'couriers' => config('rajaongkir.couriers', []),
        ]);
    }

    /**
     * Update shipping settings
     */
    public function updateShipping(Request $request)
    {
        $validated = $request->validate([
            'origin_city_id' => 'nullable|integer',
            'enabled_couriers' => 'nullable|array',
            'free_shipping_min_order' => 'nullable|numeric|min:0',
        ]);

        TenantSetting::setMultiple($validated, TenantSetting::GROUP_SHIPPING);

        return back()->with('success', 'Pengaturan pengiriman berhasil diperbarui');
    }

    /**
     * Get CSS variables for frontend
     */
    public function getCss()
    {
        $css = $this->themeService->getCssString();

        return response($css, 200, [
            'Content-Type' => 'text/css',
        ]);
    }

    /**
     * Get theme data as JSON (for API)
     */
    public function getThemeJson()
    {
        return response()->json([
            'theme' => $this->themeService->getThemeSettings(),
            'cssVariables' => $this->themeService->getCssVariables(),
            'fontUrl' => $this->themeService->getGoogleFontsUrl(),
        ]);
    }
}
