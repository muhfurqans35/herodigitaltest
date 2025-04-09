<?php
namespace App\Jobs;

use App\Exports\DynamicExport;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExcelExportJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public string $model,
        public array $fields
    ) {
    }

    public function handle()
    {
        $class = 'App\\Models\\' . Str::studly($this->model);
        $data = $class::select($this->fields)->get();
        $filename = "{$this->model}_export_" . now()->timestamp . ".xlsx";
        Excel::store(new DynamicExport($data), "exports/{$filename}");
        Log::info("File exported: exports/{$filename}");
    }
}
