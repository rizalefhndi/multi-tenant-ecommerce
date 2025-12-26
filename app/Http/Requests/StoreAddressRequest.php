<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'city_id' => ['nullable', 'string', 'max:10'],
            'province' => ['required', 'string', 'max:100'],
            'province_id' => ['nullable', 'string', 'max:10'],
            'postal_code' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'label' => 'label alamat',
            'recipient_name' => 'nama penerima',
            'phone' => 'nomor telepon',
            'address_line_1' => 'alamat',
            'address_line_2' => 'alamat tambahan',
            'city' => 'kota',
            'province' => 'provinsi',
            'postal_code' => 'kode pos',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi',
            'phone.required' => 'Nomor telepon wajib diisi',
            'address_line_1.required' => 'Alamat wajib diisi',
            'city.required' => 'Kota wajib diisi',
            'province.required' => 'Provinsi wajib diisi',
            'postal_code.required' => 'Kode pos wajib diisi',
        ];
    }
}
