<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Get approved reviews for public display.
     *
     * Query parameters:
     * - approved: boolean (default: true, only approved reviews)
     * - limit: integer (default: 8, max 50)
     * - rating: integer (filter by rating 1-5)
     */
    public function index(Request $request)
    {
        $query = Review::approved(); // Only approved reviews by default

        // Filter by rating if provided
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', (int) $request->rating);
        }

        // Get limit (default 8, max 50)
        $limit = min(50, max(1, (int) ($request->input('limit', 8))));

        $reviews = $query->latest()
            ->limit($limit)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'name' => $review->name,
                    'title' => $review->title,
                    'content' => $review->content,
                    'rating' => $review->rating,
                    'created_at' => $review->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'reviews' => $reviews,
            'count' => $reviews->count(),
        ]);
    }
}
