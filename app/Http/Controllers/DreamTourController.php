<?php

namespace App\Http\Controllers;

use App\Models\DreamTourRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DreamTourController extends Controller
{
    public function create()
    {
        return Inertia::render('DreamTour/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:10',
            'departure_date' => 'nullable|date|after_or_equal:today',
            'num_adults' => 'required|integer|min:1',
            'num_children' => 'nullable|integer|min:0',
            'destinations' => 'required|array|min:1',
            'destinations.*' => 'required|string|max:255',
            'additional_info' => 'nullable|string',
        ]);

        DreamTourRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'country_code' => $validated['country_code'] ?? null,
            'departure_date' => $validated['departure_date'] ?? null,
            'num_adults' => $validated['num_adults'],
            'num_children' => $validated['num_children'] ?? 0,
            'destinations' => $validated['destinations'],
            'additional_info' => $validated['additional_info'] ?? null,
        ]);

        // Build WhatsApp message
        $adminPhone = config('app.admin_whatsapp_number');

        $participantLabel = __('Adult');
        $childrenLabel = __('Children');

        $participantText = $validated['num_adults'] . ' ' . $participantLabel;
        if (($validated['num_children'] ?? 0) > 0) {
            $participantText .= ' & ' . $validated['num_children'] . ' ' . $childrenLabel;
        }

        $destList = implode(', ', $validated['destinations']);

        $message = "*" . __('Dream Tour Request') . "*\n\n";
        $message .= __('Halo TripTrove,') . "\n\n";
        $message .= __('I want to request a custom tour:') . "\n\n";
        $message .= "👤 *" . __('Name') . ":* {$validated['name']}\n";
        $message .= "📧 *" . __('Email Address') . ":* {$validated['email']}\n";
        if (!empty($validated['phone'])) {
            $message .= "📱 *" . __('Phone Number') . ":* " . ($validated['country_code'] ?? '') . " {$validated['phone']}\n";
        }
        $message .= "📍 *" . __('Destinations') . ":* {$destList}\n";
        if (!empty($validated['departure_date'])) {
            $message .= "📅 *" . __('Departure Date') . ":* {$validated['departure_date']}\n";
        }
        $message .= "👥 *" . __('Participants') . ":* {$participantText}\n";
        if (!empty($validated['additional_info'])) {
            $message .= "📝 *" . __('Additional Information') . ":* {$validated['additional_info']}\n";
        }
        $message .= "\n" . __('Please provide more information. Thank you!') . " 🙏";

        $waUrl = 'https://wa.me/' . $adminPhone . '?text=' . urlencode($message);

        return back()->with([
            'message' => 'Dream Tour request submitted!',
            'whatsapp_url' => $waUrl,
        ]);
    }
}
