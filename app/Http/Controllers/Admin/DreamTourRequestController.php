<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DreamTourRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\DreamTourMail;

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
            'status' => 'required|in:pending,contacted,completed,cancelled',
            'admin_price' => 'nullable|numeric|min:0',
            'price_currency' => 'nullable|in:IDR,USD,MYR',
        ]);

        $dreamTourRequest->update($validated);

        return redirect()->back()->with('message', __('Status updated successfully!'));
    }

    /**
     * Send email notification to the dream tour requester.
     */
    public function notifyEmail(Request $request, DreamTourRequest $dreamTourRequest)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'subject' => 'nullable|string|max:255',
        ]);

        try {
            $subject = $validated['subject'] ?? 'Dream Tour Request Update - TripTrove';
            Mail::to($dreamTourRequest->email)->send(new DreamTourMail($dreamTourRequest, $validated['message'], $subject));
            return back()->with('message', 'Email notifikasi berhasil dikirim.');
        } catch (\Exception $e) {
            \Log::error('Failed to send dream tour email: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified dream tour request from storage.
     */
    public function destroy(DreamTourRequest $dreamTourRequest)
    {
        if ($dreamTourRequest->attachments) {
            foreach ($dreamTourRequest->attachments as $attachment) {
                Storage::disk('public')->delete($attachment);
            }
        }
        $dreamTourRequest->delete();

        return redirect()->route('admin.dream-tour-requests.index')->with('message', __('Dream Tour request deleted successfully!'));
    }
}
