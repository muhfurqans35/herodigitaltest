<?php

namespace App\Imports;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DynamicImport implements ToCollection
{
    protected string $model;
    protected array $fields;

    public function __construct(string $model, array $fields)
    {
        /**
         * @return array
         */
        /*************  ✨ Windsurf Command ⭐  *************/
        /*******  a94ea2a0-5216-49fc-b2e3-51d8ba8f13f6  *******/
        $this->model = 'App\\Models\\' . Str::studly($model);
        $this->fields = $fields;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $data = [];

            foreach ($this->fields as $i => $field) {
                $data[$field] = $row[$i] ?? null;
            }

            $this->model::create($data);
        }
    }
}