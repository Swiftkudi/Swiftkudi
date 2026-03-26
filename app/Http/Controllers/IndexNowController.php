<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SearchEnginePinger;

class IndexNowController extends Controller
{
    /**
     * Handle IndexNow protocol requests
     * https://www.indexnow.org/
     */
    public function handle(Request $request, SearchEnginePinger $pinger)
    {
        // Validate the request
        $validated = $request->validate([
            'url' => 'required|url',
            'key' => 'nullable|string',
        ]);

        // Verify key if provided (for IndexNow verification)
        $key = $validated['key'] ?? null;
        $keyLocation = config('app.url') . '/indexnow-key.txt';
        
        // Ping search engines
        $results = $pinger->pingAll($validated['url']);

        return response()->json([
            'success' => true,
            'message' => 'URL submitted to search engines',
            'url' => $validated['url'],
            'results' => $results,
        ]);
    }

    /**
     * Get IndexNow key for verification
     */
    public function getKey()
    {
        $key = config('seo.indexnow_key', 'swiftkudi-' . substr(md5(config('app.url')), 0, 8));
        
        return response($key, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
