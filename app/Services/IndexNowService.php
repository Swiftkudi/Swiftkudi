<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * IndexNow Protocol Implementation
 * https://www.indexnow.org/
 */
class IndexNowService
{
    /**
     * IndexNow API endpoint
     */
    protected string $apiEndpoint = 'https://api.indexnow.org/indexnow';

    /**
     * Supported search engines
     */
    protected array $endpoints = [
        'bing' => 'https://www.bing.com/indexnow',
        'google' => 'https://indexnow.google.com/api/v1/report/url',
    ];

    /**
     * Generate IndexNow API key
     */
    public function generateKey(): string
    {
        return config('app.name', 'swiftkudi') . '-' . Str::random(32);
    }

    /**
     * Get or create the API key
     */
    public function getKey(): string
    {
        $key = config('seo.indexnow_key');
        
        if (!$key) {
            $key = $this->generateKey();
            // In production, store this in config/database
            config(['seo.indexnow_key' => $key]);
        }
        
        return $key;
    }

    /**
     * Get the key location URL
     */
    public function getKeyLocation(): string
    {
        return config('app.url') . '/indexnow-key.txt';
    }

    /**
     * Submit URL to IndexNow
     */
    public function submitUrl(string $url): array
    {
        $results = [];

        foreach ($this->endpoints as $engine => $endpoint) {
            try {
                $payload = [
                    'host' => parse_url(config('app.url'), PHP_URL_HOST),
                    'key' => $this->getKey(),
                    'keyLocation' => $this->getKeyLocation(),
                    'url' => $url,
                ];

                $response = Http::timeout(30)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                    ->post($endpoint, $payload);

                $results[$engine] = [
                    'success' => in_array($response->status(), [200, 202, 204]),
                    'status' => $response->status(),
                    'message' => $response->body(),
                ];

                Log::info("IndexNow submission to {$engine}: ", $results[$engine]);

            } catch (\Exception $e) {
                $results[$engine] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
                Log::error("IndexNow submission failed to {$engine}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Submit multiple URLs
     */
    public function submitUrls(array $urls): array
    {
        $results = [];

        foreach ($urls as $url) {
            $results[$url] = $this->submitUrl($url);
        }

        return $results;
    }

    /**
     * Submit when new content is published
     */
    public function onContentPublished(string $url, string $contentType = 'page'): array
    {
        Log::info("IndexNow: Content published - {$contentType}: {$url}");
        return $this->submitUrl($url);
    }

    /**
     * Submit when content is updated
     */
    public function onContentUpdated(string $url): array
    {
        Log::info("IndexNow: Content updated: {$url}");
        return $this->submitUrl($url);
    }

    /**
     * Submit new marketplace listing
     */
    public function onMarketplaceListing(string $listingUrl, string $type): array
    {
        Log::info("IndexNow: New {$type} listing: {$listingUrl}");
        return $this->submitUrl($listingUrl);
    }

    /**
     * Verify the key is correctly set up
     */
    public function verifyKey(): bool
    {
        $key = $this->getKey();
        $keyLocation = public_path('indexnow-key.txt');
        
        if (!file_exists($keyLocation)) {
            return false;
        }

        $storedKey = file_get_contents($keyLocation);
        return trim($storedKey) === $key;
    }
}
