<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'address_id' => ['required', 'exists:user_addresses,id'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,virtual_account,gopay,shopeepay,qris,cod'],
            'shipping_courier' => ['nullable', 'string', 'max:50'],
            'shipping_service' => ['nullable', 'string', 'max:50'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate address belongs to user
            $addressId = $this->input('address_id');
            if ($addressId) {
                $address = \App\Models\UserAddress::find($addressId);
                if (!$address || $address->user_id !== auth()->id()) {
                    $validator->errors()->add('address_id', 'Alamat tidak valid');
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'address_id' => 'alamat pengiriman',
            'payment_method' => 'metode pembayaran',
            'shipping_courier' => 'kurir pengiriman',
            'shipping_service' => 'layanan pengiriman',
            'shipping_cost' => 'biaya pengiriman',
            'customer_notes' => 'catatan',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'address_id.required' => 'Pilih alamat pengiriman',
            'address_id.exists' => 'Alamat pengiriman tidak ditemukan',
            'payment_method.required' => 'Pilih metode pembayaran',
            'payment_method.in' => 'Metode pembayaran tidak valid',
        ];
    }
}
