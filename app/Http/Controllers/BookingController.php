<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Service;

class BookingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $isAdmin = $user->roles()->whereIn('name', ['admin', 'user admin'])->exists();

        $bookings = Booking::with('service')
            ->when(!$isAdmin, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        $services = Service::select('id', 'name')->get();

        return Inertia::render('bookings/Index', [
            'bookings' => $bookings,
            'services' => $services,
        ]);
    }

    public function dashboardindex()
    {
        $bookings = Booking::with(['user', 'service'])->get();

        $services = Service::select('id', 'name')->get();

        return Inertia::render('dashboard/bookings/Index', [
            'bookings' => $bookings,
            'services' => $services,
        ]);
    }


    public function create()
    {
        $services = Service::all();
        return Auth::check()
            ? Inertia::render('bookings/create/Index', [
                'services' => $services
            ])
            : redirect('/login')->with('error', 'Silakan login terlebih dahulu untuk membuat booking.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'required|exists:services,id',
            'session' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'units' => 'required|integer|min:1'
        ]);

        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date);

        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = (clone $startTime)->addHours($request->session);


        $bookedUnits = Booking::where('service_id', $service->id)
            ->where('date', $date->toDateString())
            ->where('session', $request->session)
            ->sum('units');

        $availableUnits = $service->total_units - $bookedUnits;

        if ($availableUnits < $request->units) {
            return back()->with('error', "Sisa unit tidak mencukupi. Tersisa $availableUnits unit.");
        }

        $surcharge = $date->isWeekend() ? 10000 : 0;
        $totalPrice = ($service->price * $request->session * $request->units) + $surcharge;

        Booking::create([
            'user_id' => Auth::id(),
            'service_id' => $service->id,
            'service_name' => $service->name,
            'price_at_booking' => $service->price,
            'date' => $date->toDateString(),
            'session' => $request->session,
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'units' => $request->units,
            'total_price' => $totalPrice,
            'status' => 'pending'
        ]);

        return redirect()->route('booking.index')->with('success', 'Booking berhasil! Silakan lakukan pembayaran.');
    }



    public function update(Request $request, $id)
    {
        if ($request->has('start_time')) {
            $request->merge([
                'start_time' => substr($request->start_time, 0, 5),
            ]);
        }
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'required|exists:services,id',
            'session' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'units' => 'required|integer|min:1',
            'status' => 'sometimes|in:pending,paid,processing,canceled,finished'
        ]);

        $booking = Booking::findOrFail($id);
        $user = Auth::user();
        if (
            !$user->roles->contains('name', 'admin') &&
            !$user->roles->contains('name', 'superadmin') &&
            ($booking->user_id !== $user->id || $booking->status !== 'pending')
        ) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses atau booking tidak bisa diubah.');
        }
        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date);

        $bookedUnits = Booking::where('service_id', $service->id)
            ->where('date', $date->toDateString())
            ->where('session', $request->session)
            ->where('id', '!=', $booking->id)
            ->sum('units');

        $availableUnits = $service->total_units - $bookedUnits;

        if ($availableUnits < $request->units) {
            return back()->with('error', "Sisa unit tidak mencukupi. Tersisa $availableUnits unit.");
        }

        $surcharge = $date->isWeekend() ? 10000 : 0;
        $totalPrice = ($service->price * $request->session * $request->units) + $surcharge;

        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = (clone $startTime)->addHours($request->session);

        $booking->update([
            'date' => $date->toDateString(),
            'service_id' => $service->id,
            'service_name' => $service->name,
            'price_at_booking' => $service->price,
            'session' => $request->session,
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'units' => $request->units,
            'total_price' => $totalPrice,
        ]);

        return redirect()->back()->with('success', 'Booking berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,processing,canceled,finished',
        ]);

        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        if (
            !$user->roles->contains('name', 'admin') &&
            !$user->roles->contains('name', 'superadmin') &&
            ($booking->user_id !== $user->id || $booking->status !== 'pending')
        ) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses atau booking tidak bisa diubah.');
        }
        $booking->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Status berhasil diupdate');
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();
        if (
            !$user->roles->contains('name', 'admin') &&
            !$user->roles->contains('name', 'superadmin') &&
            ($booking->user_id !== $user->id || $booking->status !== 'pending')
        ) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses atau booking tidak bisa diubah.');
        }
        $booking->delete();

        return redirect()->back()->with('success', 'Booking berhasil dihapus.');
    }
    public function audits(Booking $booking)
    {
        return response()->json($booking->audits()->latest()->get());
    }


}

