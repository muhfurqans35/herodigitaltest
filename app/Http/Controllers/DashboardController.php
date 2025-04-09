<?php
namespace App\Http\Controllers;
use App\Models\Review;
use App\Models\User;
use App\Models\Service;
use App\Models\Booking;
class DashboardController extends Controller
{
    public function index()
    {
        $totalBookings = Booking::count();
        $totalPaidBookings = Booking::where('status', 'paid')->count();
        $totalServices = Service::count();
        $totalUsers = User::count();
        $totalReviews = Review::count();
        $services = Service::withSum([
            'bookings as booked_units' => function ($query) {
                $query->whereIn('status', ['paid', 'processing']);
            }
        ], 'units')->get(['id', 'name', 'total_sessions']);

        return inertia('Dashboard', [
            'stats' => [
                'bookings' => $totalBookings,
                'paidBookings' => $totalPaidBookings,
                'services' => $totalServices,
                'users' => $totalUsers,
                'reviews' => $totalReviews,
            ],
            'services' => $services,
        ]);
    }
}

