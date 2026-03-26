<?php

namespace App\Helpers;

use App\Services\KnowledgeGraphService;

/**
 * SEO Helper for Blade Templates
 */
class SeoHelper
{
    /**
     * Get Organization Schema JSON-LD
     */
    public static function organizationSchema(): string
    {
        $schema = KnowledgeGraphService::organizationSchema();
        return '<script type="application/ld+json">' . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Get WebSite Schema JSON-LD
     */
    public static function websiteSchema(): string
    {
        $schema = KnowledgeGraphService::websiteSchema();
        return '<script type="application/ld+json">' . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Get Person (Founder) Schema JSON-LD
     */
    public static function founderSchema(): string
    {
        $schema = KnowledgeGraphService::founderSchema();
        return '<script type="application/ld+json">' . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Get all Knowledge Graph schemas combined
     */
    public static function knowledgeGraph(): string
    {
        return KnowledgeGraphService::completeKnowledgeGraph();
    }

    /**
     * Get social profile links
     */
    public static function socialProfiles(): array
    {
        return KnowledgeGraphService::socialProfiles();
    }

    /**
     * Get canonical entity name
     */
    public static function getSiteName(): string
    {
        return KnowledgeGraphService::getEntityName();
    }

    /**
     * Get custom meta tags
     */
    public static function getCustomMeta(): string
    {
        $name = self::getSiteName();
        
        return "\n" . 
            '<!-- Knowledge Graph -->' . "\n" .
            self::organizationSchema() . "\n" .
            self::websiteSchema() . "\n" .
            '<!-- Entity Consistency -->' . "\n" .
            '<meta property="og:site_name" content="' . $name . '">' . "\n" .
            '<meta name="author" content="' . $name . '">' . "\n" .
            '<meta name="publisher" content="' . $name . '">' . "\n";
    }

    /**
     * Verify entity consistency
     */
    public static function verify(): array
    {
        return KnowledgeGraphService::verifyConsistency();
    }
}
