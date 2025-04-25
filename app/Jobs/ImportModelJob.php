<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GenericImport;

class ImportModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $model;
    protected string $path;

    public function __construct(string $model, string $path)
    {
        $this->model = $model;
        $this->path = $path;
    }

    public function handle()
    {
        $fullPath = storage_path("app/{$this->path}");

        if (!file_exists($fullPath)) {
            \Log::error("Import file not found: {$fullPath}");
            throw new \Exception("File not found: {$fullPath}");
        }

        $modelClass = 'App\\Models\\' . \Str::studly($this->model);
        Excel::import(new GenericImport($modelClass), $fullPath);
    }

}