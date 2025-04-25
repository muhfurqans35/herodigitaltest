<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SubscriptionPackage;
use App\Models\Subscription;
use Inertia\Inertia;

class SubscriptionManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('dashboard/subscription/SubscriptionManagement', [
            'users' => User::with(['subscription.package'])->select('id', 'name', 'email')->get(),
            'packages' => SubscriptionPackage::all(),
        ]);
    }

    public function data(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::with(['activeSubscription.package'])->findOrFail($request->user_id);

        return response()->json([
            'activeSubscription' => $user->active_subscription,
            'subscriptionHistory' => $user->subscriptions()->with('package')->latest()->get(),
        ]);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'package_id' => ['required', 'exists:subscription_packages,id'],
            'duration_months' => ['required', 'integer', 'min:1'],
        ]);

        $user = User::findOrFail($request->user_id);
        $package = SubscriptionPackage::findOrFail($request->package_id);
        $now = now();

        // Cancel existing active subscription
        $user->subscription()->where('ends_at', '>', $now)->update(['status' => 'cancelled']);

        $user->subscription()->create([
            'subscription_package_id' => $package->id,
            'starts_at' => $now,
            'ends_at' => $now->copy()->addMonths($request->duration_months),
            'status' => 'active',
        ]);

        return back()->with('success', 'Langganan berhasil ditambahkan.');
    }

    public function extend(Request $request)
    {
        $request->validate([
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'add_months' => ['required', 'integer', 'min:1'],
        ]);

        $subscription = Subscription::findOrFail($request->subscription_id);

        // Pastikan status aktif
        if ($subscription->status !== 'active') {
            return back()->with('error', 'Hanya langganan aktif yang dapat diperpanjang.');
        }

        $subscription->update([
            'ends_at' => \Carbon\Carbon::parse($subscription->ends_at)->addMonths($request->add_months),
        ]);

        return back()->with('success', 'Langganan berhasil diperpanjang.');
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'subscription_id' => ['required', 'exists:subscriptions,id'],
        ]);

        $subscription = Subscription::findOrFail($request->subscription_id);
        $subscription->update([
            'status' => 'cancelled',
            'ends_at' => now(),
        ]);

        return back()->with('success', 'Langganan berhasil dibatalkan.');
    }
}