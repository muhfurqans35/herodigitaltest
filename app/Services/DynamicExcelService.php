<?php
namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Exports\GenericExport;
use App\Imports\GenericImport;

class DynamicExcelService
{
    public function getModelClass(string $model): ?string
    {
        $modelClass = 'App\\Models\\' . Str::studly($model);
        return class_exists($modelClass) ? $modelClass : null;
    }

    public function getModelFields(string $model): array
    {
        $modelClass = $this->getModelClass($model);
        if (!$modelClass)
            return [];

        return Schema::getColumnListing((new $modelClass)->getTable());
    }

    public function export(string $model)
    {
        $modelClass = $this->getModelClass($model);
        if (!$modelClass)
            throw new \Exception("Model tidak ditemukan");

        $data = $modelClass::all();
        $fields = $this->getModelFields($model);

        return Excel::download(new GenericExport($data, $fields), "$model.xlsx");
    }

    public function import(string $model, $file)
    {
        $modelClass = $this->getModelClass($model);
        if (!$modelClass)
            throw new \Exception("Model tidak ditemukan");

        Excel::import(new GenericImport($modelClass), $file);
    }
}