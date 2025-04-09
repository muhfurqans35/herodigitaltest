<?php
namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('created_at', 'desc')->get();

        return Inertia::render('dashboard/services/Index', compact('services'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'total_units' => 'required|numeric',
            'manual_book' => 'nullable|file|mimes:pdf|between:100,500',
            'extra_info' => 'nullable|json',
        ]);

        $data = $request->only(['name', 'price', 'total_units', 'extra_info']);
        $data['extra_info'] = json_decode($data['extra_info'], true);

        if ($request->hasFile('manual_book')) {
            $path = $request->file('manual_book')->store('manual_books', 'public');
            $data['manual_book_path'] = $path;
            $data['manual_book_name'] = $request->file('manual_book')->getClientOriginalName();
        }

        Service::create($data);

        return redirect()->back()->with('success', 'Service berhasil dibuat');
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'total_units' => 'required|numeric',
            'manual_book' => 'nullable|file|mimes:pdf|between:100,500',
            'extra_info' => 'nullable|json',
        ]);

        $data = $request->only(['name', 'price', 'total_units', 'extra_info']);
        $data['extra_info'] = json_decode($data['extra_info'], true);

        if ($request->hasFile('manual_book')) {
            if ($service->manual_book_path) {
                Storage::disk('public')->delete($service->manual_book_path);
            }

            $path = $request->file('manual_book')->store('manual_books', 'public');
            $data['manual_book_path'] = $path;
            $data['manual_book_name'] = $request->file('manual_book')->getClientOriginalName();
        }

        $service->update($data);

        return redirect()->back()->with('success', 'Service berhasil diupdate');
    }

    public function destroy(Service $service)
    {
        if ($service->manual_book_path) {
            Storage::disk('public')->delete($service->manual_book_path);
        }

        $service->delete();

        return redirect()->back()->with('success', 'Service berhasil dihapus');
    }

    public function audits(Service $service)
    {
        return response()->json($service->audits()->latest()->get());
    }
}
