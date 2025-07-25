<?php
namespace Hickr\Accounting\Actions\Journals;

use Hickr\Accounting\Exceptions\UnbalancedJournalException;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Hickr\Accounting\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PostJournalEntryAction
{
    public static function execute(array $data): JournalEntry
    {
        // Validate input
        $validator = Validator::make($data, [
            'date'              => ['required', 'date'],
            'description'       => ['required', 'string'],
            'currency_code'     => ['required', 'string'],
            'exchange_rate'     => ['required', 'numeric', 'min:0.000001'],
            'tenant_id'         => ['required', 'integer'],
            'lines'             => ['required', 'array', 'min:2'],
            'lines.*.account_id'=> ['required', 'integer'],
            'lines.*.amount'    => ['required', 'numeric'],
            'lines.*.type'      => ['required', 'in:debit,credit'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $tenantModel = Tenant::class;

        if (!class_exists($tenantModel)) {
            throw new \RuntimeException("Invalid or missing tenant model configuration. Got: " . var_export($tenantModel, true));
        }

        $tenant = $data['tenant'] ?? $tenantModel::find($data['tenant_id']);

        if (!$tenant) {
            throw new \RuntimeException("Tenant with ID {$data['tenant_id']} not found.");
        }

        // Validate balanced entry
        $debitTotal = collect($data['lines'])->where('type', 'debit')->sum('amount');
        $creditTotal = collect($data['lines'])->where('type', 'credit')->sum('amount');

        if (round($debitTotal, 2) !== round($creditTotal, 2)) {
            throw new UnbalancedJournalException($debitTotal,$creditTotal);
        }

        return DB::transaction(function () use ($data, $tenant) {
            $entry = new JournalEntry();
            $entry->tenant_id = $tenant->id;
            $entry->date = $data['date'];
            $entry->description = $data['description'];
            $entry->currency_code = $data['currency_code'];
            $entry->exchange_rate = $data['exchange_rate'];

            $total = collect($data['lines'])->where('type', 'debit')->sum('amount');
            $entry->base_currency_amount = $total * $entry->exchange_rate;

            $entry->save();

            foreach ($data['lines'] as $line) {
                $account = ChartOfAccount::findOrFail($line['account_id']);

                // Validate GST type is only used on revenue accounts
                if (
                    isset($line['meta']['gst_type']) &&
                    !in_array($line['meta']['gst_type'], ['zero_rated', 'exempt'])
                ) {
                    throw new \InvalidArgumentException("Invalid gst_type value: {$line['meta']['gst_type']}");
                }

                if (
                    isset($line['meta']['supplier_name']) ||
                    isset($line['meta']['gst_amount']) // hinting it's a GST input
                ) {
                    $requiredMetaFields = ['supplier_name', 'supplier_tin', 'invoice_number', 'net_amount', 'gst_amount'];

                    foreach ($requiredMetaFields as $field) {
                        if (empty($line['meta'][$field])) {
                            throw new \InvalidArgumentException("Missing `$field` in journal line meta for GST Schedule 5 compliance.");
                        }
                    }
                }

                if (
                    isset($line['meta']['gst_type']) &&
                    $account->type !== ChartOfAccount::TYPE_REVENUE
                ) {
                    throw new \InvalidArgumentException("gst_type is only applicable to revenue accounts.");
                }


                JournalLine::create([
                    'tenant_id' => $data['tenant_id'],
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'type'             => $line['type'],
                    'amount'           => $line['amount'],
                ]);
            }

            return $entry;
        });
    }
}