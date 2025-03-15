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
            'date' => 'required|date|after_or_equal:today',
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

        Booking::create([
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

        if (Auth::user()->role === 'admin') {

            $bookings = Booking::all();
        } else {
            $bookings = Booking::where('user_id', Auth::id())->orderBy('date', 'asc')->get();
        }

        return inertia('bookings/Index', ['bookings' => $bookings]);
    }

    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu untuk membuat booking.');
        }
        return Inertia::render('bookings/create/Index');
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service' => 'required|in:ps4,ps5',
            'session' => 'required|integer|min:1|max:10',
        ]);

        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== Auth::id()) {
            return redirect()->route('booking.index')->with('error', 'Anda tidak memiliki akses untuk mengedit booking ini.');
        }

        $basePrice = $request->service === 'ps4' ? 30000 : 40000;
        $bookingDate = Carbon::parse($request->date)->toDateString();
        $isWeekend = Carbon::parse($bookingDate)->isWeekend();
        $surcharge = $isWeekend ? 50000 : 0;
        $totalPrice = ($basePrice * $request->session) + $surcharge;

        $booking->update([
            'date' => $bookingDate,
            'service' => $request->service,
            'session' => $request->session,
            'total_price' => $totalPrice,
        ]);

        return redirect()->route('booking.index')->with('success', 'Booking berhasil diperbarui.');
    }

}
