<?php
namespace App\Jobs;

use App\Imports\DynamicImport;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExcelImportJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public string $model,
        public array $fields,
        public string $filePath
    ) {
    }

    public function handle()
    {
        Excel::import(new DynamicImport($this->model, $this->fields), $this->filePath);

        Log::info("Import complete from: {$this->filePath}");
    }
}
