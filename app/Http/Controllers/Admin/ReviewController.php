<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'company']);

        // Filter by approval status
        if ($request->has('approved')) {
            if ($request->approved === '1') {
                $query->approved();
            } elseif ($request->approved === '0') {
                $query->pending();
            }
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        // Search by name, title, or content
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $reviews = $query->latest()
            ->paginate(15)
            ->through(function ($review) {
                return [
                    'id' => $review->id,
                    'name' => $review->name,
                    'title' => $review->title,
                    'content' => $review->content,
                    'rating' => $review->rating,
                    'approved' => $review->approved,
                    'user' => $review->user ? [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                    ] : null,
                    'company' => $review->company ? [
                        'id' => $review->company->id,
                        'name' => $review->company->name,
                    ] : null,
                    'created_at' => $review->created_at,
                ];
            });

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'filters' => $request->only(['approved', 'rating', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.reviews.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request)
    {
        $validated = $request->validated();

        Review::create($validated);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        return view('admin.reviews.edit', [
            'review' => $review,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        $validated = $request->validated();

        $review->update($validated);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review updated successfully.');
    }

    /**
     * Toggle approval status of a review.
     */
    public function approve(Review $review)
    {
        $review->update([
            'approved' => ! $review->approved,
        ]);

        $status = $review->approved ? 'approved' : 'unapproved';

        return back()->with('success', "Review {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }
}
