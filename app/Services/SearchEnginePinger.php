<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchEnginePinger
{
    /**
     * Base URL for the site
     */
    protected string $baseUrl;

    /**
     * Search engine ping endpoints
     */
    protected array $endpoints = [
        'google' => 'https://www.google.com/ping?sitemap=',
        'bing' => 'https://www.bing.com/indexnow?url=',
        'yandex' => 'https://webmaster.yandex.com/ping?sitemap=',
    ];

    /**
     * IndexNow endpoints (modern protocol)
     */
    protected array $indexNowEndpoints = [
        'google' => 'https://indexnow.google.com/api/v1/report/url',
        'bing' => 'https://www.bing.com/indexnow',
    ];

    public function __construct()
    {
        $this->baseUrl = config('app.url', 'http://127.0.0.1:8001');
    }

    /**
     * Ping sitemap to all search engines
     */
    public function pingSitemap(string $sitemapUrl = null): array
    {
        $sitemapUrl = $sitemapUrl ?? $this->baseUrl . '/sitemap.xml';
        $results = [];

        foreach ($this->endpoints as $engine => $endpoint) {
            try {
                $response = Http::timeout(10)->get($endpoint . urlencode($sitemapUrl));
                $results[$engine] = [
                    'success' => $response->successful(),
                    'status' => $response->status(),
                ];
            } catch (\Exception $e) {
                $results[$engine] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
                Log::error("Failed to ping {$engine}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Ping specific URL using IndexNow protocol
     */
    public function pingUrl(string $url): array
    {
        $results = [];

        foreach ($this->indexNowEndpoints as $engine => $endpoint) {
            try {
                $payload = [
                    'host' => parse_url($this->baseUrl, PHP_URL_HOST),
                    'key' => $this->getIndexNowKey(),
                    'url' => $url,
                    'keyLocation' => $this->baseUrl . '/indexnow-key.txt',
                ];

                $response = Http::timeout(10)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($endpoint, $payload);

                $results[$engine] = [
                    'success' => $response->successful(),
                    'status' => $response->status(),
                ];
            } catch (\Exception $e) {
                $results[$engine] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Ping all search engines with sitemap
     */
    public function pingAll(string $url = null): array
    {
        return [
            'sitemap' => $this->pingSitemap(),
            'indexnow' => $url ? $this->pingUrl($url) : [],
        ];
    }

    /**
     * Get IndexNow key
     */
    protected function getIndexNowKey(): string
    {
        return config('seo.indexnow_key', 'swiftkudi-' . substr(md5($this->baseUrl), 0, 8));
    }

    /**
     * Get the key location URL
     */
    public function getKeyLocation(): string
    {
        return $this->baseUrl . '/indexnow-key.txt';
    }
}
