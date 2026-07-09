<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class KomerceService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected bool $cacheEnabled;

    public function __construct()
    {
        $this->apiKey = config('komerce.api_key');
        // Gunakan komerce base URL default (RajaOngkir endpoints)
        $this->baseUrl = config('komerce.base_url', 'https://rajaongkir.komerce.id/api/v1');
        $this->cacheEnabled = config('komerce.cache.enabled', true);
    }

    /**
     * Get all provinces
     */
    public function getProvinces(): array
    {
        $cacheKey = config('komerce.cache.prefix') . 'provinces';

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/province');

            if ($response->successful()) {
                $data = $response->json();
                $provinces = $data['rajaongkir']['results'] ?? ($data['data'] ?? []);

                // Format data
                $formatted = collect($provinces)->map(function ($province) {
                    return [
                        'id' => $province['province_id'] ?? $province['id'] ?? null,
                        'name' => $province['province'] ?? $province['name'] ?? null,
                    ];
                })->filter(fn($p) => $p['id'] !== null)->sortBy('name')->values()->all();

                // Cache
                if ($this->cacheEnabled && !empty($formatted)) {
                    Cache::put(
                        $cacheKey,
                        $formatted,
                        config('komerce.cache.ttl.provinces', 86400)
                    );
                }

                return $formatted;
            } else {
                Log::error('Komerce Get Provinces API Error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Komerce Get Provinces Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Search destination (City/Subdistrict)
     * For autocomplete UI
     */
    public function searchDestination(string $search): array
    {
        $cacheKey = config('komerce.cache.prefix') . 'search_' . md5($search);

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/destination/domestic-destination', [
                    'search' => $search
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $destinations = $data['data'] ?? [];

                if ($this->cacheEnabled && !empty($destinations)) {
                    // Cache search for 24 hours
                    Cache::put($cacheKey, $destinations, 86400);
                }

                return $destinations;
            } else {
                Log::error('Komerce Search Destination API Error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Komerce Search Destination Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Get cities by province ID (DEPRECATED - Komerce V2 uses search)
     */
    public function getCities(?int $provinceId = null): array
    {
        $cacheKey = config('komerce.cache.prefix') . 'cities_' . ($provinceId ?? 'all');

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = $provinceId ? ['province' => $provinceId] : [];

            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/city', $params);

            if ($response->successful()) {
                $data = $response->json();
                $cities = $data['rajaongkir']['results'] ?? ($data['data'] ?? []);

                // Format data
                $formatted = collect($cities)->map(function ($city) {
                    $type = $city['type'] ?? '';
                    $cityName = $city['city_name'] ?? $city['name'] ?? '';
                    $fullName = trim($type . ' ' . $cityName);

                    return [
                        'id' => $city['city_id'] ?? $city['id'] ?? null,
                        'province_id' => $city['province_id'] ?? null,
                        'province' => $city['province'] ?? null,
                        'type' => $type,
                        'name' => $fullName,
                        'city_name' => $cityName,
                        'postal_code' => $city['postal_code'] ?? null,
                    ];
                })->filter(fn($c) => $c['id'] !== null)->sortBy('name')->values()->all();

                // Cache
                if ($this->cacheEnabled && !empty($formatted)) {
                    Cache::put(
                        $cacheKey,
                        $formatted,
                        config('komerce.cache.ttl.cities', 86400)
                    );
                }

                return $formatted;
            } else {
                Log::error('Komerce Get Cities API Error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Komerce Get Cities Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Calculate shipping cost
     * Komerce V2 payload: origin, destination, weight, courier
     * Komerce V2 response: {meta, data: [{name, code, service, description, cost, etd}]}
     */
    public function calculateCost(int $origin, int $destination, int $weight, string $courier): array
    {
        $cacheKey = config('komerce.cache.prefix') . "cost_{$origin}_{$destination}_{$weight}_{$courier}";

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Komerce V2 API only needs origin, destination, weight, courier
            $payload = [
                'origin'      => $origin,
                'destination' => $destination,
                'weight'      => $weight,
                'courier'     => $courier,
            ];

            $response = Http::asForm()->withHeaders(['key' => $this->apiKey])
                ->post($this->baseUrl . '/calculate/domestic-cost', $payload);

            if ($response->successful()) {
                $data = $response->json();
                // Komerce V2 returns flat array in 'data'
                $rawResults = $data['data'] ?? [];

                // Normalize to format: [{service, description, cost (int), etd}]
                $results = collect($rawResults)->map(function ($item) {
                    return [
                        'service'     => $item['service'] ?? '',
                        'description' => $item['description'] ?? '',
                        'cost'        => (int) ($item['cost'] ?? 0),
                        'etd'         => $item['etd'] ?? '',
                    ];
                })->filter(fn($s) => $s['cost'] > 0)->values()->all();

                if ($this->cacheEnabled && !empty($results)) {
                    Cache::put(
                        $cacheKey,
                        $results,
                        config('komerce.cache.ttl.cost', 3600)
                    );
                }

                return $results;
            } else {
                Log::error('Komerce Calculate Cost API Error', [
                    'status' => $response->status(),
                    'body'   => $response->json(),
                    'origin' => $origin,
                    'destination' => $destination,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Komerce Calculate Cost Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Get shipping cost for a specific courier
     * @param int|string $destination  Komerce destination ID
     */
    public function getCost($destination, int $weight, string $courier): array
    {
        $origin = (int) config('komerce.origin');
        $costs  = $this->calculateCost($origin, (int) $destination, $weight, $courier);

        return [
            'courier'  => $courier,
            'name'     => $this->getCourierInfo($courier)['name'] ?? strtoupper($courier),
            'services' => $costs,
        ];
    }

    /**
     * Get shipping cost for all enabled couriers
     */
    public function getMultipleCouriersCost(int $destination, int $weight): array
    {
        $couriers = $this->getEnabledCouriers();
        $results = [];

        foreach ($couriers as $courier) {
            $cost = $this->getCost($destination, $weight, $courier);
            if (!empty($cost['services'])) {
                $results[] = $cost;
            }
        }

        return $results;
    }

    /**
     * Get enabled couriers
     */
    public function getEnabledCouriers(): array
    {
        return collect(config('komerce.couriers', []))
            ->filter(fn($c) => $c['enabled'] ?? false)
            ->keys()
            ->all();
    }

    /**
     * Get courier info
     */
    public function getCourierInfo(string $courierCode): array
    {
        return config("komerce.couriers.{$courierCode}", []);
    }

    /**
     * Track shipment
     */
    public function trackShipment(string $awb, string $courier): array
    {
        // Not implemented for starter API usually, or return dummy/empty
        return [];
    }
}
