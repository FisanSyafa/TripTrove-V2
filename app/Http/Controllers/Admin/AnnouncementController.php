<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(10);
        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Announcements/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($request->boolean('is_active')) {
            Announcement::where('is_active', true)->update(['is_active' => false]);
        }

        Announcement::create([
            'message' => $request->message,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('message', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        return Inertia::render('Admin/Announcements/Edit', [
            'announcement' => $announcement
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'message' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($request->boolean('is_active')) {
            Announcement::where('id', '!=', $announcement->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $announcement->update([
            'message' => $request->message,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('message', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->back()->with('message', 'Announcement deleted successfully.');
    }
}