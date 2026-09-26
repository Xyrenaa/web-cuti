<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapCutiImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'DATA CUTI'  => new DataCutiSheetImport(),
            'JATAH CUTI' => new JatahCutiSheetImport(),
        ];
    }
}