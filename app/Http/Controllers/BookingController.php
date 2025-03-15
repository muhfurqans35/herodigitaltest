<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'service' => 'required|in:ps4,ps5',
            'session' => 'required|integer|min:1'
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        $basePrice = $request->service === 'ps4' ? 30000 : 40000;
        $bookingDate = Carbon::parse($request->date)->toDateString();
        $isWeekend = Carbon::parse($bookingDate)->isWeekend();
        $surcharge = $isWeekend ? 50000 : 0;
        $totalPrice = ($basePrice * $request->session) + $surcharge;

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'date' => $bookingDate,
            'service' => $request->service,
            'session' => $request->session,
            'total_price' => $totalPrice,
            'status' => 'pending'
        ]);

        return redirect()->route('booking.index')
            ->with('success', 'Booking berhasil! Silakan lakukan pembayaran.');
    }

    public function index()
    {
        $bookings = Booking::where('user_id', Auth::id())->orderBy('date', 'asc')->get();
        return Inertia::render('bookings/Index', [
            'bookings' => $bookings,
            'title' => 'Daftar Booking'
        ]);
    }

    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu untuk membuat booking.');
        }
        return Inertia::render('bookings/create/Index', [
            'title' => 'Buat Booking'
        ]);
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Booking tidak dapat dihapus karena statusnya sudah diproses.');
        }

        $booking->delete();

        return redirect()->route('booking.index')->with('success', 'Booking berhasil dihapus.');
    }
}
