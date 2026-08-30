<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->addSlug('jobs');
        $this->addSlug('professional_services');
    }

    private function addSlug(string $table): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!Schema::hasColumn($table, 'slug')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('slug', 220)->nullable()->unique()->after('id');
            });
        }

        DB::table($table)
            ->whereNull('slug')
            ->orderBy('id')
            ->select(['id', 'title'])
            ->chunkById(200, function ($rows) use ($table) {
                foreach ($rows as $row) {
                    $base = Str::slug((string) $row->title) ?: 'listing';
                    DB::table($table)->where('id', $row->id)->update([
                        'slug' => Str::limit($base, 190, '') . '-' . $row->id,
                    ]);
                }
            });
    }

    public function down(): void
    {
        foreach (['jobs', 'professional_services'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'slug')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropUnique(['slug']);
                    $blueprint->dropColumn('slug');
                });
            }
        }
    }
};
