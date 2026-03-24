<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert module control settings
        $moduleSettings = [
            // Module Control - Tasks
            ['key' => 'module_tasks_enabled', 'value' => 'true', 'group' => 'modules', 'type' => 'boolean', 'description' => 'Enable Tasks module'],
            ['key' => 'module_tasks_commission', 'value' => '25', 'group' => 'modules', 'type' => 'number', 'description' => 'Tasks module commission percentage'],
            
            // Module Control - Professional Services
            ['key' => 'module_services_enabled', 'value' => 'true', 'group' => 'modules', 'type' => 'boolean', 'description' => 'Enable Professional Services module'],
            ['key' => 'module_services_commission', 'value' => '15', 'group' => 'modules', 'type' => 'number', 'description' => 'Services module commission percentage'],
            ['key' => 'module_services_approval_required', 'value' => 'true', 'group' => 'modules', 'type' => 'boolean', 'description' => 'Require admin approval for new services'],
            
            // Module Control - Growth/Marketplace
            ['key' => 'module_growth_enabled', 'value' => 'true', 'group' => 'modules', 'type' => 'boolean', 'description' => 'Enable Growth/Marketplace module'],
            ['key' => 'module_growth_commission', 'value' => '10', 'group' => 'modules', 'type' => 'number', 'description' => 'Growth module commission percentage'],
            
            // Module Control - Digital Products
            ['key' => 'module_digital_enabled', 'value' => 'true', 'group' => 'modules', 'type' => 'boolean', 'description' => 'Enable Digital Products module'],
            ['key' => 'module_digital_commission', 'value' => '20', 'group' => 'modules', 'type' => 'number', 'description' => 'Digital Products commission percentage'],
            
            // Module Control - Jobs
            ['key' => 'module_jobs_enabled', 'value' => 'true', 'group' => 'modules', 'type' => 'boolean', 'description' => 'Enable Jobs module'],
            ['key' => 'module_jobs_listing_fee', 'value' => '0', 'group' => 'modules', 'type' => 'number', 'description' => 'Job listing fee'],
            ['key' => 'module_jobs_featured_fee', 'value' => '500', 'group' => 'modules', 'type' => 'number', 'description' => 'Featured job listing fee'],
            
            // Module Control - Escrow
            ['key' => 'module_escrow_enabled', 'value' => 'true', 'group' => 'modules', 'type' => 'boolean', 'description' => 'Enable Escrow system'],
            
            // Module Control - Boost
            ['key' => 'module_boost_enabled', 'value' => 'true', 'group' => 'modules', 'type' => 'boolean', 'description' => 'Enable Boost system'],
            
            // Module Control - Referral
            ['key' => 'module_referral_enabled', 'value' => 'true', 'group' => 'modules', 'type' => 'boolean', 'description' => 'Enable Referral system'],
        ];

        foreach ($moduleSettings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Insert escrow settings
        $escrowSettings = [
            ['key' => 'escrow_auto_release_days', 'value' => '7', 'group' => 'escrow', 'type' => 'number', 'description' => 'Days before auto-release of escrow funds'],
            ['key' => 'escrow_max_revision_cycles', 'value' => '3', 'group' => 'escrow', 'type' => 'number', 'description' => 'Maximum revision cycles allowed'],
            ['key' => 'escrow_dispute_window_days', 'value' => '14', 'group' => 'escrow', 'type' => 'number', 'description' => 'Days after completion to raise dispute'],
            ['key' => 'escrow_partial_refund_allowed', 'value' => 'true', 'group' => 'escrow', 'type' => 'boolean', 'description' => 'Allow partial refunds in disputes'],
            ['key' => 'escrow_auto_accept_days', 'value' => '3', 'group' => 'escrow', 'type' => 'number', 'description' => 'Days to auto-accept delivered work'],
        ];

        foreach ($escrowSettings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Insert verification settings
        $verificationSettings = [
            ['key' => 'verification_enabled', 'value' => 'true', 'group' => 'verification', 'type' => 'boolean', 'description' => 'Enable user verification system'],
            ['key' => 'verification_fee', 'value' => '500', 'group' => 'verification', 'type' => 'number', 'description' => 'Verification processing fee'],
            ['key' => 'verification_required_documents', 'value' => '["id_card","proof_of_address","selfie"]', 'group' => 'verification', 'type' => 'json', 'description' => 'Required document types for verification'],
            ['key' => 'verification_tier1_threshold', 'value' => '50000', 'group' => 'verification', 'type' => 'number', 'description' => 'Tier 1 withdrawal threshold (unverified)'],
            ['key' => 'verification_tier2_threshold', 'value' => '200000', 'group' => 'verification', 'type' => 'number', 'description' => 'Tier 2 withdrawal threshold (ID verified)'],
            ['key' => 'verification_tier3_threshold', 'value' => '1000000', 'group' => 'verification', 'type' => 'number', 'description' => 'Tier 3 withdrawal threshold (fully verified)'],
            ['key' => 'verification_expiry_days', 'value' => '365', 'group' => 'verification', 'type' => 'number', 'description' => 'Verification validity period in days'],
        ];

        foreach ($verificationSettings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Insert boost settings
        $boostSettings = [
            ['key' => 'boost_enabled_tasks', 'value' => 'true', 'group' => 'boost', 'type' => 'boolean', 'description' => 'Enable boost for tasks'],
            ['key' => 'boost_enabled_services', 'value' => 'true', 'group' => 'boost', 'type' => 'boolean', 'description' => 'Enable boost for services'],
            ['key' => 'boost_enabled_growth', 'value' => 'true', 'group' => 'boost', 'type' => 'boolean', 'description' => 'Enable boost for growth listings'],
            ['key' => 'boost_enabled_digital', 'value' => 'true', 'group' => 'boost', 'type' => 'boolean', 'description' => 'Enable boost for digital products'],
            ['key' => 'boost_max_active_per_user', 'value' => '5', 'group' => 'boost', 'type' => 'number', 'description' => 'Maximum active boosts per user'],
            ['key' => 'boost_default_multiplier', 'value' => '2', 'group' => 'boost', 'type' => 'number', 'description' => 'Default visibility multiplier for boosted items'],
        ];

        foreach ($boostSettings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Insert additional commission settings
        $commissionSettings = [
            ['key' => 'commission_services_enabled', 'value' => 'true', 'group' => 'commission', 'type' => 'boolean', 'description' => 'Enable commission for services'],
            ['key' => 'commission_growth_enabled', 'value' => 'true', 'group' => 'commission', 'type' => 'boolean', 'description' => 'Enable commission for growth listings'],
            ['key' => 'commission_digital_enabled', 'value' => 'true', 'group' => 'commission', 'type' => 'boolean', 'description' => 'Enable commission for digital products'],
            ['key' => 'commission_jobs_enabled', 'value' => 'false', 'group' => 'commission', 'type' => 'boolean', 'description' => 'Enable commission for job listings'],
            ['key' => 'dispute_penalty_fee', 'value' => '5', 'group' => 'commission', 'type' => 'number', 'description' => 'Fee percentage for disputed transactions'],
            ['key' => 'dispute_penalty_max', 'value' => '5000', 'group' => 'commission', 'type' => 'number', 'description' => 'Maximum dispute penalty fee'],
        ];

        foreach ($commissionSettings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            // Modules
            'module_tasks_enabled', 'module_tasks_commission',
            'module_services_enabled', 'module_services_commission', 'module_services_approval_required',
            'module_growth_enabled', 'module_growth_commission',
            'module_digital_enabled', 'module_digital_commission',
            'module_jobs_enabled', 'module_jobs_listing_fee', 'module_jobs_featured_fee',
            'module_escrow_enabled', 'module_boost_enabled', 'module_referral_enabled',
            // Escrow
            'escrow_auto_release_days', 'escrow_max_revision_cycles', 'escrow_dispute_window_days',
            'escrow_partial_refund_allowed', 'escrow_auto_accept_days',
            // Verification
            'verification_enabled', 'verification_fee', 'verification_required_documents',
            'verification_tier1_threshold', 'verification_tier2_threshold', 'verification_tier3_threshold',
            'verification_expiry_days',
            // Boost
            'boost_enabled_tasks', 'boost_enabled_services', 'boost_enabled_growth', 'boost_enabled_digital',
            'boost_max_active_per_user', 'boost_default_multiplier',
            // Commission
            'commission_services_enabled', 'commission_growth_enabled', 'commission_digital_enabled',
            'commission_jobs_enabled', 'dispute_penalty_fee', 'dispute_penalty_max',
        ];

        DB::table('system_settings')->whereIn('key', $keys)->delete();
    }
};
