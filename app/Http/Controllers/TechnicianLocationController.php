<?php

namespace App\Http\Controllers;

use App\Models\TechnicianLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechnicianLocationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ]);

        $location = TechnicianLocation::updateOrCreate(
            ['user_id' => $request->user('technician')->id],
            $data + ['recorded_at' => now()],
        );

        return response()->json(['message' => 'Location shared securely.', 'recorded_at' => $location->recorded_at->toIso8601String()]);
    }
}
