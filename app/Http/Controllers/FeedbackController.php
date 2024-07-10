<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function store(StoreFeedbackRequest $request)
    {
        $validated = $request->validated();

        $feedback = Feedback::create($validated);

        return inertia('Feedback/Index');
    }
}
