<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DreamTourRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DreamTourRequestController extends Controller
{
    /**
     * Display a listing of dream tour requests.
     */
    public function index(Request $request)
    {
        $requests = DreamTourRequest::query()
            ->when($request->input('search'), function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('destinations', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/DreamTourRequests/Index', [
            'requests' => $requests,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Display the specified dream tour request.
     */
    public function show(DreamTourRequest $dreamTourRequest)
    {
        return Inertia::render('Admin/DreamTourRequests/Show', [
            'request' => $dreamTourRequest
        ]);
    }

    /**
     * Update the status of the specified dream tour request.
     */
    public function update(Request $request, DreamTourRequest $dreamTourRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,contacted,completed,cancelled'
        ]);

        $dreamTourRequest->update($validated);

        return redirect()->back()->with('message', __('Status updated successfully!'));
    }
}
