<?php
// tests/TestCase.php
namespace Hickr\Accounting\Tests;

use Hickr\Accounting\Models\Tenant;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Hickr\Accounting\AccountingServiceProvider;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            AccountingServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Minimal config overrides
        $app['config']->set('accounting.tenant_model', Tenant::class);
        $app['config']->set('accounting.default_currency', 'MVR');

        // Optional: in-memory SQLite for fast testing
        $databasePath = __DIR__ . '/../../vendor/orchestra/testbench-core/laravel/database/database.sqlite';

        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $databasePath,
            'prefix' => '',
        ]);
    }
}