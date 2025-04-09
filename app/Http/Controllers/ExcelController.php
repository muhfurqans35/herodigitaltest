<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ExcelExportJob;
use App\Jobs\ExcelImportJob;
use Illuminate\Support\Facades\DB;


class ExcelController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'fields' => 'required|array|min:1',
        ]);

        ExcelExportJob::dispatch($request->model, $request->fields);

        return back()->with('success', 'Export sedang diproses di background.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'fields' => 'required|array|min:1',
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $path = $request->file('file')->store('imports');

        ExcelImportJob::dispatch($request->model, $request->fields, $path);

        return back()->with('success', 'Import sedang diproses di background.');
    }


    public function getColumns(Request $request)
    {
        $model = $request->model;

        $validModels = ['users', 'services', 'bookings', 'reviews'];

        if (!in_array($model, $validModels)) {
            return response()->json(['error' => 'Model tidak valid'], 400);
        }

        // Ambil kolom dari tabel yang sesuai
        $columns = DB::getSchemaBuilder()->getColumnListing($model);

        return response()->json($columns);
    }
}
