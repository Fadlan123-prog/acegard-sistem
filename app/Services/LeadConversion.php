<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Model;

/**
 * Konversi lead -> customer tanpa quotation/SO: form customer diisi dari data lead,
 * dan lead ditandai terkonversi saat customer tersimpan.
 */
class LeadConversion
{
    /** Lead yang boleh dijadikan sumber isian: cabang boleh diakses, belum terkonversi, belum gagal. */
    public function find(?int $leadId): ?Lead
    {
        if (! $leadId) {
            return null;
        }

        $lead = Lead::find($leadId);

        if (! $lead || $lead->isConverted() || $lead->status === 'lost') {
            return null;
        }

        abort_unless(
            auth()->user()->canAccessBranch($lead->branch_id !== null ? (int) $lead->branch_id : null),
            403,
            'Lead ini milik cabang lain.'
        );

        return $lead;
    }

    public function markConverted(?int $leadId, Model $customer): void
    {
        $lead = $this->find($leadId);

        if (! $lead) {
            return;
        }

        $lead->update([
            'status'            => 'won',
            'converted_to_type' => $customer::class,
            'converted_to_id'   => $customer->getKey(),
            'converted_at'      => now(),
        ]);
    }
}
