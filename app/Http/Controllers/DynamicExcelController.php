<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DynamicExcelService;
use App\Jobs\ExportModelJob;
use App\Jobs\ImportModelJob;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;

class DynamicExcelController extends Controller
{
    public function __construct(protected DynamicExcelService $excelService)
    {
    }

    public function index()
    {

        return Inertia::render('dashboard/excel/ExcelManagement');
    }


    public function export(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
        ]);

        $model = $request->input('model');
        $filename = now()->format('Ymd_His') . "_{$model}.xlsx";

        // Ambil semua field dari model secara dinamis
        $modelClass = 'App\\Models\\' . \Str::studly($model);
        $fields = (new $modelClass)->getFillable();

        ExportModelJob::dispatch($model, $filename, $fields);

        return response()->json(['message' => 'Export dalam antrian', 'file' => $filename]);
    }



    public function import(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        // Simpan file ke folder `imports` (bukan tmp)
        $filename = now()->format('Ymd_His') . '_' . $request->model . '.' . $request->file('file')->getClientOriginalExtension();
        $path = $request->file('file')->storeAs('imports', $filename); // ➜ storage/app/imports/...

        // Dispatch job dengan path relatif ke `storage/app`
        ImportModelJob::dispatch($request->model, $path);

        return response()->json(['message' => 'Import sedang diproses']);
    }



    public function availableModels()
    {
        return ['Service', 'Review', 'User'];
    }

    public function downloadExport($filename): StreamedResponse
    {
        $path = "exports/{$filename}";

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::disk('public')->download($path, $filename);
    }
}