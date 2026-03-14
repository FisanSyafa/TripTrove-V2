<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index()
    {
        // Mengambil pesan terbaru, dipaginasi 10 per halaman
        $messages = Message::latest()->paginate(10);

        return Inertia::render('Admin/Messages/Index', [
            'messages' => $messages
        ]);
    }

    public function destroy(Message $message)
    {
        $message->delete();
        return redirect()->back()->with('message', 'Pesan berhasil dihapus.');
    }
}