<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Booking;

class MidtransController extends Controller
{

    public function __construct()
    {
        Config::$serverKey = config('app.midtrans.server_key');
        Config::$isProduction = false;
    }
    public function payment(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json(['error' => 'Booking ini sudah diproses atau dibatalkan.'], 400);
        }

        try {
            if ($booking->snap_token && $booking->snap_token_expires_at > now()) {
                return response()->json([
                    'snap_token' => $booking->snap_token,
                ]);
            }
            $transactionDetails = [
                'transaction_details' => [
                    'order_id' => $booking->id . '-' . time(),
                    'gross_amount' => $booking->total_price,
                ],
            ];
            $snapToken = Snap::getSnapToken($transactionDetails);

            $booking->update([
                'snap_token' => $snapToken,
                'snap_token_expires_at' => now()->addHours(12),
            ]);

            return response()->json([
                'snap_token' => $snapToken,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat memproses pembayaran.', 'message' => $e->getMessage()], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        $transactionStatus = $request->transaction_status;
        $orderId = $request->order_id;
        $fraudStatus = $request->fraud_status;

        $booking = Booking::find($orderId);

        if (!$booking) {
            return response()->json(['status' => 'error', 'message' => 'Booking tidak ditemukan.'], 404);
        }
        if ($transactionStatus == 'capture' && $fraudStatus == 'accept') {
            $booking->update(['status' => 'finished']);
        } elseif ($transactionStatus == 'settlement') {
            $booking->update(['status' => 'finished']);
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $booking->update(['status' => 'canceled']);
        } else {
            $booking->update(['status' => 'pending']);
        }

        return response()->json(['status' => 'success']);
    }
}
