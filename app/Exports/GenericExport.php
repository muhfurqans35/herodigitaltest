<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $fields;

    public function __construct($data, $fields)
    {
        $this->data = $data;
        $this->fields = $fields;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return collect($this->fields)->map(fn($field) => $item->$field);
        });
    }

    public function headings(): array
    {
        return $this->fields;
    }
}