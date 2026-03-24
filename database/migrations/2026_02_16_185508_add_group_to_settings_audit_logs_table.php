<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings_audit_logs', function (Blueprint $table) {
            $table->string('group')->nullable()->after('new_value');
        });
    }

    public function down()
    {
        Schema::table('settings_audit_logs', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
