<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ControllerRouteTargetsTest extends TestCase
{
    /**
     * Every controller-backed route must reference an existing public method.
     */
    public function test_controller_route_targets_exist(): void
    {
        $missing = [];

        foreach (Route::getRoutes() as $route) {
            $controller = $route->getAction('controller');

            if (!is_string($controller) || $controller === '') {
                continue;
            }

            if (strpos($controller, '@') === false) {
                $class = $controller;
                $method = '__invoke';
            } else {
                [$class, $method] = explode('@', $controller, 2);
            }

            if (!class_exists($class) || !method_exists($class, $method)) {
                $missing[] = sprintf(
                    '%s %s -> %s@%s',
                    implode('|', $route->methods()),
                    $route->uri(),
                    $class,
                    $method
                );
            }
        }

        $this->assertSame([], $missing, "Broken controller route targets:\n" . implode("\n", $missing));
    }
}
