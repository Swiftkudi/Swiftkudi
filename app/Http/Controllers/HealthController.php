<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Health check endpoint for monitoring
     */
    public function check(): Response
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => [],
        ];

        // Check database connection
        try {
            DB::connection()->getPdo();
            $status['checks']['database'] = 'connected';
        } catch (\Exception $e) {
            $status['checks']['database'] = 'failed: ' . $e->getMessage();
            $status['status'] = 'unhealthy';
        }

        // Check storage
        try {
            $storagePath = storage_path('framework');
            if (is_writable($storagePath)) {
                $status['checks']['storage'] = 'writable';
            } else {
                $status['checks']['storage'] = 'not writable';
                $status['status'] = 'unhealthy';
            }
        } catch (\Exception $e) {
            $status['checks']['storage'] = 'failed: ' . $e->getMessage();
            $status['status'] = 'unhealthy';
        }

        // Check cache
        try {
            cache()->put('health_check', true, 1);
            if (cache()->get('health_check')) {
                $status['checks']['cache'] = 'working';
            } else {
                $status['checks']['cache'] = 'not working';
            }
        } catch (\Exception $e) {
            $status['checks']['cache'] = 'failed: ' . $e->getMessage();
        }

        $statusCode = $status['status'] === 'healthy' ? 200 : 503;

        return response($status, $statusCode)->header('Content-Type', 'application/json');
    }
}
