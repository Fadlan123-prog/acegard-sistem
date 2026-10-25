<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class InvoicePdfService
{
    public function output(Invoice $invoice): string
    {
        $invoice->load([
            'customer.branch',
            'customer.customerProducts.part',
            'customer.customerProducts.product',
            'customer.customerProducts.categoryProduct',
            'customer.tukang:id,name',
            'customer.kenek:id,name',
        ]);

        $customer = $invoice->customer;
        if (!$customer) {
            throw new \RuntimeException('Customer untuk invoice ini tidak ditemukan.');
        }

        $branch    = $customer->branch;
        $branchKey = $branch ? Str::slug($branch->name) : 'default';
        $view      = $this->resolveInvoiceViewForBranch($branchKey);

        // Ambil dari ENV biar aman untuk server/cli/queue
        $publicPath = env('INVOICE_PUBLIC_PATH', public_path());

        app()->usePublicPath($publicPath);
        app()->instance('path.public', $publicPath);

        config([
            'dompdf.public_path'     => $publicPath,
            'dompdf.chroot'          => $publicPath,
            'dompdf.isRemoteEnabled' => true,
        ]);

        $pdf = Pdf::setPaper('A4', 'portrait')
            ->setOptions([
                'chroot'          => $publicPath,
                'isRemoteEnabled' => true,
            ])
            ->loadView($view, [
                'invoice'         => $invoice,
                'customer'        => $customer,
                'customerProduct' => $customer->customerProducts,
                'branch'          => $branch,
            ]);

        return $pdf->output(); // <— binary PDF
    }

    protected function resolveInvoiceViewForBranch(string $branchKey): string
    {
        $candidate1 = "invoice.branches.$branchKey";
        if (view()->exists($candidate1)) return $candidate1;

        $candidate2 = "invoice.pdf_{$branchKey}";
        if (view()->exists($candidate2)) return $candidate2;

        return 'invoice.pdf';
    }
}
