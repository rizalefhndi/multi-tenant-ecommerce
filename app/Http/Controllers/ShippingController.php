<?php

namespace App\Http\Controllers;

use App\Services\KomerceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    protected KomerceService $komerceService;

    public function __construct(KomerceService $komerceService)
    {
        $this->komerceService = $komerceService;
    }

    /**
     * Search destination (Autocomplete for Komerce)
     * 
     * Route: GET /api/shipping/search-destination
     */
    public function searchDestination(Request $request): JsonResponse
    {
        $search = $request->query('q', '');
        
        if (strlen($search) < 3) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $destinations = $this->komerceService->searchDestination($search);

        return response()->json([
            'success' => true,
            'data' => $destinations
        ]);
    }

    /**
     * Get all provinces
     * 
     * Route: GET /api/shipping/provinces
     */
    public function provinces(): JsonResponse
    {
        $provinces = $this->komerceService->getProvinces();

        return response()->json([
            'success' => true,
            'data' => $provinces,
        ]);
    }

    /**
     * Get cities by province
     * 
     * Route: GET /api/shipping/cities/{provinceId?}
     */
    public function cities(?int $provinceId = null): JsonResponse
    {
        $cities = $this->komerceService->getCities($provinceId);

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }

    /**
     * Get subdistricts by city (Pro only)
     * 
     * Route: GET /api/shipping/subdistricts/{cityId}
     */
    public function subdistricts(int $cityId): JsonResponse
    {
        $subdistricts = $this->rajaOngkirService->getSubdistricts($cityId);

        return response()->json([
            'success' => true,
            'data' => $subdistricts,
        ]);
    }

    /**
     * Search city by keyword
     * 
     * Route: GET /api/shipping/search-city?q=keyword
     */
    public function searchCity(Request $request): JsonResponse
    {
        $keyword = $request->get('q', '');

        if (strlen($keyword) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Keyword minimal 2 karakter',
            ], 400);
        }

        $cities = $this->rajaOngkirService->searchCity($keyword);

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }

    /**
     * Get available couriers
     * 
     * Route: GET /api/shipping/couriers
     */
    public function couriers(): JsonResponse
    {
        $couriers = collect(config('komerce.couriers', []))
            ->filter(fn($c) => $c['enabled'] ?? false)
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => $couriers,
        ]);
    }

    /**
     * Calculate shipping cost
     * 
     * Route: POST /api/shipping/cost
     */
    public function cost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'destination_id' => 'required|integer',
            'weight' => 'required|integer|min:1', // in grams
            'courier' => 'nullable|string',
        ]);

        // If specific courier
        if (!empty($validated['courier'])) {
            $result = $this->komerceService->getCost(
                $validated['destination_id'],
                $validated['weight'],
                $validated['courier']
            );

            return response()->json([
                'success' => true,
                'data' => [$result],
            ]);
        }

        // Get all enabled couriers
        $results = $this->komerceService->getMultipleCouriersCost(
            $validated['destination_id'],
            $validated['weight']
        );

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Track shipment (Pro only)
     * 
     * Route: POST /api/shipping/track
     */
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'awb' => 'required|string',
            'courier' => 'required|string',
        ]);

        $result = $this->komerceService->trackShipment(
            $validated['awb'],
            $validated['courier']
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat melacak pengiriman. Pastikan AWB dan kurir benar.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get shipping options for checkout
     * 
     * Route: POST /api/shipping/options
     */
    public function options(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city_id' => 'required|string', // Komerce destination ID (can be large int stored as string)
            'weight'  => 'required|integer|min:1',
        ]);

        $couriers = $this->komerceService->getEnabledCouriers();
        $options  = [];

        foreach ($couriers as $courierCode) {
            $cost = $this->komerceService->getCost(
                $validated['city_id'],
                $validated['weight'],
                $courierCode
            );

            if (!empty($cost['services'])) {
                $courierInfo = $this->komerceService->getCourierInfo($courierCode);

                foreach ($cost['services'] as $service) {
                    // Komerce V2: cost is a flat int, not nested array
                    $costValue = is_array($service['cost'])
                        ? ($service['cost'][0]['value'] ?? 0)
                        : ((int) $service['cost']);

                    $options[] = [
                        'courier_code'   => $courierCode,
                        'courier_name'   => $courierInfo['name'] ?? strtoupper($courierCode),
                        'courier_logo'   => $courierInfo['logo'] ?? null,
                        'service'        => $service['service'],
                        'description'    => $service['description'],
                        'cost'           => $costValue,
                        'formatted_cost' => 'Rp ' . number_format($costValue, 0, ',', '.'),
                        'etd'            => $service['etd'],
                        'value'          => $courierCode . '_' . $service['service'],
                    ];
                }
            }
        }

        // Sort by cost ascending
        usort($options, fn($a, $b) => $a['cost'] - $b['cost']);

        return response()->json([
            'success' => true,
            'data'    => $options,
        ]);
    }
}
