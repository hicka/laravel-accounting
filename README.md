# Laravel Accounting Package

> IFRS-compliant, multi-tenant, multi-currency accounting system for Laravel apps. Modular support for regional compliance (e.g., MIRA Maldives).

---

## ✨ Features

- Double-entry accounting (Journal + Ledger)
- Multi-tenant support (tenant = business/entity)
- Multi-currency ready (use your base + foreign currencies)
- Modular regional compliance (e.g., MIRA reports)
- Extensible Chart of Accounts
- UUID-based references for traceability
- Drop-in ready for SaaS or enterprise Laravel apps

---

## 📦 Installation

```bash
composer require hickr/laravel-accounting
php artisan vendor:publish --tag=config
```

### Add the following to your app's `composer.json`:
```json
"repositories": [
  {
    "type": "path",
    "url": "./packages/hickr/laravel-accounting"
  }
]
```

---

## ⚙️ Configuration

In `config/accounting.php`:

```php
return [
    'tenant_model' => App\Models\Tenant::class,
    'tenant_table' => 'tenants',
    'region_module_column' => 'region_module',
    'multi_currency' => true,
    'default_currency' => 'MVR',
];
```

In your `tenants` table (or `businesses`), add:

```php
$table->string('region_module')->default('global');
```

---

## 🧾 Journal Entry Posting

```php
use Hickr\Accounting\Actions\PostJournalEntryAction;

$entry = PostJournalEntryAction::execute([
    'tenant_id' => $tenant->id,
    'date' => '2025-07-14',
    'description' => 'Initial capital',
    'lines' => [
        [
            'account_id' => 1, // Cash
            'amount' => '10000.000000',
            'side' => 'debit',
            'memo' => 'Owner deposit',
        ],
        [
            'account_id' => 2, // Equity
            'amount' => '10000.000000',
            'side' => 'credit',
            'memo' => 'Capital account',
        ],
    ],
]);
```

Throws `UnbalancedJournalException` if debits ≠ credits.

---

## 🧩 Regional Modules

Regional logic (like MIRA GST201, WHT) is resolved per-tenant at runtime.

```php
use Hickr\Accounting\Support\Region\RegionResolver;

$module = RegionResolver::resolveForTenant($tenant);

$gst = $module?->generateReport('gst201', [
    'start_date' => '2025-01-01',
    'end_date' => '2025-03-31',
]);
```

Each region module implements:

```php
interface RegionalModule {
    public function getAvailableReports(): array;
    public function generateReport(string $key, array $params);
}
```

---

## ✅ Requirements

- PHP ^8.2
- Laravel 12+

---

## 📚 Upcoming

- Brick\Money integration for currency-safe math
- Default chart of accounts seeder
- Balance sheet, P&L reports
- MIRA GST201, WHT, Income Tax reports
- Pest + PHPUnit test coverage

---

## License

MIT License