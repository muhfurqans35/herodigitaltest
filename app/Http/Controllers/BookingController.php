<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;
use Midtrans\Snap;
use Midtrans\Config;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
class BookingController extends Controller
{
    public function __construct()
    {

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');

        Config::$isProduction = false;

    }
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

        return redirect()->route('booking.payment', ['id' => $booking->id])
            ->with('success', 'Booking berhasil! Silakan lakukan pembayaran.');
    }

    public function payment(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Booking ini sudah diproses atau dibatalkan.');
        }

        try {
            $transactionDetails = [
                'transaction_details' => [
                    'order_id' => $booking->id,
                    'gross_amount' => $booking->total_price,
                ],
            ];

            // Dapatkan snap_token dari Midtrans
            $snapToken = Snap::getSnapToken($transactionDetails);

            \Log::info('Midtrans Response:', ['snap_token' => $snapToken]);

            // Kembalikan snap_token sebagai respons JSON
            return response()->json([
                'snap_token' => $snapToken,
            ]);

        } catch (\Exception $e) {
            \Log::error('Midtrans Error:', ['error' => $e->getMessage()]);

            return back()->with('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
        }
    }

    public function index()
    {
        $bookings = Booking::where('user_id', Auth::id())->orderBy('date', 'asc')->get();
        return Inertia::render('bookings/Index', ['bookings' => $bookings]);
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
}
