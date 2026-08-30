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
        Schema::table('service_provider_profiles', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('user_id');
            $table->string('professional_title')->nullable()->after('slug');
            $table->text('languages')->nullable()->after('skills');
            $table->text('education')->nullable()->after('languages');
            $table->text('work_experience')->nullable()->after('education');
            $table->string('availability_note', 255)->nullable()->after('is_available');
            $table->unsignedBigInteger('profile_views')->default(0)->after('total_reviews');
        });

        DB::table('service_provider_profiles')
            ->join('users', 'users.id', '=', 'service_provider_profiles.user_id')
            ->select('service_provider_profiles.id', 'service_provider_profiles.user_id', 'users.name')
            ->orderBy('service_provider_profiles.id')
            ->chunk(200, function ($profiles) {
                foreach ($profiles as $profile) {
                    $base = Str::slug($profile->name ?: 'freelancer') ?: 'freelancer';
                    $slug = $base . '-' . $profile->user_id;
                    DB::table('service_provider_profiles')->where('id', $profile->id)->update(['slug' => $slug]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('service_provider_profiles', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'professional_title', 'languages', 'education', 'work_experience', 'availability_note', 'profile_views']);
        });
    }
};
