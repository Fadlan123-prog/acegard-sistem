<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use Illuminate\Database\Seeder;

class ApprovalFlowSeeder extends Seeder
{
    public function run(): void
    {
        $flows = [
            'employee_claim' => [
                'name'  => 'Klaim Karyawan (Pembelian/Bensin/Kesehatan)',
                'steps' => [
                    ['step_order' => 1, 'approver_role' => 'manager_operasional', 'same_branch_as_subject' => false],
                ],
            ],
            'kasbon' => [
                'name'  => 'Pengajuan Kasbon Karyawan',
                'steps' => [
                    ['step_order' => 1, 'approver_role' => 'manager_operasional', 'same_branch_as_subject' => false],
                ],
            ],
            'purchasing_transaction' => [
                'name'  => 'Transaksi Purchasing',
                'steps' => [
                    ['step_order' => 1, 'approver_role' => 'owner', 'same_branch_as_subject' => false],
                ],
            ],
            'finance_transaction' => [
                'name'  => 'Transaksi Finance',
                'steps' => [
                    ['step_order' => 1, 'approver_role' => 'owner', 'same_branch_as_subject' => false],
                ],
            ],
        ];

        foreach ($flows as $code => $data) {
            $flow = ApprovalFlow::firstOrCreate(['code' => $code], ['name' => $data['name'], 'is_active' => true]);

            foreach ($data['steps'] as $step) {
                $flow->steps()->firstOrCreate(
                    ['step_order' => $step['step_order']],
                    ['approver_role' => $step['approver_role'], 'same_branch_as_subject' => $step['same_branch_as_subject']]
                );
            }
        }
    }
}
