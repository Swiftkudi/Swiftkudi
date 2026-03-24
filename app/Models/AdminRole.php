<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    // Role types
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_FINANCE_ADMIN = 'finance_admin';
    const ROLE_SUPPORT_ADMIN = 'support_admin';

    // Permission constants
    const PERMISSION_SETTINGS_VIEW = 'settings.view';
    const PERMISSION_SETTINGS_EDIT = 'settings.edit';
    const PERMISSION_USERS_VIEW = 'users.view';
    const PERMISSION_USERS_EDIT = 'users.edit';
    const PERMISSION_USERS_DELETE = 'users.delete';
    const PERMISSION_TASKS_VIEW = 'tasks.view';
    const PERMISSION_TASKS_EDIT = 'tasks.edit';
    const PERMISSION_TASKS_DELETE = 'tasks.delete';
    const PERMISSION_WITHDRAWALS_VIEW = 'withdrawals.view';
    const PERMISSION_WITHDRAWALS_PROCESS = 'withdrawals.process';
    const PERMISSION_ANALYTICS_VIEW = 'analytics.view';
    const PERMISSION_AUDIT_VIEW = 'audit.view';

    /**
     * Get all users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class, 'admin_role_id');
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }

    /**
     * Check if this is a super admin role
     */
    public function isSuperAdmin(): bool
    {
        return $this->name === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Get default role permissions
     */
    public static function getDefaultPermissions(string $role): array
    {
        switch ($role) {
            case self::ROLE_SUPER_ADMIN:
                return [
                    self::PERMISSION_SETTINGS_VIEW,
                    self::PERMISSION_SETTINGS_EDIT,
                    self::PERMISSION_USERS_VIEW,
                    self::PERMISSION_USERS_EDIT,
                    self::PERMISSION_USERS_DELETE,
                    self::PERMISSION_TASKS_VIEW,
                    self::PERMISSION_TASKS_EDIT,
                    self::PERMISSION_TASKS_DELETE,
                    self::PERMISSION_WITHDRAWALS_VIEW,
                    self::PERMISSION_WITHDRAWALS_PROCESS,
                    self::PERMISSION_ANALYTICS_VIEW,
                    self::PERMISSION_AUDIT_VIEW,
                ];

            case self::ROLE_FINANCE_ADMIN:
                return [
                    self::PERMISSION_SETTINGS_VIEW,
                    self::PERMISSION_WITHDRAWALS_VIEW,
                    self::PERMISSION_WITHDRAWALS_PROCESS,
                    self::PERMISSION_ANALYTICS_VIEW,
                ];

            case self::ROLE_SUPPORT_ADMIN:
                return [
                    self::PERMISSION_USERS_VIEW,
                    self::PERMISSION_USERS_EDIT,
                    self::PERMISSION_TASKS_VIEW,
                    self::PERMISSION_TASKS_EDIT,
                    self::PERMISSION_ANALYTICS_VIEW,
                ];

            default:
                return [];
        }
    }

    /**
     * Create default admin roles
     */
    public static function createDefaults(): void
    {
        // Super Admin
        if (!self::where('name', self::ROLE_SUPER_ADMIN)->exists()) {
            self::create([
                'name' => self::ROLE_SUPER_ADMIN,
                'display_name' => 'Super Admin',
                'permissions' => self::getDefaultPermissions(self::ROLE_SUPER_ADMIN),
            ]);
        }

        // Finance Admin
        if (!self::where('name', self::ROLE_FINANCE_ADMIN)->exists()) {
            self::create([
                'name' => self::ROLE_FINANCE_ADMIN,
                'display_name' => 'Finance Admin',
                'permissions' => self::getDefaultPermissions(self::ROLE_FINANCE_ADMIN),
            ]);
        }

        // Support Admin
        if (!self::where('name', self::ROLE_SUPPORT_ADMIN)->exists()) {
            self::create([
                'name' => self::ROLE_SUPPORT_ADMIN,
                'display_name' => 'Support Admin',
                'permissions' => self::getDefaultPermissions(self::ROLE_SUPPORT_ADMIN),
            ]);
        }
    }
}
