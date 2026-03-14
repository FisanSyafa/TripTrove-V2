<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            // Ubah ke nullable jika ingin opsional, atau required jika wajib
            'phone_number' => 'nullable|string|max:20', 
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        Message::create($validated);

        return redirect()->back()->with('message', 'Pesan Anda berhasil dikirim. Kami akan segera menghubungi Anda.');
    }
}