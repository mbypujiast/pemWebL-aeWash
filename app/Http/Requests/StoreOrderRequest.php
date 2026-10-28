<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    // Akses sudah dibatasi middleware role di route
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Pilih customer lama ATAU isi data customer baru
            'customer_id'        => ['nullable', 'required_without:new_customer_name', 'exists:customers,id'],
            'new_customer_name'  => ['nullable', 'required_without:customer_id', 'string', 'max:100'],
            'new_customer_phone' => ['nullable', 'required_with:new_customer_name', 'string', 'max:20'],

            'notes' => ['nullable', 'string', 'max:500'],

            // Item cucian (baris dinamis)
            'items'                => ['required', 'array', 'min:1'],
            'items.*.service_id'   => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'items.*.fabric_type'  => ['required', 'string', 'max:100'],
            'items.*.weight_kg'    => ['required', 'numeric', 'min:0.1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required_without'       => 'Pilih customer atau isi data customer baru.',
            'new_customer_name.required_without' => 'Pilih customer atau isi nama customer baru.',
            'new_customer_phone.required_with'   => 'Nomor HP customer baru wajib diisi.',
            'items.required'                     => 'Tambahkan minimal satu item cucian.',
            'items.min'                          => 'Tambahkan minimal satu item cucian.',
            'items.*.service_id.required'        => 'Layanan pada setiap item wajib dipilih.',
            'items.*.service_id.exists'          => 'Layanan yang dipilih tidak valid.',
            'items.*.fabric_type.required'       => 'Jenis kain pada setiap item wajib diisi.',
            'items.*.weight_kg.required'         => 'Berat pada setiap item wajib diisi.',
            'items.*.weight_kg.numeric'          => 'Berat harus berupa angka.',
            'items.*.weight_kg.min'              => 'Berat minimal 0,1 kg.',
            'items.*.weight_kg.max'              => 'Berat maksimal 100 kg per item.',
        ];
    }
}
