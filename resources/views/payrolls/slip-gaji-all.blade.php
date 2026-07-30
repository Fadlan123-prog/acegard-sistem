<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
@page { margin:15mm; }

body  { font-family:poppins,sans-serif; font-size:8pt; color:#1e293b; margin:0; padding:0; background:#fff; }
table { border-collapse:collapse; }
</style>
</head>
<body>

@foreach($payroll->items as $item)
@php
$earnTotal   = $item->prorate_salary
             + ($item->transport_allowance ?? 0)
             + ($item->meal_allowance      ?? 0)
             + ($item->overtime_pay        ?? 0);

$deductTotal = ($item->attendance_deduction ?? 0)
             + ($item->late_deduction       ?? 0)
             + ($item->main_deduction       ?? 0)
             + ($item->cash_advance         ?? 0)
             + ($item->bpjs_total_employee  ?? 0)
             + ($item->other_deduction      ?? 0);

$paidDays    = $item->paid_days ?? ($item->present_days + $item->sick_days);
$docNo       = 'PAY/'. \Carbon\Carbon::parse($payroll->month.'-01')->format('Y')
             . '/'. str_pad($payroll->id, 4,'0',STR_PAD_LEFT)
             . '/'. str_pad($item->employee_id ?? $item->id, 3,'0',STR_PAD_LEFT);

$hireDate    = $item->employee?->hire_date
             ? \Carbon\Carbon::parse($item->employee->hire_date)->isoFormat('D MMM Y')
             : '—';

$empStatus   = $item->employee?->employment_status
             ? ucwords(str_replace('_',' ', $item->employee->employment_status))
             : 'Aktif';

$bpjsLines   = [];
if(($item->bpjs_jht_employee ?? 0) > 0) $bpjsLines[] = 'JHT Rp '.number_format($item->bpjs_jht_employee,0,',','.');
if(($item->bpjs_jp_employee  ?? 0) > 0) $bpjsLines[] = 'JP Rp '.number_format($item->bpjs_jp_employee,0,',','.');
if(($item->bpjs_kes_employee ?? 0) > 0) $bpjsLines[] = 'Kes Rp '.number_format($item->bpjs_kes_employee,0,',','.');
@endphp
<div@if(!$loop->last) style="page-break-after:always;"@endif>

{{-- HEADER --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:8pt;">
    <tr>
        <td width="62%" style="background:#0f172a; vertical-align:top;">
            <div style="padding:13pt 16pt 12pt;">
                <div style="font-size:18pt; font-weight:700; color:#fff; line-height:1;">ACEGARD</div>
                <div style="font-size:6.5pt; color:#475569; margin-top:2pt;">Window Film &amp; Automotive Protection Specialist</div>
                <div style="height:1pt; background:#1e293b; margin:9pt 0;"></div>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="60" style="font-size:6.5pt; color:#64748b; vertical-align:top;">Cabang</td>
                        <td style="font-size:6.5pt; font-weight:700; color:#cbd5e1; vertical-align:top;">{{ $branch->name ?? '—' }}@if($branch->city ?? null), {{ $branch->city }}@endif</td>
                    </tr>
                    <tr>
                        <td width="60" style="font-size:6.5pt; color:#64748b; vertical-align:top; padding-top:3pt;">Dibuat</td>
                        <td style="font-size:6.5pt; color:#94a3b8; vertical-align:top; padding-top:3pt;">{{ now()->isoFormat('D MMMM Y, HH:mm') }} WIB</td>
                    </tr>
                </table>
            </div>
        </td>
        <td width="38%" style="background:#1e40af; vertical-align:top;">
            <div style="padding:13pt 48pt 12pt 16pt; text-align:right;">
                <div style="font-size:6.5pt; font-weight:700; color:#93c5fd; text-transform:uppercase; letter-spacing:.3pt;">Slip Penggajian</div>
                <div style="font-size:15pt; font-weight:700; color:#fff; margin-top:3pt; line-height:1.1;">{{ $period }}</div>
                <div style="height:1pt; background:#2563eb; margin:9pt 0;"></div>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size:6.5pt; color:#93c5fd;">No. Dokumen</td>
                        <td style="padding-right:4pt; font-size:6.5pt; font-weight:700; color:#fff; text-align:right;">{{ $docNo }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:6.5pt; color:#93c5fd; padding-top:3pt;">Hari Kerja</td>
                        <td style="padding-right:4pt; font-size:6.5pt; color:#fff; text-align:right; padding-top:3pt;">{{ $payroll->working_days ?? '—' }} hari</td>
                    </tr>
                    <tr>
                        <td style="font-size:6.5pt; color:#93c5fd; padding-top:3pt;">Status</td>
                        <td style="padding-right:4pt; font-size:6.5pt; font-weight:700; color:#fff; text-align:right; padding-top:3pt;">{{ strtoupper($payroll->status) }}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- EMPLOYEE INFORMATION CARD --}}
<div style="border:1pt solid #e2e8f0; border-radius:7pt; margin-bottom:8pt; background:#fff;">
    <div style="background:#1e40af; border-radius:7pt 7pt 0 0; padding:5pt 14pt;">
        <div style="font-size:6pt; font-weight:700; color:#bfdbfe; text-transform:uppercase; letter-spacing:.4pt;">&#9632;&nbsp; Data Karyawan</div>
    </div>
    <div style="padding:10pt 14pt 8pt;">
        <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:1pt solid #f1f5f9; padding-bottom:8pt; margin-bottom:9pt;">
            <tr>
                <td style="vertical-align:middle;">
                    <div style="font-size:13pt; font-weight:700; color:#0f172a; line-height:1;">{{ $item->employee_name }}</div>
                    <div style="font-size:7pt; color:#64748b; margin-top:2pt;">{{ $item->job_position ?? '—' }}</div>
                </td>
                <td width="32%" style="text-align:right; vertical-align:middle;">
                    <div style="font-size:6.5pt; color:#94a3b8;">ID Karyawan</div>
                    <div style="font-size:10pt; font-weight:700; color:#1e40af; margin-top:2pt;">#{{ str_pad($item->employee_id ?? 0, 4,'0',STR_PAD_LEFT) }}</div>
                </td>
            </tr>
        </table>
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="25%" style="padding-bottom:7pt; vertical-align:top;">
                    <div style="font-size:6pt; color:#94a3b8; text-transform:uppercase; margin-bottom:2pt;">Jabatan</div>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b;">{{ $item->job_position ?? '—' }}</div>
                </td>
                <td width="25%" style="padding-bottom:7pt; vertical-align:top;">
                    <div style="font-size:6pt; color:#94a3b8; text-transform:uppercase; margin-bottom:2pt;">Cabang</div>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b;">{{ $branch->name ?? '—' }}</div>
                </td>
                <td width="25%" style="padding-bottom:7pt; vertical-align:top;">
                    <div style="font-size:6pt; color:#94a3b8; text-transform:uppercase; margin-bottom:2pt;">Status</div>
                    <div style="font-size:7.5pt; font-weight:700; color:#15803d;">{{ $empStatus }}</div>
                </td>
                <td width="25%" style="padding-bottom:7pt; vertical-align:top;">
                    <div style="font-size:6pt; color:#94a3b8; text-transform:uppercase; margin-bottom:2pt;">Tgl. Bergabung</div>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b;">{{ $hireDate }}</div>
                </td>
            </tr>
            <tr>
                <td width="25%" style="vertical-align:top;">
                    <div style="font-size:6pt; color:#94a3b8; text-transform:uppercase; margin-bottom:2pt;">Gaji Pokok</div>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b;">Rp {{ number_format($item->monthly_salary,0,',','.') }}</div>
                </td>
                <td width="25%" style="vertical-align:top;">
                    <div style="font-size:6pt; color:#94a3b8; text-transform:uppercase; margin-bottom:2pt;">Tarif Harian</div>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b;">Rp {{ number_format($item->daily_prorate,0,',','.') }}</div>
                </td>
                <td width="25%" style="vertical-align:top;">
                    <div style="font-size:6pt; color:#94a3b8; text-transform:uppercase; margin-bottom:2pt;">Hari Dibayar</div>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b;">{{ $paidDays }} hari</div>
                </td>
                <td width="25%" style="vertical-align:top;">
                    <div style="font-size:6pt; color:#94a3b8; text-transform:uppercase; margin-bottom:2pt;">Periode</div>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b;">{{ $period }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- ATTENDANCE SUMMARY CARD --}}
<div style="border:1pt solid #e2e8f0; border-radius:7pt; margin-bottom:8pt; background:#fff;">
    <div style="background:#0369a1; border-radius:7pt 7pt 0 0; padding:5pt 14pt;">
        <div style="font-size:6pt; font-weight:700; color:#bae6fd; text-transform:uppercase; letter-spacing:.4pt;">&#9632;&nbsp; Rekap Kehadiran &nbsp;&mdash;&nbsp; {{ $payroll->working_days ?? '—' }} Hari Kerja</div>
    </div>
    <div style="padding:10pt 14pt;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="16%"><div style="padding-right:4pt;">
                    <div style="border:1pt solid #bbf7d0; background:#f0fdf4; border-radius:6pt; padding:8pt 3pt; text-align:center;">
                        <div style="font-size:15pt; font-weight:700; color:#15803d; line-height:1;">{{ $item->present_days }}</div>
                        <div style="font-size:5.5pt; font-weight:700; color:#16a34a; text-transform:uppercase; margin-top:3pt;">Masuk</div>
                        <div style="font-size:5pt; color:#86efac; margin-top:1pt;">hari</div>
                    </div>
                </div></td>
                <td width="16%"><div style="padding-right:4pt;">
                    <div style="border:1pt solid #e2e8f0; background:#f8fafc; border-radius:6pt; padding:8pt 3pt; text-align:center;">
                        <div style="font-size:15pt; font-weight:700; color:#475569; line-height:1;">{{ $item->off_days }}</div>
                        <div style="font-size:5.5pt; font-weight:700; color:#64748b; text-transform:uppercase; margin-top:3pt;">Libur</div>
                        <div style="font-size:5pt; color:#94a3b8; margin-top:1pt;">hari</div>
                    </div>
                </div></td>
                <td width="16%"><div style="padding-right:4pt;">
                    <div style="border:1pt solid #bfdbfe; background:#eff6ff; border-radius:6pt; padding:8pt 3pt; text-align:center;">
                        <div style="font-size:15pt; font-weight:700; color:#1d4ed8; line-height:1;">{{ $item->sick_days }}</div>
                        <div style="font-size:5.5pt; font-weight:700; color:#2563eb; text-transform:uppercase; margin-top:3pt;">Sakit</div>
                        <div style="font-size:5pt; color:#93c5fd; margin-top:1pt;">hari</div>
                    </div>
                </div></td>
                <td width="16%"><div style="padding-right:4pt;">
                    <div style="border:1pt solid #fed7aa; background:#fff7ed; border-radius:6pt; padding:8pt 3pt; text-align:center;">
                        <div style="font-size:15pt; font-weight:700; color:#c2410c; line-height:1;">{{ $item->permission_days }}</div>
                        <div style="font-size:5.5pt; font-weight:700; color:#ea580c; text-transform:uppercase; margin-top:3pt;">Izin</div>
                        <div style="font-size:5pt; color:#fdba74; margin-top:1pt;">hari</div>
                    </div>
                </div></td>
                <td width="16%"><div style="padding-right:4pt;">
                    <div style="border:1pt solid #fecaca; background:#fef2f2; border-radius:6pt; padding:8pt 3pt; text-align:center;">
                        <div style="font-size:15pt; font-weight:700; color:#dc2626; line-height:1;">{{ $item->absent_days }}</div>
                        <div style="font-size:5.5pt; font-weight:700; color:#dc2626; text-transform:uppercase; margin-top:3pt;">Alpha</div>
                        <div style="font-size:5pt; color:#fca5a5; margin-top:1pt;">hari</div>
                    </div>
                </div></td>
                <td width="20%">
                    <div style="border:1pt solid #e9d5ff; background:#faf5ff; border-radius:6pt; padding:8pt 3pt; text-align:center;">
                        <div style="font-size:15pt; font-weight:700; color:#7c3aed; line-height:1;">{{ $item->excess_off_days }}</div>
                        <div style="font-size:5.5pt; font-weight:700; color:#7c3aed; text-transform:uppercase; margin-top:3pt;">Lbh Libur</div>
                        <div style="font-size:5pt; color:#c4b5fd; margin-top:1pt;">hari</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- SALARY DETAILS --}}
