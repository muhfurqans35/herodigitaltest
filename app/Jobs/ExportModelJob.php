<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;
use Illuminate\Support\Str;

class ExportModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    protected $filename;
    protected $fields;

    public function __construct($model, $filename, $fields)
    {
        $this->model = $model;
        $this->filename = $filename;
        $this->fields = $fields;
    }

    public function handle()
    {
        $modelClass = 'App\\Models\\' . Str::studly($this->model);

        // Ambil data dari model dan pastikan berupa collection
        $data = $modelClass::select($this->fields)->get();

        // Simpan file export (misal ke storage/app/exports/)
        Excel::store(
            new GenericExport($data, $this->fields),
            'exports/' . $this->filename,
            'public'
        );

    }
}
