<?php

namespace App\Http\Controllers\v1\products;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\v1\products\ReviewService;
use App\Http\Resources\v1\products\ReviewResource;
use App\Http\Requests\v1\products\StoreReviewRequest;

class ReviewController extends Controller
{
   
    public function __construct(protected ReviewService $reviewService) {}
    
        public function store(StoreReviewRequest $request)
        {
            $review = $reviewService->store($request);
            return new ReviewResource($review);
        }
    
        public function byProduct($productId)
        {
            return ReviewResource::collection(
                $this->reviewService->getByProduct($productId)
            );
        }
    
        public function destroy($id)
        {
            $this->reviewService->delete($id);
            return response()->json(['message' => 'Review deleted successfully.']);
        }
}
    