<?php

namespace App\Support;

class ValidationMessages
{
    /** Pesan validasi umum (berlaku untuk semua field). Nama field diatur lewat $attributes. */
    public static function id(): array
    {
        return [
            'required'       => ':attribute wajib diisi.',
            'email'          => ':attribute harus berupa alamat email yang valid.',
            'unique'         => ':attribute sudah dipakai.',
            'confirmed'      => 'Konfirmasi :attribute tidak cocok.',
            'numeric'        => ':attribute harus berupa angka.',
            'integer'        => ':attribute harus berupa bilangan bulat.',
            'exists'         => ':attribute yang dipilih tidak valid.',
            'in'             => ':attribute yang dipilih tidak valid.',
            'date'           => ':attribute bukan tanggal yang valid.',
            'after_or_equal' => ':attribute tidak boleh lebih awal dari :date.',
            'min.string'     => ':attribute minimal :min karakter.',
            'max.string'     => ':attribute maksimal :max karakter.',
            'min.numeric'    => ':attribute minimal :min.',
            'max.numeric'    => ':attribute maksimal :max.',
        ];
    }
}
