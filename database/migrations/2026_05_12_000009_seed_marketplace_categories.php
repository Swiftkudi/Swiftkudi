<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('marketplace_categories')->where('type', 'marketplace')->exists()) {
            $now = now()->toDateTimeString();
            DB::table('marketplace_categories')->insert([
                // Top-level parent categories
                ['id' => 100, 'name' => 'Textbooks', 'slug' => 'textbooks', 'description' => 'Academic textbooks by course', 'type' => 'marketplace', 'icon' => 'fas fa-book', 'color' => '#6366f1', 'parent_id' => null, 'is_active' => true, 'order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 101, 'name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Devices and gadgets', 'type' => 'marketplace', 'icon' => 'fas fa-laptop', 'color' => '#ec4899', 'parent_id' => null, 'is_active' => true, 'order' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 102, 'name' => 'Furniture', 'slug' => 'furniture', 'description' => 'Home and dorm furniture', 'type' => 'marketplace', 'icon' => 'fas fa-couch', 'color' => '#f59e0b', 'parent_id' => null, 'is_active' => true, 'order' => 3, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 103, 'name' => 'Clothing & Fashion', 'slug' => 'clothing-fashion', 'description' => 'Apparel and accessories', 'type' => 'marketplace', 'icon' => 'fas fa-tshirt', 'color' => '#10b981', 'parent_id' => null, 'is_active' => true, 'order' => 4, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 104, 'name' => 'School Supplies', 'slug' => 'school-supplies', 'description' => 'Stationery and supplies', 'type' => 'marketplace', 'icon' => 'fas fa-pen', 'color' => '#3b82f6', 'parent_id' => null, 'is_active' => true, 'order' => 5, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 105, 'name' => 'Housing & Roommates', 'slug' => 'housing-roommates', 'description' => 'Accommodation and roommate posts', 'type' => 'marketplace', 'icon' => 'fas fa-home', 'color' => '#8b5cf6', 'parent_id' => null, 'is_active' => true, 'order' => 6, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 106, 'name' => 'Services', 'slug' => 'services', 'description' => 'Student services and tutoring', 'type' => 'marketplace', 'icon' => 'fas fa-concierge-bell', 'color' => '#f97316', 'parent_id' => null, 'is_active' => true, 'order' => 7, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 107, 'name' => 'Other', 'slug' => 'other', 'description' => 'Miscellaneous items', 'type' => 'marketplace', 'icon' => 'fas fa-ellipsis-h', 'color' => '#6b7280', 'parent_id' => null, 'is_active' => true, 'order' => 8, 'created_at' => $now, 'updated_at' => $now],

                // Subcategories under Textbooks
                ['id' => 200, 'name' => 'Science & Tech', 'slug' => 'textbooks-science-tech', 'description' => null, 'type' => 'marketplace', 'icon' => null, 'color' => '#6366f1', 'parent_id' => 100, 'is_active' => true, 'order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 201, 'name' => 'Arts & Humanities', 'slug' => 'textbooks-arts-humanities', 'description' => null, 'type' => 'marketplace', 'icon' => null, 'color' => '#6366f1', 'parent_id' => 100, 'is_active' => true, 'order' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 202, 'name' => 'Business & Law', 'slug' => 'textbooks-business-law', 'description' => null, 'type' => 'marketplace', 'icon' => null, 'color' => '#6366f1', 'parent_id' => 100, 'is_active' => true, 'order' => 3, 'created_at' => $now, 'updated_at' => $now],

                // Subcategories under Electronics
                ['id' => 300, 'name' => 'Laptops & PCs', 'slug' => 'electronics-laptops', 'description' => null, 'type' => 'marketplace', 'icon' => null, 'color' => '#ec4899', 'parent_id' => 101, 'is_active' => true, 'order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 301, 'name' => 'Phones & Tablets', 'slug' => 'electronics-phones', 'description' => null, 'type' => 'marketplace', 'icon' => null, 'color' => '#ec4899', 'parent_id' => 101, 'is_active' => true, 'order' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 302, 'name' => 'Accessories', 'slug' => 'electronics-accessories', 'description' => null, 'type' => 'marketplace', 'icon' => null, 'color' => '#ec4899', 'parent_id' => 101, 'is_active' => true, 'order' => 3, 'created_at' => $now, 'updated_at' => $now],

                // Subcategories under Furniture
                ['id' => 400, 'name' => 'Desks & Tables', 'slug' => 'furniture-desks', 'description' => null, 'type' => 'marketplace', 'icon' => null, 'color' => '#f59e0b', 'parent_id' => 102, 'is_active' => true, 'order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 401, 'name' => 'Chairs & Seating', 'slug' => 'furniture-chairs', 'description' => null, 'type' => 'marketplace', 'icon' => null, 'color' => '#f59e0b', 'parent_id' => 102, 'is_active' => true, 'order' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 402, 'name' => 'Beds & Bedding', 'slug' => 'furniture-beds', 'description' => null, 'type' => 'marketplace', 'icon' => null, 'color' => '#f59e0b', 'parent_id' => 102, 'is_active' => true, 'order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        DB::table('marketplace_categories')->where('type', 'marketplace')->delete();
    }
};