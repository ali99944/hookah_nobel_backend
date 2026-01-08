<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SettingsService; // Import your service
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // If needed for specific dropdowns

class SettingsController extends Controller
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Show the form for editing the application settings.
     */
    public function index()
    {
        $settings = $this->settingsService->getAllSettings();

        return response()->json($settings, 200);
    }

    /**
     * Update the application settings in storage.
     */
    public function update(Request $request)
    {
        // make sure to validate the incoming request data as needed
        $validator = Validator::make($request->all(), [
            'site_identity' => 'sometimes|array',
            'contact_info' => 'sometimes|array',
            'social_media' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $success = $this->settingsService->saveSettings(
            $request->only(['site_identity', 'contact_info', 'social_media'])
        );

        if ($success) {
            return response()->json(['success' => 'Settings updated successfully.'], 200);
        } else {
            Log::error("Failed to save one or more settings files.");
            return response()->json(['error' => 'Failed to save some settings. Please check logs.'], 500);
        }
    }
}