<div style="border:1pt solid #d1fae5; border-radius:7pt; background:#fff; margin-bottom:8pt;">
    <div style="background:#166534; border-radius:7pt 7pt 0 0; padding:5pt 14pt;">
        <div style="font-size:6pt; font-weight:700; color:#bbf7d0; text-transform:uppercase; letter-spacing:.4pt;">&#9650;&nbsp; Rincian Gaji</div>
    </div>
    <div style="padding:8pt 14pt;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding:5pt 6pt; font-size:7.5pt; color:#374151; background:#f8fafc;">Gaji Pokok</td>
                <td style="padding:5pt 6pt; font-size:7.5pt; font-weight:700; color:#1e293b; text-align:right; background:#f8fafc;">Rp {{ number_format($item->monthly_salary,0,',','.') }}</td>
            </tr>
            <tr>
                <td style="padding:5pt 6pt; border-top:1pt solid #f1f5f9;">
                    <div style="font-size:7.5pt; color:#374151;">Gaji Dihitung</div>
                    <div style="font-size:6pt; color:#94a3b8; margin-top:1pt;">{{ $paidDays }} hari x Rp {{ number_format($item->daily_prorate,0,',','.') }}</div>
                </td>
                <td style="padding:5pt 6pt; font-size:7.5pt; font-weight:700; color:#1e293b; text-align:right; border-top:1pt solid #f1f5f9;">Rp {{ number_format($item->prorate_salary,0,',','.') }}</td>
            </tr>
            @if(($item->transport_allowance ?? 0) > 0)
            <tr>
                <td style="padding:5pt 6pt; font-size:7.5pt; color:#374151; background:#f8fafc; border-top:1pt solid #f1f5f9;">Tunjangan Transport</td>
                <td style="padding:5pt 6pt; font-size:7.5pt; font-weight:700; color:#15803d; text-align:right; background:#f8fafc; border-top:1pt solid #f1f5f9;">+ Rp {{ number_format($item->transport_allowance,0,',','.') }}</td>
            </tr>
            @endif
            @if(($item->meal_allowance ?? 0) > 0)
            <tr>
                <td style="padding:5pt 6pt; font-size:7.5pt; color:#374151; border-top:1pt solid #f1f5f9;">Tunjangan Makan</td>
                <td style="padding:5pt 6pt; font-size:7.5pt; font-weight:700; color:#15803d; text-align:right; border-top:1pt solid #f1f5f9;">+ Rp {{ number_format($item->meal_allowance,0,',','.') }}</td>
            </tr>
            @endif
            @if(($item->overtime_pay ?? 0) > 0)
            <tr>
                <td style="padding:5pt 6pt; background:#f8fafc; border-top:1pt solid #f1f5f9;">
                    <div style="font-size:7.5pt; color:#374151;">Upah Lembur</div>
                    <div style="font-size:6pt; color:#94a3b8; margin-top:1pt;">{{ $item->overtime_minutes ?? 0 }} menit</div>
                </td>
                <td style="padding:5pt 6pt; font-size:7.5pt; font-weight:700; color:#15803d; text-align:right; background:#f8fafc; border-top:1pt solid #f1f5f9;">+ Rp {{ number_format($item->overtime_pay,0,',','.') }}</td>
            </tr>
            @endif
            @if($deductTotal > 0)
            <tr>
                <td style="padding:5pt 6pt; font-size:7.5pt; color:#374151; border-top:1pt solid #f1f5f9;">Total Potongan</td>
                <td style="padding:5pt 6pt; font-size:7.5pt; font-weight:700; color:#dc2626; text-align:right; border-top:1pt solid #f1f5f9;">- Rp {{ number_format($deductTotal,0,',','.') }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="2" style="height:4pt; border-top:1.5pt solid #d1fae5;"></td>
            </tr>
            <tr>
                <td style="padding:6pt 6pt; background:#f0fdf4;">
                    <div style="font-size:7.5pt; font-weight:700; color:#166534;">Total Penghasilan</div>
                </td>
                <td style="padding:6pt 6pt; background:#f0fdf4; text-align:right;">
                    <div style="font-size:8.5pt; font-weight:700; color:#166534;">Rp {{ number_format($earnTotal,0,',','.') }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- NET SALARY --}}
