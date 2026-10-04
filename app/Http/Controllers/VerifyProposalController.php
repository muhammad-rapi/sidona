<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

class VerifyProposalController extends Controller
{
    public function __invoke(string $code, string $hash, AuditLogger $logger)
    {
        $campaign = Campaign::query()->where('proposal_code', strtoupper($code))->firstOrFail();

        abort_unless(hash_equals(sha1((string) $campaign->proposer_contact), $hash), 403);

        if ($campaign->proposer_verified_at === null) {
            $campaign->update(['proposer_verified_at' => now()]);
            $logger->log('campaign.proposer_verified', null, $campaign, [], ['proposer_verified_at' => $campaign->proposer_verified_at]);
        }

        session()->flash('status', 'Email terkonfirmasi. Admin sekarang bisa meninjau pengajuan Anda.');

        return new RedirectResponse(route('program.proposal', $campaign->proposal_code));
    }
}
