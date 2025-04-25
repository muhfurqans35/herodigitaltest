<?php
namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{

    public function index()
    {
        $services = Service::latest()->get();
        return Inertia::render('dashboard/services/ServiceManagement', [
            'services' => $services
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|integer|min:0',
            'fields' => 'nullable|array',
            'fields.*' => 'array',
            'fields.*.*.name' => 'required|string|max:255',
            'fields.*.*.price' => 'required|integer|min:0',
        ]);


        Service::create($validated);

        return redirect()->back()->with('success', 'Layanan berhasil ditambahkan');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|integer|min:0',
            'fields' => 'nullable|array',
            'fields.*' => 'array',
            'fields.*.*.name' => 'required|string|max:255',
            'fields.*.*.price' => 'required|integer|min:0',
        ]);


        $service->update($validated);

        return redirect()->back()->with('success', 'Layanan berhasil diperbarui');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->back()->with('success', 'Layanan berhasil dihapus');
    }
}

