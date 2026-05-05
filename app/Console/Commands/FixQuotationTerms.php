<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quotation;
use App\Models\Term;

class FixQuotationTerms extends Command
{
    protected $signature = 'fix:quotation-terms';

    protected $description = 'Convert old quotation term strings to IDs';

    public function handle()
    {
        $quotations = Quotation::all();

        foreach ($quotations as $quotation) {

            $terms = $quotation->terms;

            if (!is_array($terms) || empty($terms)) {
                continue;
            }

            // already migrated
            if (is_numeric($terms[0])) {
                continue;
            }

            $termIds = [];

            foreach ($terms as $termText) {

                $term = Term::whereRaw(
                    'LOWER(TRIM(text)) = ?',
                    [strtolower(trim($termText))]
                )->first();

                if ($term) {
                    $termIds[] = $term->id;
                } else {
                    $this->warn("Term not found: {$termText}");
                }
            }

            $quotation->terms = $termIds;

            $quotation->save();

            $this->info("Updated quotation #{$quotation->id}");
        }

        $this->info('Done!');
    }
}
