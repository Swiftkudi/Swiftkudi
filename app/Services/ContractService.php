<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractMilestone;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContractService
{
    public function createFromJobApplication(JobApplication $application): Contract
    {
        return DB::transaction(function () use ($application) {
            $existing = Contract::where('job_application_id', $application->id)->first();
            if ($existing) return $existing;

            $application->loadMissing(['job.user', 'user']);
            $job = $application->job;

            $contract = Contract::create([
                'contract_no' => $this->nextContractNumber(),
                'job_id' => $job->id,
                'job_application_id' => $application->id,
                'client_id' => $job->user_id,
                'freelancer_id' => $application->user_id,
                'title' => $job->title,
                'description' => $job->description,
                'contract_type' => 'fixed',
                'amount' => (float) ($application->proposal_amount ?: $job->budget_max ?: $job->budget_min ?: 0),
                'status' => Contract::STATUS_ACTIVE,
                'started_at' => now(),
            ]);

            $amount = (float) $contract->amount;
            if ($amount > 0) {
                ContractMilestone::create([
                    'contract_id' => $contract->id,
                    'title' => 'Project delivery',
                    'description' => 'Initial milestone created from the accepted proposal.',
                    'amount' => $amount,
                    'status' => ContractMilestone::STATUS_PENDING_FUNDING,
                ]);
            }

            return $contract->load('milestones');
        });
    }

    private function nextContractNumber(): string
    {
        do {
            $number = 'SKC-' . now()->format('Ym') . '-' . strtoupper(Str::random(8));
        } while (Contract::where('contract_no', $number)->exists());

        return $number;
    }
}
