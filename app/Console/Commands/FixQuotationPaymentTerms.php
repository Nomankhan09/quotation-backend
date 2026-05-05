<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuotationPaymentTerm;
use App\Models\PaymentTerm;

class FixQuotationPaymentTerms extends Command
{
    protected $signature = 'fix:quotation-payment-terms';

    protected $description = 'Fix missing payment_term_id in quotation payment terms';

    public function handle()
    {
        $rows = QuotationPaymentTerm::whereNull('payment_term_id')->get();

        foreach ($rows as $row) {

            $paymentTerm = PaymentTerm::whereRaw(
                'LOWER(TRIM(description)) = ?',
                [strtolower(trim($row->description))]
            )
            ->where('value', $row->value)
            ->first();

            if ($paymentTerm) {

                $row->payment_term_id = $paymentTerm->id;

                $row->save();

                $this->info(
                    "Updated quotation_payment_term #{$row->id} => payment_term_id {$paymentTerm->id}"
                );

            } else {

                $this->warn(
                    "No matching payment term found for row #{$row->id}"
                );
            }
        }

        $this->info('Done!');
    }
}