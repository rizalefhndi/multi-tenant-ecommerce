<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $accountType;
    protected bool $cacheEnabled;

    public function __construct()
    {
        $this->apiKey = config('rajaongkir.api_key');
        $this->accountType = config('rajaongkir.account_type', 'starter');
        $this->baseUrl = config('rajaongkir.base_url')[$this->accountType] ?? config('rajaongkir.base_url.starter');
        $this->cacheEnabled = config('rajaongkir.cache.enabled', true);
    }

    /**
     * Get all provinces
     */
    public function getProvinces(): array
    {
        $cacheKey = config('rajaongkir.cache.prefix') . 'provinces';

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/province');

            if ($response->successful()) {
                $data = $response->json();
                $provinces = $data['rajaongkir']['results'] ?? [];

                // Format data
                $formatted = collect($provinces)->map(function ($province) {
                    return [
                        'id' => $province['province_id'],
                        'name' => $province['province'],
                    ];
                })->sortBy('name')->values()->all();

                // Cache
                if ($this->cacheEnabled) {
                    Cache::put(
                        $cacheKey,
                        $formatted,
                        config('rajaongkir.cache.ttl.provinces', 86400)
                    );
                }

                return $formatted;
            }
        } catch (\Exception $e) {
            Log::error('RajaOngkir Get Provinces Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Get cities by province ID
     */
    public function getCities(?int $provinceId = null): array
    {
        $cacheKey = config('rajaongkir.cache.prefix') . 'cities_' . ($provinceId ?? 'all');

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = $provinceId ? ['province' => $provinceId] : [];

            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/city', $params);

            if ($response->successful()) {
                $data = $response->json();
                $cities = $data['rajaongkir']['results'] ?? [];

                // Format data
                $formatted = collect($cities)->map(function ($city) {
                    return [
                        'id' => $city['city_id'],
                        'province_id' => $city['province_id'],
                        'province' => $city['province'],
                        'type' => $city['type'], // Kabupaten / Kota
                        'name' => $city['type'] . ' ' . $city['city_name'],
                        'city_name' => $city['city_name'],
                        'postal_code' => $city['postal_code'],
                    ];
                })->sortBy('name')->values()->all();

                // Cache
                if ($this->cacheEnabled) {
                    Cache::put(
                        $cacheKey,
                        $formatted,
                        config('rajaongkir.cache.ttl.cities', 86400)
                    );
                }

                return $formatted;
            }
        } catch (\Exception $e) {
            Log::error('RajaOngkir Get Cities Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Get subdistricts by city ID (Pro account only)
     */
    public function getSubdistricts(int $cityId): array
    {
        if ($this->accountType !== 'pro') {
            return [];
        }

        $cacheKey = config('rajaongkir.cache.prefix') . 'subdistricts_' . $cityId;

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/subdistrict', ['city' => $cityId]);

            if ($response->successful()) {
                $data = $response->json();
                $subdistricts = $data['rajaongkir']['results'] ?? [];

                // Format data
                $formatted = collect($subdistricts)->map(function ($sub) {
                    return [
                        'id' => $sub['subdistrict_id'],
                        'city_id' => $sub['city_id'],
                        'name' => $sub['subdistrict_name'],
                    ];
                })->sortBy('name')->values()->all();

                // Cache
                if ($this->cacheEnabled) {
                    Cache::put(
                        $cacheKey,
                        $formatted,
                        config('rajaongkir.cache.ttl.subdistricts', 86400)
                    );
                }

                return $formatted;
            }
        } catch (\Exception $e) {
            Log::error('RajaOngkir Get Subdistricts Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Calculate shipping cost
     */
    public function getCost(
        int $destinationId,
        int $weight,
        string $courier,
        ?int $originId = null,
        string $destinationType = 'city'
    ): array {
        $origin = $originId ?? config('rajaongkir.origin');

        if (!$origin) {
            Log::error('RajaOngkir Origin not configured');
            return $this->getFallbackCost();
        }

        // Cache key
        $cacheKey = sprintf(
            '%scost_%s_%s_%d_%s',
            config('rajaongkir.cache.prefix'),
            $origin,
            $destinationId,
            $weight,
            $courier
        );

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = [
                'origin' => $origin,
                'destination' => $destinationId,
                'weight' => $weight,
                'courier' => strtolower($courier),
            ];

            // Pro account supports destinationType
            if ($this->accountType === 'pro') {
                $params['originType'] = config('rajaongkir.origin_type', 'city');
                $params['destinationType'] = $destinationType;
            }

            $response = Http::withHeaders(['key' => $this->apiKey])
                ->asForm()
                ->post($this->baseUrl . '/cost', $params);

            if ($response->successful()) {
                $data = $response->json();
                $results = $data['rajaongkir']['results'] ?? [];

                if (empty($results)) {
                    return $this->getFallbackCost();
                }

                $courierData = $results[0] ?? [];
                $costs = $courierData['costs'] ?? [];

                // Format response
                $formatted = [
                    'courier' => [
                        'code' => $courierData['code'] ?? $courier,
                        'name' => $courierData['name'] ?? strtoupper($courier),
                    ],
                    'services' => collect($costs)->map(function ($cost) {
                        return [
                            'service' => $cost['service'],
                            'description' => $cost['description'],
                            'cost' => $cost['cost'][0]['value'] ?? 0,
                            'etd' => $cost['cost'][0]['etd'] ?? '-',
                        ];
                    })->all(),
                ];

                // Cache
                if ($this->cacheEnabled) {
                    Cache::put(
                        $cacheKey,
                        $formatted,
                        config('rajaongkir.cache.ttl.cost', 3600)
                    );
                }

                return $formatted;
            }
        } catch (\Exception $e) {
            Log::error('RajaOngkir Get Cost Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return $this->getFallbackCost();
    }

    /**
     * Get shipping cost for multiple couriers
     */
    public function getMultipleCouriersCost(
        int $destinationId,
        int $weight,
        array $couriers = []
    ): array {
        if (empty($couriers)) {
            $couriers = $this->getEnabledCouriers();
        }

        $results = [];

        foreach ($couriers as $courier) {
            $cost = $this->getCost($destinationId, $weight, $courier);
            if (!empty($cost['services'])) {
                $results[] = $cost;
            }
        }

        return $results;
    }

    /**
     * Get enabled couriers from config
     */
    public function getEnabledCouriers(): array
    {
        $couriers = config('rajaongkir.couriers', []);

        return collect($couriers)
            ->filter(fn($c) => $c['enabled'] ?? false)
            ->keys()
            ->all();
    }

    /**
     * Get courier info by code
     */
    public function getCourierInfo(string $code): ?array
    {
        $couriers = config('rajaongkir.couriers', []);
        return $couriers[$code] ?? null;
    }

    /**
     * Get fallback cost when API fails
     */
    protected function getFallbackCost(): array
    {
        if (!config('rajaongkir.fallback.enabled', false)) {
            return ['courier' => null, 'services' => []];
        }

        $flatRate = config('rajaongkir.fallback.flat_rate', 15000);

        return [
            'courier' => [
                'code' => 'flat',
                'name' => 'Pengiriman Standard',
            ],
            'services' => [
                [
                    'service' => 'FLAT',
                    'description' => 'Tarif Flat',
                    'cost' => $flatRate,
                    'etd' => '3-7 hari',
                ],
            ],
        ];
    }

    /**
     * Look up city by keyword
     */
    public function searchCity(string $keyword): array
    {
        $cities = $this->getCities();

        return collect($cities)
            ->filter(function ($city) use ($keyword) {
                return str_contains(strtolower($city['name']), strtolower($keyword))
                    || str_contains(strtolower($city['city_name']), strtolower($keyword));
            })
            ->values()
            ->all();
    }

    /**
     * Track shipment (Pro account only)
     */
    public function trackShipment(string $awb, string $courier): ?array
    {
        if ($this->accountType !== 'pro') {
            return null;
        }

        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->asForm()
                ->post($this->baseUrl . '/waybill', [
                    'waybill' => $awb,
                    'courier' => strtolower($courier),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['rajaongkir']['result'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('RajaOngkir Track Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
