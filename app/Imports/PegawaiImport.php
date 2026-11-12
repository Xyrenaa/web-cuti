<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class PegawaiImport implements WithMultipleSheets, WithEvents
{

    public function sheets(): array
    {
        return [
            'DATA PNS'  => new PegawaiSheetImport(),
            'DATA PPPK' => new PegawaiSheetImport(),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                User::whereDoesntHave('roles')
                    ->where('level_jabatan', 'Pegawai')
                    ->chunkById(200, function ($users) {
                        foreach ($users as $user) {
                            $user->assignRole('Pegawai');
                        }
                    });
            },
        ];
    }
}