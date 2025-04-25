<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SubscriptionPackage;
use App\Models\Subscription;

// Untuk pembelian dengan midtrans/apapun dan masih belum berjalan
class SubscriptionController extends Controller
{
    public function index()
    {
        $packages = SubscriptionPackage::all();

        // Ambil langganan aktif menggunakan metode activeSubscription
        $subscription = auth()->user()->activeSubscription();

        return Inertia::render('SubscriptionPage', [
            'packages' => $packages,
            'activeSubscription' => $subscription,
        ]);
    }


    public function select(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:subscription_packages,id',
        ]);

        $user = auth()->user();

        // Replace existing subscription or create new one
        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_package_id' => $request->package_id,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]
        );

        return redirect()->route('subscription.index')->with('success', 'Subscription updated.');
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'package_id' => ['required', 'exists:subscription_packages,id'],
            'duration_months' => ['required', 'integer', 'min:1'],
        ]);

        $startDate = now();
        $endDate = now()->addMonths($request->duration_months);

        Subscription::create([
            'user_id' => $request->user_id,
            'package_id' => $request->package_id,
            'starts_at' => $startDate,
            'ends_at' => $endDate,
        ]);

        return back()->with('success', 'Subscription berhasil ditambahkan.');
    }

}
