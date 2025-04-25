<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{

    public function index()
    {
        // Ambil data transaksi
        $transactions = Transaction::with('items')->get();

        return Inertia::render('dashboard/transaction/TransactionManagement', [
            'transactions' => $transactions
        ]);
    }

    public function create()
    {
        // Ambil data layanan
        $services = Service::all();

        return Inertia::render('dashboard/transaction/TransactionCreateEdit', [
            'services' => $services,
            'transaction' => null, // Karena create, tidak ada transaksi
        ]);
    }

    public function edit($id)
    {
        // Ambil transaksi berdasarkan id
        $transaction = Transaction::with('items')->findOrFail($id);
        $services = Service::all();

        return Inertia::render('dashboard/transaction/TransactionCreateEdit', [
            'services' => $services,
            'transaction' => $transaction, // Kirim data transaksi untuk di-edit
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_date' => 'required|date',
            'customer_name' => 'nullable|string',
            'meta' => 'nullable|array',
            'items' => 'required|array',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.fields' => 'nullable|array',
            'items.*.fields.*.name' => 'required|string',
            'items.*.fields.*.price' => 'required|integer|min:0',
            'items.*.fields.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($data) {
            $items = collect($data['items'])->map(function ($item) {

                $service = Service::findOrFail($item['service_id']);
                $basePrice = $service->base_price;

                // Calculate fields total price (sum of all field prices)
                $fieldPrice = 0;
                foreach ($item['fields'] ?? [] as $field) {
                    $fieldPrice += $field['price'] * ($field['quantity'] ?? 1);
                }

                // Price per unit is base price plus all fields price
                $pricePerUnit = $basePrice + $fieldPrice;

                // Subtotal is price per unit multiplied by item quantity
                $subtotal = $pricePerUnit * $item['quantity'];
                return [
                    'id' => Str::uuid(),
                    'service_id' => $item['service_id'],
                    'name' => $service->name,
                    'quantity' => $item['quantity'],
                    'price_per_unit' => $pricePerUnit,
                    'subtotal' => $subtotal,
                    'fields' => $item['fields'] ?? [],
                    'notes' => $item['notes'] ?? null,
                ];
            });

            $transaction = Transaction::create([
                'id' => Str::uuid(),
                'transaction_date' => $data['transaction_date'],
                'customer_name' => $data['customer_name'] ?? null,
                'total_price' => $items->sum('subtotal'),
                'meta' => $data['meta'] ?? [],
            ]);

            $transaction->items()->createMany($items);

            return to_route('transactions.index')->with('success', 'Transaksi berhasil disimpan.');
        });
    }
    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'transaction_date' => 'required|date',
            'customer_name' => 'nullable|string',
            'meta' => 'nullable|array',
            'items' => 'required|array',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.fields' => 'nullable|array',
            'items.*.fields.*.name' => 'required|string',
            'items.*.fields.*.price' => 'required|integer|min:0',
            'items.*.fields.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($transaction, $data) {
            $transaction->items()->delete(); // reset dulu itemnya

            $items = collect($data['items'])->map(function ($item) {
                $service = Service::findOrFail($item['service_id']);
                $basePrice = $service->base_price;
                $fieldPrice = collect($item['fields'] ?? [])->sum('price');
                $pricePerUnit = $basePrice + $fieldPrice;
                $subtotal = $pricePerUnit * $item['quantity'];

                return [
                    'id' => Str::uuid(),
                    'service_id' => $item['service_id'],
                    'name' => $service->name,
                    'quantity' => $item['quantity'],
                    'price_per_unit' => $pricePerUnit,
                    'subtotal' => $subtotal,
                    'fields' => $item['fields'] ?? [],
                    'notes' => $item['notes'] ?? null,
                ];
            });

            $transaction->update([
                'transaction_date' => $data['transaction_date'],
                'customer_name' => $data['customer_name'],
                'total_price' => $items->sum('subtotal'),
                'meta' => $data['meta'] ?? [],
            ]);

            $transaction->items()->createMany($items);

            return to_route('transactions.index')->with('success', 'Transaksi berhasil diperbarui.');
        });
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function audit(Transaction $transaction)
    {
        return response()->json([
            'audits' => $transaction->audits()->latest()->get(),
        ]);
    }

}
