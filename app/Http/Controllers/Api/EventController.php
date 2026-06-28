<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\BlacklistDomain;
use App\Models\Setting;
use App\Jobs\TelegramNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Store a new event from the Android agent.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate token from Authorization header
        $token = $request->header('Authorization');
        $expectedToken = Setting::get('api_token');

        if (!$token || $token !== 'Bearer ' . $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API token',
            ], 401);
        }

        // Validate request
        $validated = $request->validate([
            'type' => 'required|in:app_open,browser_access',
            'value' => 'required|string|max:255',
            'device_id' => 'nullable|string|max:100',
            'occurred_at' => 'required|date',
        ]);

        // Check if value is a suspicious domain (only for browser_access)
        $isSuspicious = false;
        if ($validated['type'] === 'browser_access') {
            $domain = $this->extractDomain($validated['value']);
            if ($domain) {
                $blacklisted = BlacklistDomain::where('domain', 'like', '%' . $domain . '%')->exists();
                $isSuspicious = $blacklisted;
            }
        }

        // Save event to database
        $event = Event::create([
            'type' => $validated['type'],
            'value' => $validated['value'],
            'is_suspicious' => $isSuspicious,
            'device_id' => $validated['device_id'] ?? null,
            'occurred_at' => $validated['occurred_at'],
        ]);

        // Dispatch Telegram notification job
        TelegramNotification::dispatch($event);

        return response()->json([
            'success' => true,
            'message' => 'Event recorded successfully',
            'data' => [
                'id' => $event->id,
                'is_suspicious' => $event->is_suspicious,
            ],
        ], 201);
    }

    /**
     * Health check endpoint.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'PantauKu API is running',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Extract domain from a URL string.
     */
    private function extractDomain(string $url): ?string
    {
        // Remove protocol if present
        $url = preg_replace('#^https?://#', '', $url);
        // Remove path
        $url = explode('/', $url)[0];
        // Remove port
        $url = explode(':', $url)[0];
        // Remove www prefix
        $url = preg_replace('#^www\.#', '', $url);

        return $url ?: null;
    }
}
