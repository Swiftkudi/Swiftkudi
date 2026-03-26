<?php

namespace App\Models\Traits;

use App\Services\SeoGeneratorService;
use App\Services\IndexNowService;

/**
 * Trait for automatic SEO optimization on marketplace listings
 * 
 * Usage:
 * class Task extends Model
 * {
 *     use SeoOptimizable;
 * }
 */
trait SeoOptimizable
{
    /**
     * Boot the trait
     */
    public static function bootSeoOptimizable()
    {
        static::created(function ($model) {
            $model->generateSeoFields();
            $model->pingSearchEngines();
        });

        static::updated(function ($model) {
            if ($model->wasChanged(['title', 'description', 'budget', 'status'])) {
                $model->generateSeoFields();
                $model->pingSearchEngines();
            }
        });
    }

    /**
     * Generate SEO fields automatically
     */
    public function generateSeoFields(): void
    {
        $seoGenerator = new SeoGeneratorService();
        
        $type = $this->getSeoType();
        
        $seoPackage = $seoGenerator->generateSeoPackage([
            'type' => $type,
            'title' => $this->title,
            'description' => $this->description,
            'budget' => $this->budget ?? $this->price ?? null,
            'category' => $this->category?->name ?? null,
            'slug' => $this->id,
            'created_at' => $this->created_at,
        ]);

        // Update model SEO fields if they exist
        if (isset($this-> seo_title)) {
            $this->seo_title = $seoPackage['seo']['title'];
        }
        
        if (isset($this->seo_description)) {
            $this->seo_description = $seoPackage['seo']['meta_description'];
        }
        
        if (isset($this->seo_schema)) {
            $this->seo_schema = json_encode($seoPackage['schema']);
        }
        
        if (isset($this->seo_faq)) {
            $this->seo_faq = json_encode($seoPackage['faq']);
        }
        
        if (isset($this->seo_content)) {
            $this->seo_content = $seoPackage['content']['full_content'];
        }
        
        if (isset($this->canonical_url)) {
            $this->canonical_url = $seoPackage['seo']['canonical_url'];
        }
        
        // Save without triggering events
        $this->saveQuieted();
    }

    /**
     * Ping search engines when listing is created/updated
     */
    public function pingSearchEngines(): void
    {
        if (!config('app.production', false)) {
            return; // Skip on local
        }

        $url = $this->getSeoUrl();
        
        if ($url) {
            try {
                $indexNow = new IndexNowService();
                $indexNow->onContentPublished($url, $this->getSeoType());
            } catch (\Exception $e) {
                // Log but don't fail
                \Log::warning("Failed to ping IndexNow: " . $e->getMessage());
            }
        }
    }

    /**
     * Get the SEO type for this model
     */
    protected function getSeoType(): string
    {
        $class = get_class($this);
        
        return match($class) {
            'App\Models\Task', 'App\Models\TaskNew' => 'task',
            'App\Models\ProfessionalService' => 'service',
            'App\Models\DigitalProduct' => 'product',
            default => 'listing'
        };
    }

    /**
     * Get the SEO URL for this listing
     */
    public function getSeoUrl(): string
    {
        $baseUrl = config('app.url', 'https://swiftkudi.com');
        $type = $this->getSeoType();
        
        return "{$baseUrl}/{$type}s/{$this->id}";
    }

    /**
     * Get SEO meta title
     */
    public function getSeoTitleAttribute(): string
    {
        if (isset($this->attributes['seo_title'])) {
            return $this->attributes['seo_title'];
        }
        
        $seoGenerator = new SeoGeneratorService();
        $package = $seoGenerator->generateSeoPackage([
            'type' => $this->getSeoType(),
            'title' => $this->title,
            'description' => $this->description ?? '',
            'budget' => $this->budget ?? $this->price ?? null,
            'category' => $this->category?->name ?? null,
            'slug' => $this->id,
        ]);
        
        return $package['seo']['title'];
    }

    /**
     * Get SEO meta description
     */
    public function getSeoDescriptionAttribute(): string
    {
        if (isset($this->attributes['seo_description'])) {
            return $this->attributes['seo_description'];
        }
        
        $seoGenerator = new SeoGeneratorService();
        $package = $seoGenerator->generateSeoPackage([
            'type' => $this->getSeoType(),
            'title' => $this->title,
            'description' => $this->description ?? '',
            'budget' => $this->budget ?? $this->price ?? null,
            'category' => $this->category?->name ?? null,
            'slug' => $this->id,
        ]);
        
        return $package['seo']['meta_description'];
    }

    /**
     * Get canonical URL
     */
    public function getCanonicalUrlAttribute(): string
    {
        return $this->getSeoUrl();
    }

    /**
     * Get JSON-LD schema
     */
    public function getSchemaAttribute(): array
    {
        if (isset($this->attributes['seo_schema'])) {
            return json_decode($this->attributes['seo_schema'], true);
        }
        
        $seoGenerator = new SeoGeneratorService();
        $package = $seoGenerator->generateSeoPackage([
            'type' => $this->getSeoType(),
            'title' => $this->title,
            'description' => $this->description ?? '',
            'budget' => (float) ($this->budget ?? $this->price ?? 0),
            'category' => $this->category?->name ?? null,
            'slug' => $this->id,
            'created_at' => $this->created_at?->toDateString(),
        ]);
        
        return $package['schema'];
    }

    /**
     * Get FAQ array
     */
    public function getFaqAttribute(): array
    {
        if (isset($this->attributes['seo_faq'])) {
            return json_decode($this->attributes['seo_faq'], true);
        }
        
        $seoGenerator = new SeoGeneratorService();
        return $seoGenerator->generateFaq($this->title ?? '', $this->getSeoType());
    }
}
