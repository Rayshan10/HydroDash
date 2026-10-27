<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi
    |--------------------------------------------------------------------------
    |
    | Baris-baris berikut adalah pesan validasi standar Laravel versi Indonesia.
    | Token :attribute, :min, :max, dst. diganti Laravel secara otomatis.
    |
    */

    'accepted'             => 'Kolom :attribute harus menerima syarat.',
    'accepted_if'          => 'Kolom :attribute harus menerima syarat saat :other berisi :value.',
    'active_url'           => 'Kolom :attribute harus merupakan alamat URL yang valid.',
    'after'                => 'Kolom :attribute harus merupakan tanggal setelah :date.',
    'after_or_equal'       => 'Kolom :attribute harus merupakan tanggal setelah atau sama dengan :date.',
    'alnum'                => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'alpha'                => 'Kolom :attribute hanya boleh berisi huruf.',
    'alpha_dash'           => 'Kolom :attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num'            => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'array'                => 'Kolom :attribute harus berupa array.',
    'ascii'                => 'Kolom :attribute hanya boleh berisi karakter dan simbol alfanumerik satu byte.',
    'before'               => 'Kolom :attribute harus merupakan tanggal sebelum :date.',
    'before_or_equal'      => 'Kolom :attribute harus merupakan tanggal sebelum atau sama dengan :date.',
    'between'              => [
        'array'   => 'Kolom :attribute harus memiliki antara :min dan :max item.',
        'file'    => 'Kolom :attribute harus antara :min dan :max kilobyte.',
        'numeric' => 'Kolom :attribute harus di antara :min dan :max.',
        'string'  => 'Kolom :attribute harus di antara :min dan :max karakter.',
    ],
    'boolean'              => 'Kolom :attribute harus bernilai true atau false.',
    'can'                  => 'Kolom :attribute tidak mengandung izin yang diperlukan.',
    'confirmed'            => 'Konfirmasi :attribute tidak sesuai.',
    'contains'             => 'Kolom :attribute tidak mengandung nilai yang diperlukan.',
    'current_password'     => 'Kata sandi saat ini salah.',
    'date'                 => 'Kolom :attribute harus berupa tanggal yang valid.',
    'date_equals'          => 'Kolom :attribute harus merupakan tanggal sama dengan :date.',
    'date_format'          => 'Kolom :attribute tidak sesuai format :format.',
    'decimal'              => 'Kolom :attribute harus memiliki :decimal digit desimal.',
    'decline'              => 'Kolom :attribute harus menolak syarat.',
    'decline_if'           => 'Kolom :attribute harus menolak syarat saat :other berisi :value.',
    'different'            => 'Kolom :attribute dan :other harus berbeda.',
    'digits'               => 'Kolom :attribute harus :digits digit.',
    'digits_between'       => 'Kolom :attribute harus di antara :min dan :max digit.',
    'dimensions'           => 'Kolom :attribute tidak memiliki dimensi gambar yang sesuai.',
    'distinct'             => 'Kolom :attribute memiliki nilai duplikat.',
    'doesnt_end_with'      => 'Kolom :attribute tidak boleh diakhiri dengan salah satu dari berikut: :values.',
    'doesnt_start_with'    => 'Kolom :attribute tidak boleh diawali dengan salah satu dari berikut: :values.',
    'email'                => 'Kolom :attribute harus merupakan alamat email yang valid.',
    'ends_with'            => 'Kolom :attribute harus diakhiri dengan salah satu dari berikut: :values.',
    'enum'                 => 'Kolom :attribute yang dipilih tidak valid.',
    'exists'               => 'Kolom :attribute yang dipilih tidak valid.',
    'extensions'           => 'Kolom :attribute harus memiliki salah satu dari ekstensi berikut: :values.',
    'file'                 => 'Kolom :attribute harus berupa berkas.',
    'filled'               => 'Kolom :attribute wajib diisi.',
    'gt'                   => [
        'array'   => 'Kolom :attribute harus memiliki lebih dari :value item.',
        'file'    => 'Kolom :attribute harus lebih dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus lebih dari :value.',
        'string'  => 'Kolom :attribute harus lebih dari :value karakter.',
    ],
    'gte'                  => [
        'array'   => 'Kolom :attribute harus memiliki :value item atau lebih.',
        'file'    => 'Kolom :attribute harus :value kilobyte atau lebih.',
        'numeric' => 'Kolom :attribute harus :value atau lebih.',
        'string'  => 'Kolom :attribute harus :value karakter atau lebih.',
    ],
    'hex_color'            => 'Kolom :attribute harus berupa kode warna hex yang valid.',
    'image'                => 'Kolom :attribute harus berupa gambar.',
    'in'                   => 'Kolom :attribute yang dipilih tidak valid.',
    'in_array'             => 'Kolom :attribute harus ada di dalam :other.',
    'integer'              => 'Kolom :attribute harus berupa bilangan bulat.',
    'ip'                   => 'Kolom :attribute harus berupa alamat IP yang valid.',
    'ipv4'                 => 'Kolom :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6'                 => 'Kolom :attribute harus berupa alamat IPv6 yang valid.',
    'json'                 => 'Kolom :attribute harus merupakan string JSON yang valid.',
    'list'                 => 'Kolom :attribute harus berupa list.',
    'lowercase'            => 'Kolom :attribute harus berupa huruf kecil.',
    'lt'                   => [
        'array'   => 'Kolom :attribute harus memiliki kurang dari :value item.',
        'file'    => 'Kolom :attribute harus kurang dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus kurang dari :value.',
        'string'  => 'Kolom :attribute harus kurang dari :value karakter.',
    ],
    'lte'                  => [
        'array'   => 'Kolom :attribute harus memiliki maksimal :value item.',
        'file'    => 'Kolom :attribute harus maksimal :value kilobyte.',
        'numeric' => 'Kolom :attribute harus maksimal :value.',
        'string'  => 'Kolom :attribute harus maksimal :value karakter.',
    ],
    'mac_address'          => 'Kolom :attribute harus berupa alamat MAC yang valid.',
    'max'                  => [
        'array'   => 'Kolom :attribute tidak boleh lebih dari :max item.',
        'file'    => 'Kolom :attribute tidak boleh lebih dari :max kilobyte.',
        'numeric' => 'Kolom :attribute tidak boleh lebih dari :max.',
        'string'  => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits'           => 'Kolom :attribute tidak boleh lebih dari :max digit.',
    'mimes'                => 'Kolom :attribute harus berupa berkas bertipe: :values.',
    'mime_types'           => 'Kolom :attribute harus berupa berkas bertipe: :values.',
    'min'                  => [
        'array'   => 'Kolom :attribute minimal :min item.',
        'file'    => 'Kolom :attribute minimal :min kilobyte.',
        'numeric' => 'Kolom :attribute minimal :min.',
        'string'  => 'Kolom :attribute minimal :min karakter.',
    ],
    'min_digits'           => 'Kolom :attribute minimal :min digit.',
    'missing'              => 'Kolom :attribute harus kosong.',
    'missing_if'           => 'Kolom :attribute harus kosong saat :other berisi :value.',
    'missing_unless'       => 'Kolom :attribute harus kosong kecuali :other berisi :value.',
    'missing_with'         => 'Kolom :attribute harus kosong saat terdapat :values.',
    'missing_with_all'     => 'Kolom :attribute harus kosong saat terdapat nilai :values.',
    'multiple_of'          => 'Kolom :attribute harus merupakan multiple of :value.',
    'not_in'               => 'Kolom :attribute yang dipilih tidak valid.',
    'not_regex'            => 'Format kolom :attribute tidak valid.',
    'numeric'              => 'Kolom :attribute harus berupa angka.',
    'password'             => [
        'letters'       => 'Kolom :attribute harus mengandung setidaknya satu huruf.',
        'mixed'         => 'Kolom :attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers'       => 'Kolom :attribute harus mengandung setidaknya satu angka.',
        'symbols'       => 'Kolom :attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => 'Kolom :attribute muncul di kebocoran data yang diketahui. Silakan pilih :attribute yang lain.',
    ],
    'present'              => 'Kolom :attribute wajib ada.',
    'present_if'           => 'Kolom :attribute wajib ada saat :other berisi :value.',
    'present_unless'       => 'Kolom :attribute wajib ada kecuali :other berisi :value.',
    'present_with'         => 'Kolom :attribute wajib ada saat terdapat :values.',
    'present_with_all'     => 'Kolom :attribute wajib ada saat terdapat nilai :values.',
    'prohibited'           => 'Kolom :attribute tidak diperbolehkan.',
    'prohibited_if'        => 'Kolom :attribute tidak diperbolehkan saat :other berisi :value.',
    'prohibited_unless'    => 'Kolom :attribute tidak diperbolehkan kecuali :other berisi :value.',
    'prohibited_with'      => 'Kolom :attribute tidak diperbolehkan saat terdapat :values.',
    'prohibited_with_all'  => 'Kolom :attribute tidak diperbolehkan saat terdapat nilai :values.',
    'regex'                => 'Format kolom :attribute tidak valid.',
    'required'             => 'Kolom :attribute wajib diisi.',
    'required_array_keys'  => 'Kolom :attribute harus mengandung entri untuk: :values.',
    'required_if'          => 'Kolom :attribute wajib diisi saat :other berisi :value.',
    'required_if_accepted' => 'Kolom :attribute wajib diisi saat :other diterima.',
    'required_unless'      => 'Kolom :attribute wajib diisi kecuali :other berisi :values.',
    'required_with'        => 'Kolom :attribute wajib diisi saat terdapat :values.',
    'required_with_all'    => 'Kolom :attribute wajib diisi saat terdapat nilai :values.',
    'required_without'     => 'Kolom :attribute wajib diisi saat tidak terdapat :values.',
    'required_without_all' => 'Kolom :attribute wajib diisi saat tidak terdapat nilai :values.',
    'same'                 => 'Kolom :attribute dan :other harus sama.',
    'starts_with'          => 'Kolom :attribute harus diawali dengan salah satu dari berikut: :values.',
    'string'               => 'Kolom :attribute harus berupa teks.',
    'timezone'             => 'Kolom :attribute harus merupakan zona waktu yang valid.',
    'unique'               => 'Kolom :attribute sudah digunakan sebelumnya.',
    'uploaded'             => 'Gagal mengunggah :attribute.',
    'uppercase'            => 'Kolom :attribute harus berupa huruf besar.',
    'url'                  => 'Kolom :attribute harus berupa URL yang valid.',
    'ulid'                 => 'Kolom :attribute harus merupakan ULID yang valid.',
    'uuid'                 => 'Kolom :attribute harus merupakan UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi Kustom
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan pesan kustom untuk atribut tertentu
    | menggunakan konvensi "attribute.rule" serta menamai ulang kolom.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Atribut Kustom
    |--------------------------------------------------------------------------
    |
    | Baris berikut digunakan untuk memangkas string placeholder ":attribute"
    | menjadi sesuatu yang lebih enak dibaca, seperti "alamat email".
    |
    */

    'attributes' => [
        'ph'   => 'pH',
        'tds'  => 'TDS',
        'suhu' => 'suhu',
        'token' => 'token perangkat',
    ],

];
