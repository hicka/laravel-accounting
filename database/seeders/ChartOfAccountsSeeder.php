<?php
namespace Hickr\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Hickr\Accounting\Models\ChartOfAccount;

class ChartOfAccountsSeeder extends Seeder
{
    public function run($tenantId)
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset'],
            ['code' => '1010', 'name' => 'Bank Account', 'type' => 'asset'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset'],

            // Liabilities
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability'],
            ['code' => '2100', 'name' => 'Taxes Payable', 'type' => 'liability'],

            // Equity
            ['code' => '3000', 'name' => 'Owner Capital', 'type' => 'equity'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity'],

            // Income
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'income'],
            ['code' => '4100', 'name' => 'Service Income', 'type' => 'income'],

            // Expenses
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
            ['code' => '5100', 'name' => 'Salaries and Wages', 'type' => 'expense'],
            ['code' => '5200', 'name' => 'Utilities', 'type' => 'expense'],
            ['code' => '5300', 'name' => 'Rent Expense', 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create([
                ...$account,
                'tenant_id' => $tenantId,
            ]);
        }
    }
}