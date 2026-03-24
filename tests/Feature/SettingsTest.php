<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Hash;

class SettingsTest extends TestCase
{
    public function test_super_admin_can_view_settings()
    {
        $admin = User::factory()->create([ 'is_admin' => true ]);
        $this->actingAs($admin)->get(route('admin.settings'))->assertStatus(200);
    }

    public function test_set_and_get_encrypted_setting()
    {
        SystemSetting::set('test_encrypted_key', 'secret-value', SystemSetting::GROUP_GENERAL, 'encrypted');
        $decrypted = SystemSetting::getDecrypted('test_encrypted_key');
        $this->assertEquals('secret-value', $decrypted);
    }
}
