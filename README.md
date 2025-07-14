# Laravel Accounting Package

A modular, multi-tenant, multi-currency accounting engine for Laravel 12+, supporting IFRS-compliant reports and regional extensions (e.g., MIRA Maldives).

---

## ✨ Features

- 📚 Double-entry accounting (Journal, Ledger, COA)
- 🧾 IFRS-compliant financial reports:
    - Trial Balance
    - Profit & Loss
    - Balance Sheet
    - General Ledger
- 🌐 Multi-currency support with exchange rates and inverse logic
- 🏢 Multi-tenancy (per business/entity)
- 🌍 Modular regional compliance (e.g., MIRA 201 via plugin)
- ✅ Pest/PHPUnit test coverage
- 🧱 Clean architecture & Laravel Action-based design
- 🛠️ Configurable tenant model and table names

---

## ⚙️ Installation

```bash
composer require hickr/laravel-accounting
```

### Publish Config & Migrations

```bash
php artisan vendor:publish --tag=accounting-config
php artisan vendor:publish --tag=accounting-migrations
```

---

## 🚀 Usage Examples

### Post a Journal Entry

```php
PostJournalEntryAction::execute([
    'tenant_id' => 1,
    'date' => '2025-01-01',
    'description' => 'Initial capital',
    'currency_code' => 'MVR',
    'exchange_rate' => 1,
    'lines' => [
        ['account_id' => 1, 'type' => 'debit', 'amount' => 1000],
        ['account_id' => 2, 'type' => 'credit', 'amount' => 1000],
    ],
]);
```

---

## 📊 Reports

### Trial Balance

```php
$data = TrialBalanceReportAction::run([
    'tenant_id' => 1,
    'date_from' => '2025-01-01',
    'date_to' => '2025-12-31',
    'group_by_type' => false,
]);
```

### Profit & Loss

```php
$data = ProfitAndLossReportAction::run([
    'tenant_id' => 1,
    'date_from' => '2025-01-01',
    'date_to' => '2025-12-31',
]);
```

### Balance Sheet

```php
$data = BalanceSheetReportAction::run([
    'tenant_id' => 1,
    'date_to' => '2025-12-31',
    'group_by_account' => true,
]);
```

### General Ledger

```php
$data = GeneralLedgerReportAction::run([
    'tenant_id' => 1,
    'date_from' => '2025-01-01',
    'date_to' => '2025-12-31',
]);
```

---

## ✅ Testing

```bash
vendor/bin/pest
# or
vendor/bin/phpunit
```

Uses [Orchestra Testbench](https://github.com/orchestral/testbench) to run package tests.

---

## 🧩 Regional Compliance

This package supports regional reporting plugins via config. For example:

```php
// config/accounting.php
'region_module' => \Hickr\Accounting\MIRA\MiraModule::class,
```

MIRA reports (GST 201, Income Tax) can be developed as standalone modules.

---

## 🧪 Tests Included

- Journal entries (balancing, currency conversion)
- Trial balance (flat & grouped)
- Profit & Loss
- Balance Sheet
- General Ledger

---

## 🧱 Architecture

- Laravel Actions (`lorisleiva/laravel-actions`)
- Tenant-aware by config
- Supports soft-deleted tenants
- Region support via strategy pattern

---

## 🗂️ Roadmap

- [x] Trial Balance
- [x] Profit & Loss
- [x] Balance Sheet
- [x] General Ledger
- [ ] Cash Flow Statement
- [ ] MIRA 201, 401, Income Tax Schedules
- [ ] Journal approvals / audit trail

---

## 🧾 License

MIT License