<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class SitemapController extends Controller
{
    /**
     * Generate sitemap index for large sites
     */
    public function index()
    {
        $baseUrl = config('app.url', 'http://127.0.0.1:8001');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Main sitemap
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>' . $baseUrl . '/sitemap.xml</loc>' . "\n";
        $xml .= '    <lastmod>' . Carbon::now()->toIso8601String() . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";
        
        // Tasks sitemap
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>' . $baseUrl . '/sitemap-tasks.xml</loc>' . "\n";
        $xml .= '    <lastmod>' . Carbon::now()->toIso8601String() . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";
        
        // Services sitemap
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>' . $baseUrl . '/sitemap-services.xml</loc>' . "\n";
        $xml .= '    <lastmod>' . Carbon::now()->toIso8601String() . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";
        
        $xml .= '</sitemapindex>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Generate main sitemap XML
     */
    public function main()
    {
        $sitemap = $this->buildSitemap();
        
        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Generate tasks sitemap
     */
    public function tasks()
    {
        $baseUrl = config('app.url', 'http://127.0.0.1:8001');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '    xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        
        // Task categories
        $categories = ['social-media', 'content', 'data-entry', 'app-testing', 'research', 'ugc'];
        
        foreach ($categories as $category) {
            $xml .= $this->generateUrlEntry(
                $baseUrl . '/tasks/' . $category,
                '0.8',
                'daily',
                ['en' => $baseUrl . '/tasks/' . $category, 'en-NG' => $baseUrl . '/tasks/' . $category]
            );
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Generate services sitemap
     */
    public function services()
    {
        $baseUrl = config('app.url', 'http://127.0.0.1:8001');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '    xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        
        // Service categories
        $categories = ['development', 'design', 'writing', 'video', 'marketing', 'virtual-assistant'];
        
        foreach ($categories as $category) {
            $xml .= $this->generateUrlEntry(
                $baseUrl . '/services/' . $category,
                '0.8',
                'weekly',
                ['en' => $baseUrl . '/services/' . $category, 'en-NG' => $baseUrl . '/services/' . $category]
            );
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Build the main sitemap XML
     */
    private function buildSitemap()
    {
        $baseUrl = config('app.url', 'http://127.0.0.1:8001');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '    xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        // Static pages with hreflang
        $staticPages = [
            // Core pages
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily', 'hreflang' => ['en' => '/', 'en-NG' => '/', 'en-US' => '/']],
            
            // About & Authority pages
            ['loc' => '/about', 'priority' => '0.9', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/about', 'en-NG' => '/about']],
            ['loc' => '/about-author', 'priority' => '0.8', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/about-author', 'en-NG' => '/about-author']],
            ['loc' => '/editorial-policy', 'priority' => '0.6', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/editorial-policy', 'en-NG' => '/editorial-policy']],
            
            // Main service pages
            ['loc' => '/services', 'priority' => '0.9', 'changefreq' => 'weekly', 'hreflang' => ['en' => '/services', 'en-NG' => '/services']],
            ['loc' => '/marketplace', 'priority' => '0.9', 'changefreq' => 'daily', 'hreflang' => ['en' => '/marketplace', 'en-NG' => '/marketplace']],
            ['loc' => '/tasks', 'priority' => '0.9', 'changefreq' => 'daily', 'hreflang' => ['en' => '/tasks', 'en-NG' => '/tasks']],
            ['loc' => '/products', 'priority' => '0.8', 'changefreq' => 'daily', 'hreflang' => ['en' => '/products', 'en-NG' => '/products']],
            ['loc' => '/growth', 'priority' => '0.8', 'changefreq' => 'weekly', 'hreflang' => ['en' => '/growth', 'en-NG' => '/growth']],
            
            // Learning Hub
            ['loc' => '/learn', 'priority' => '0.8', 'changefreq' => 'weekly', 'hreflang' => ['en' => '/learn', 'en-NG' => '/learn']],
            
            // Auth pages
            ['loc' => '/register', 'priority' => '0.7', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/register', 'en-NG' => '/register']],
            ['loc' => '/login', 'priority' => '0.7', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/login', 'en-NG' => '/login']],
            
            // Support & Information
            ['loc' => '/contact', 'priority' => '0.7', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/contact', 'en-NG' => '/contact']],
            ['loc' => '/faq', 'priority' => '0.8', 'changefreq' => 'weekly', 'hreflang' => ['en' => '/faq', 'en-NG' => '/faq']],
            ['loc' => '/how-it-works', 'priority' => '0.8', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/how-it-works', 'en-NG' => '/how-it-works']],
            ['loc' => '/pricing', 'priority' => '0.7', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/pricing', 'en-NG' => '/pricing']],
            
            // Trust Pages
            ['loc' => '/how-payments-work', 'priority' => '0.7', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/how-payments-work', 'en-NG' => '/how-payments-work']],
            ['loc' => '/platform-safety', 'priority' => '0.7', 'changefreq' => 'monthly', 'hreflang' => ['en' => '/platform-safety', 'en-NG' => '/platform-safety']],
            
            // Legal
            ['loc' => '/terms', 'priority' => '0.5', 'changefreq' => 'yearly', 'hreflang' => ['en' => '/terms', 'en-NG' => '/terms']],
            ['loc' => '/privacy', 'priority' => '0.5', 'changefreq' => 'yearly', 'hreflang' => ['en' => '/privacy', 'en-NG' => '/privacy']],
        ];

        foreach ($staticPages as $page) {
            $hreflang = $page['hreflang'] ?? ['en' => $page['loc'], 'en-NG' => $page['loc']];
            $xml .= $this->generateUrlEntry($baseUrl . $page['loc'], $page['priority'], $page['changefreq'], $hreflang);
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate a single URL entry with hreflang
     */
    private function generateUrlEntry($url, $priority, $changefreq, $hreflang = null)
    {
        $entry = '  <url>' . "\n";
        $entry .= '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
        $entry .= '    <lastmod>' . Carbon::now()->toIso8601String() . '</lastmod>' . "\n";
        $entry .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
        $entry .= '    <priority>' . $priority . '</priority>' . "\n";
        
        // Add hreflang alternatives
        if ($hreflang && is_array($hreflang)) {
            foreach ($hreflang as $lang => $href) {
                $entry .= '    <xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . htmlspecialchars($href) . '" />' . "\n";
            }
        }
        
        $entry .= '  </url>' . "\n";

        return $entry;
    }
}
