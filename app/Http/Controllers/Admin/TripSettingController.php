<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TripSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'small_car_price' => TripSetting::getByKey('small_car_price', 0),
            'large_car_price' => TripSetting::getByKey('large_car_price', 0),
        ];

        return Inertia::render('Admin/Settings/TripSettings', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'small_car_price' => 'required|numeric|min:0',
            'large_car_price' => 'required|numeric|min:0',
        ]);

        foreach ($validated as $key => $value) {
            TripSetting::setByKey($key, $value);
        }

        return back()->with('message', 'Settings updated successfully!');
    }
}
