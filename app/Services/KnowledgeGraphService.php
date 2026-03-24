<?php

namespace App\Services;

/**
 * Knowledge Graph Optimization Service
 * Ensures consistent entity representation across all pages
 */
class KnowledgeGraphService
{
    /**
     * Get the canonical entity name
     */
    public static function getEntityName(): string
    {
        return 'SwiftKudi';
    }

    /**
     * Generate Organization Schema
     */
    public static function organizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => 'https://swiftkudi.com/#organization',
            'name' => self::getEntityName(),
            'url' => 'https://swiftkudi.com',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://swiftkudi.com/logo.png',
                'width' => 512,
                'height' => 512
            ],
            'description' => 'Nigerian multi-sided earning and freelance marketplace. Users earn money by completing digital tasks, offering professional services, selling digital products, and participating in growth marketing campaigns.',
            'foundingDate' => '2023',
            'numberOfEmployees' => [
                '@type' => 'QuantitativeValue',
                'minValue' => 10,
                'maxValue' => 50
            ],
            'areaServed' => [
                [
                    '@type' => 'Country',
                    'name' => 'Nigeria',
                    '@id' => 'https://en.wikipedia.org/wiki/Nigeria'
                ],
                [
                    '@type' => 'Country',
                    'name' => 'Africa'
                ]
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'NG',
                'addressRegion' => 'Lagos',
                'addressLocality' => 'Lagos'
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Customer Support',
                'email' => 'support@swiftkudi.com',
                'availableLanguage' => ['English'],
                'areaServed' => ['NG', 'ZA', 'GH', 'KE'],
                'availableHours' => 'Mo-Su 00:00-24:00'
            ],
            'serviceType' => [
                'Micro-task Marketplace',
                'Freelance Services',
                'Digital Products Marketplace',
                'Growth Marketing Platform',
                'Escrow Payment Services'
            ],
            'sameAs' => self::socialProfiles(),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => 'https://swiftkudi.com/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }

    /**
     * Generate WebSite Schema
     */
    public static function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => 'https://swiftkudi.com/#website',
            'url' => 'https://swiftkudi.com',
            'name' => self::getEntityName(),
            'description' => 'Nigerian multi-sided earning marketplace. Earn money through tasks, services, digital products, and growth campaigns.',
            'publisher' => [
                '@type' => 'Organization',
                '@id' => 'https://swiftkudi.com/#organization'
            ],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => 'https://swiftkudi.com/search?q={search_term_string}'
                ],
                'query-input' => 'required name=search_term_string'
            ],
            'inLanguage' => 'en-NG',
            'supportedLanguage' => ['en', 'en-NG', 'en-US'],
            'copyrightYear' => 2026,
            'dateModified' => date('Y-m-d')
        ];
    }

    /**
     * Generate Person Schema (Founder)
     */
    public static function founderSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            '@id' => 'https://swiftkudi.com/#founder',
            'name' => 'SwiftKudi Founder',
            'jobTitle' => 'Founder & CEO',
            'worksFor' => [
                '@type' => 'Organization',
                '@id' => 'https://swiftkudi.com/#organization',
                'name' => self::getEntityName()
            ],
            'sameAs' => self::socialProfiles()
        ];
    }

    /**
     * Generate Social Profile Links
     */
    public static function socialProfiles(): array
    {
        return [
            'https://twitter.com/swiftkudi',
            'https://facebook.com/swiftkudi',
            'https://instagram.com/swiftkudi',
            'https://linkedin.com/company/swiftkudi',
            'https://youtube.com/@swiftkudi',
            'https://tiktok.com/@swiftkudi'
        ];
    }

    /**
     * Generate complete Knowledge Graph JSON-LD
     */
    public static function completeKnowledgeGraph(): string
    {
        $schemas = [
            self::organizationSchema(),
            self::websiteSchema()
        ];

        $output = '';
        foreach ($schemas as $schema) {
            $output .= '<script type="application/ld+json">' . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
        }

        return $output;
    }

    /**
     * Get entity name for display
     */
    public static function getDisplayName(): string
    {
        return self::getEntityName();
    }

    /**
     * Verify entity consistency
     */
    public static function verifyConsistency(): array
    {
        $entityName = self::getEntityName();
        
        return [
            'entity_name' => $entityName,
            'verified' => $entityName === 'SwiftKudi',
            'social_profiles' => count(self::socialProfiles()),
            'schemas_available' => ['Organization', 'WebSite', 'Person']
        ];
    }
}
