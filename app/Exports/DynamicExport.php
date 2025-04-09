<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;

class DynamicExport implements FromArray
{
    public function __construct(
        protected Collection $data
    ) {
    }

    public function array(): array
    {
        return $this->data->toArray();
    }
}
