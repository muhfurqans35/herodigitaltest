<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;
class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::orderBy('created_at', 'desc')->get();

        return Inertia::render('dashboard/reviews/Index', compact('reviews'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        return Review::create($request->all());
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $review->update($request->all());

        return redirect()->back()->with('success', 'Review berhasil diperbarui');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->back()->with('success', 'Review berhasil dihapus');
    }
    public function audits(Review $review)
    {
        return response()->json($review->audits()->latest()->get());
    }
}