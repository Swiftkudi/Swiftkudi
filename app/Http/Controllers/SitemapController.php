<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\ProfessionalService;
use App\Models\ServiceProviderProfile;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $maps = [
            ['url' => route('sitemap.pages'), 'lastmod' => now()],
            ['url' => route('sitemap.jobs'), 'lastmod' => Job::where('status', 'active')->max('updated_at')],
            ['url' => route('sitemap.services'), 'lastmod' => ProfessionalService::active()->max('updated_at')],
            ['url' => route('sitemap.freelancers'), 'lastmod' => ServiceProviderProfile::whereNotNull('slug')->max('updated_at')],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($maps as $map) {
            $xml .= "  <sitemap>\n";
            $xml .= '    <loc>' . $this->xml($map['url']) . "</loc>\n";
            if (!empty($map['lastmod'])) {
                $xml .= '    <lastmod>' . $this->xml((string) \Illuminate\Support\Carbon::parse($map['lastmod'])->toAtomString()) . "</lastmod>\n";
            }
            $xml .= "  </sitemap>\n";
        }
        $xml .= '</sitemapindex>';

        return $this->xmlResponse($xml);
    }

    public function pages(): Response
    {
        $routeNames = [
            'home' => ['1.0', 'daily'],
            'jobs.index' => ['0.9', 'daily'],
            'freelancers.index' => ['0.9', 'daily'],
            'professional-services.index' => ['0.9', 'daily'],
            'digital-products.index' => ['0.7', 'daily'],
            'growth.index' => ['0.7', 'daily'],
            'support' => ['0.5', 'monthly'],
            'legal.privacy' => ['0.4', 'yearly'],
            'legal.terms' => ['0.4', 'yearly'],
        ];

        $entries = [];
        foreach ($routeNames as $name => [$priority, $frequency]) {
            if (!Route::has($name)) {
                continue;
            }
            $entries[] = [
                'loc' => route($name),
                'priority' => $priority,
                'changefreq' => $frequency,
            ];
        }

        return $this->urlSetResponse($entries);
    }

    public function jobs(): Response
    {
        $entries = Job::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (Job $job) => [
                'loc' => route('jobs.show', $job),
                'lastmod' => $job->updated_at,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ])
            ->all();

        return $this->urlSetResponse($entries);
    }

    public function services(): Response
    {
        $entries = ProfessionalService::query()
            ->active()
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (ProfessionalService $service) => [
                'loc' => route('professional-services.show', $service),
                'lastmod' => $service->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ])
            ->all();

        return $this->urlSetResponse($entries);
    }

    public function freelancers(): Response
    {
        $entries = ServiceProviderProfile::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('is_available', true)
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (ServiceProviderProfile $profile) => [
                'loc' => route('freelancers.show', $profile->slug),
                'lastmod' => $profile->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ])
            ->all();

        return $this->urlSetResponse($entries);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /notifications',
            'Disallow: /settings',
            'Disallow: /contracts',
            'Disallow: /chat',
            'Disallow: /wallet',
            'Disallow: /escrow',
            'Disallow: /disputes',
            'Disallow: /*?*search=',
            'Disallow: /*?*saved=',
            'Sitemap: ' . route('sitemap.index'),
        ];

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function urlSetResponse(array $entries): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . $this->xml($entry['loc']) . "</loc>\n";
            if (!empty($entry['lastmod'])) {
                $xml .= '    <lastmod>' . $this->xml((string) \Illuminate\Support\Carbon::parse($entry['lastmod'])->toAtomString()) . "</lastmod>\n";
            }
            if (!empty($entry['changefreq'])) {
                $xml .= '    <changefreq>' . $this->xml($entry['changefreq']) . "</changefreq>\n";
            }
            if (!empty($entry['priority'])) {
                $xml .= '    <priority>' . $this->xml($entry['priority']) . "</priority>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return $this->xmlResponse($xml);
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex, follow',
        ]);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
