<?php
// tests/TestCase.php
namespace Hickr\Accounting\Tests;

use Hickr\Accounting\Models\Tenant;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Hickr\Accounting\AccountingServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AccountingServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // 🛠 Set tenant model config here:
        $app['config']->set('accounting.tenant_model', \Hickr\Accounting\Models\Tenant::class);
        $app['config']->set('accounting.tenant_table', 'tenants');
        $app['config']->set('accounting.default_currency', 'MVR');
    }
}