<div style="background:#0f172a; border-radius:8pt; margin-bottom:8pt;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="60%" style="background:#0f172a; vertical-align:middle;">
                <div style="padding:13pt 18pt;">
                    <div style="font-size:6.5pt; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.4pt;">Gaji Bersih Diterima</div>
                    <div style="font-size:6.5pt; color:#334155; margin-top:3pt;">Dibulatkan ke Rp1.000 terdekat &nbsp;&middot;&nbsp; {{ $period }}</div>
                </div>
            </td>
            <td style="background:#1e3a8a; vertical-align:middle;">
                <div style="padding:13pt 18pt; text-align:right;">
                    <div style="font-size:19pt; font-weight:700; color:#fff; line-height:1;">Rp {{ number_format($item->rounded_salary,0,',','.') }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- NOTES --}}
@if($item->notes)
<div style="border:1pt solid #fef08a; background:#fefce8; border-radius:6pt; padding:7pt 12pt; margin-bottom:8pt;">
    <div style="font-size:6.5pt; font-weight:700; color:#854d0e; margin-bottom:2pt;">Catatan</div>
    <div style="font-size:7.5pt; color:#713f12;">{{ $item->notes }}</div>
</div>
@endif

{{-- SIGNATURE --}}
<div style="border:1pt solid #e2e8f0; border-radius:7pt; margin-bottom:8pt; background:#fff;">
    <div style="background:#475569; border-radius:7pt 7pt 0 0; padding:5pt 14pt;">
        <div style="font-size:6pt; font-weight:700; color:#cbd5e1; text-transform:uppercase; letter-spacing:.4pt;">&#9632;&nbsp; Persetujuan &amp; Tanda Tangan</div>
    </div>
    <div style="padding:12pt 14pt 10pt;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="33%" style="text-align:center; vertical-align:bottom;">
                    <div style="padding-right:6pt;">
                    <table width="100" cellpadding="0" cellspacing="0" align="center">
                        <tr><td style="height:34pt; border-bottom:1pt solid #cbd5e1;"></td></tr>
                    </table>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b; margin-top:5pt;">HRD / Admin</div>
                    <div style="font-size:6.5pt; color:#94a3b8; margin-top:1pt;">Dibuat Oleh</div>
                    </div>
                </td>
                <td width="34%" style="text-align:center; vertical-align:bottom;">
                    <div style="padding:0 3pt;">
                    <table width="100" cellpadding="0" cellspacing="0" align="center">
                        <tr><td style="height:34pt; border-bottom:1pt solid #cbd5e1;"></td></tr>
                    </table>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b; margin-top:5pt;">Pimpinan Cabang</div>
                    <div style="font-size:6.5pt; color:#94a3b8; margin-top:1pt;">{{ $branch->name ?? '—' }}</div>
                    </div>
                </td>
                <td width="33%" style="text-align:center; vertical-align:bottom;">
                    <div style="padding-left:6pt;">
                    <table width="100" cellpadding="0" cellspacing="0" align="center">
                        <tr><td style="height:34pt; border-bottom:1pt solid #cbd5e1;"></td></tr>
                    </table>
                    <div style="font-size:7.5pt; font-weight:700; color:#1e293b; margin-top:5pt;">{{ $item->employee_name }}</div>
                    <div style="font-size:6.5pt; color:#94a3b8; margin-top:1pt;">Karyawan</div>
                    </div>
                </td>
            </tr>
        </table>
        <div style="text-align:center; font-size:6pt; color:#94a3b8; margin-top:10pt; border-top:1pt solid #f8fafc; padding-top:6pt;">
            Dokumen ini sah tanpa stempel &nbsp;&middot;&nbsp; Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} WIB
        </div>
    </div>
</div>

{{-- FOOTER --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border-top:1pt solid #e2e8f0;">
    <tr>
        <td style="padding-top:6pt; font-size:5.5pt; color:#94a3b8;">
            Dokumen ini dibuat secara otomatis oleh Sistem ERP ACEGARD. Tidak memerlukan tanda tangan basah jika telah ditandatangani secara digital.
        </td>
        <td style="padding-top:6pt; font-size:5.5pt; color:#94a3b8; text-align:right; white-space:nowrap;">
            {{ $docNo }} &nbsp;&middot;&nbsp; {{ now()->format('d/m/Y H:i') }}
        </td>
    </tr>
</table>

</div>
@endforeach

</body>
</html>
