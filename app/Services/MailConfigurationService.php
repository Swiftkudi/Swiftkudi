<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;

/**
 * Single source of truth for runtime mail configuration.
 *
 * Web requests and queue workers both call this service so SMTP behaviour cannot
 * drift between notification paths.
 */
class MailConfigurationService
{
    public function apply(): bool
    {
        if (!SystemSetting::getBool('smtp_enabled', false)) {
            return false;
        }

        $selectedDriver = (string) SystemSetting::get('smtp_driver', config('mail.default', 'smtp'));
        $isTurbo = $selectedDriver === 'turbosmtp';
        $driver = $isTurbo ? 'smtp' : $selectedDriver;

        $host = SystemSetting::get('smtp_host', config('mail.mailers.smtp.host'));
        if (empty($host) && $isTurbo) {
            $host = config('services.turbosmtp.server', $host);
        }

        $port = (int) SystemSetting::getNumber('smtp_port', config('mail.mailers.smtp.port', 587));
        $username = SystemSetting::get('smtp_username', config('mail.mailers.smtp.username'));
        $password = SystemSetting::getDecrypted('smtp_password', config('mail.mailers.smtp.password'));

        if ($isTurbo) {
            $port = $port > 0 ? $port : (int) config('services.turbosmtp.port', 587);
            $username = $username ?: config('services.turbosmtp.username');
            $password = $password ?: config('services.turbosmtp.password');
        }

        $encryption = strtolower((string) SystemSetting::get('smtp_encryption', config('mail.mailers.smtp.encryption', 'tls')));
        if (in_array($encryption, ['', 'none', 'null'], true)) {
            $encryption = null;
        }

        if ($port <= 0) {
            $port = $encryption === 'ssl' ? 465 : 587;
        }
        if ($encryption === 'ssl' && $port === 587) {
            $port = 465;
        }
        if (($encryption === 'tls' || $encryption === null) && $port === 465) {
            $port = 587;
        }

        $fromAddress = (string) SystemSetting::get('smtp_from_email', config('mail.from.address'));
        $fromName = (string) SystemSetting::get('smtp_from_name', config('mail.from.name', config('app.name', 'SwiftKudi')));

        if ($isTurbo) {
            $fromAddress = $fromAddress ?: (string) config('services.turbosmtp.from_address');
            $fromName = $fromName ?: (string) config('services.turbosmtp.from_name', config('app.name', 'SwiftKudi'));
        }

        Config::set('mail.default', $driver);
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port);
        Config::set('mail.mailers.smtp.username', $username);
        Config::set('mail.mailers.smtp.password', $password);
        Config::set('mail.mailers.smtp.encryption', $encryption);
        Config::set('mail.mailers.smtp.timeout', 30);
        Config::set('mail.mailers.smtp.auth_mode', null);
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);

        // MailManager may have been resolved before database settings were applied.
        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');

        return true;
    }

    public function diagnostics(): array
    {
        $from = (string) SystemSetting::get('smtp_from_email', config('mail.from.address'));
        $domain = str_contains($from, '@') ? substr(strrchr($from, '@'), 1) : null;

        return [
            'enabled' => SystemSetting::getBool('smtp_enabled', false),
            'driver' => (string) SystemSetting::get('smtp_driver', config('mail.default', 'smtp')),
            'host_configured' => (bool) SystemSetting::get('smtp_host', config('mail.mailers.smtp.host')),
            'from_address' => $from,
            'from_domain' => $domain,
            'alignment_hint' => $domain ? 'SPF, DKIM and DMARC should all be configured for ' . $domain . '.' : 'Configure a From address on your own domain.',
        ];
    }
}
