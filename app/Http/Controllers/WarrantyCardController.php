<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Intervention\Image\Laravel\Facades\Image;

use App\Models\CustomerPpfInstallation;

class WarrantyCardController extends Controller
{
    /**
     * Preview inline (image/jpeg)
     * GET /warranty/ppf-card/{installation}
     */
    public function ppfPreview(int $installationId): BinaryFileResponse
    {
        [$tmpPath, $filename] = $this->buildPpfCard($installationId);

        return response()
            ->file($tmpPath, ['Content-Type' => 'image/jpeg'])
            ->deleteFileAfterSend(true);
    }

    /**
     * Download as attachment
     * GET /warranty/ppf-card/{installation}/download
     */
    public function ppfDownload(int $installationId): BinaryFileResponse
    {
        [$tmpPath, $filename] = $this->buildPpfCard($installationId);

        return response()
            ->download($tmpPath, $filename)
            ->deleteFileAfterSend(true);
    }

    /**
     * Build PPF card and return [tmpPath, filename]
     */
    private function buildPpfCard(int $installationId): array
    {
        $ins = CustomerPpfInstallation::with([
                'customer:id,name,vehicle_brand,vehicle_model,vehicle_plate',
                'ppfProduct:id,name'
            ])
            ->findOrFail($installationId);

        $customer = $ins->customer;
        $productName = (string) optional($ins->ppfProduct)->name;

        // Start/end date (fallback)
        $start = $ins->warranty_start ?? $ins->install_date ?? null;
        $end   = $ins->warranty_end ?? null;

        $validFrom  = $start ? date('m/d/Y', strtotime($start)) : '-';
        $validUntil = $end   ? date('m/d/Y', strtotime($end))   : '-';

        // Vehicle lines
        $vehicleLine1 = trim(($customer->vehicle_brand ?? '') . ' ' . ($customer->vehicle_model ?? ''));
        $vehicleLine2 = trim((string) ($customer->vehicle_plate ?? '-'));

        // Template + colors by product
        [$colors, $template] = $this->styleByPpfProduct($productName, (int) ($ins->warranty_duration ?? 0));

        $templatePath = public_path('assets/ppf/' . $template);

        $fontSemiBold = resource_path('fonts/Montserrat-semibold.ttf');
        $fontRegular  = resource_path('fonts/Montserrat-regular.ttf');

        $img = Image::read($templatePath);

        // Helper text writer
        $putText = function (
            string $text,
            string $fontPath,
            int $size,
            string $hexColor,
            string $align,
            int $xOffset,
            int $y
        ) use ($img) {
            $baseX = match ($align) {
                'left'   => 0,
                'center' => (int) ($img->width() / 2),
                'right'  => $img->width(),
            };

            $img->text($text ?: '-', $baseX + $xOffset, $y, function ($font) use ($fontPath, $size, $hexColor, $align) {
                $font->filename($fontPath);
                $font->size($size);
                $font->color('#' . ltrim($hexColor, '#'));
                $font->align(match ($align) {
                    'left' => 'left',
                    'right' => 'right',
                    default => 'center'
                });
                $font->valign('top');
            });
        };

        // --- SIMPLE AUTO SIZE (biar tidak kepanjangan)
        $fit = function(string $text, int $base, int $maxLen, int $step = 4, int $min = 18) {
            $len = mb_strlen($text);
            if ($len <= $maxLen) return $base;

            $over = $len - $maxLen;
            $reduce = (int) ceil($over / 6) * $step;
            return max($min, $base - $reduce);
        };

        // =========================
        // COORDINATES (template 1000x600)
        // Dark band starts ~y=259, line ~y=409 (hasil analisa template kamu)
        // =========================

        // NAME (center, above line)
        $name = Str::upper((string) ($customer->name ?? '-'));
        $nameSize = $fit($name, 44, 22, 4, 22);
        $putText($name, $fontSemiBold, $nameSize, $colors['name'], 'center', 0, 322);

        // LEFT: Valid From
        $putText('Valid From', $fontRegular, 24, $colors['label'], 'left', 70, 440);
        $putText($validFrom,   $fontRegular, 24, $colors['value'], 'left', 70, 472);

        // CENTER: Vehicle
        $veh1 = Str::upper($vehicleLine1 ?: '-');
        $veh2 = Str::upper($vehicleLine2 ?: '-');

        $veh1Size = $fit($veh1, 24, 22, 3, 18);
        $veh2Size = $fit($veh2, 24, 18, 3, 18);

        $putText($veh1, $fontRegular, $veh1Size, $colors['value'], 'center', 0, 452);
        $putText($veh2, $fontRegular, $veh2Size, $colors['value'], 'center', 0, 482);

        // RIGHT: Valid Until
        $putText('Valid Until', $fontRegular, 24, $colors['label'], 'right', -70, 440);
        $putText($validUntil,   $fontRegular, 24, $colors['value'], 'right', -70, 472);

        // Save to temp as JPG
        $tmpPath  = tempnam(sys_get_temp_dir(), 'ppf_card_') . '.jpg';
        $filename = 'ppf_card_' . Str::slug($customer->name ?? 'acegard') . '_' . $installationId . '.jpg';

        $img->save($tmpPath, 90, 'jpg');

        return [$tmpPath, $filename];
    }

    /**
     * Template mapping for PPF
     * - Template 26.png => AEGIS PLUS 8.5 MIL (7YR)
     * - Template 17.png => AEGIS MATTE 7.5 MIL (5YR)
     */
    private function styleByPpfProduct(string $productName, int $years = 0): array
    {
        $name = Str::of($productName)->lower()->toString();

        // Text color on dark band
        $colors = [
            'name'  => 'cfd8ee',
            'label' => 'cfd8ee',
            'value' => 'cfd8ee',
        ];

        // Default
        $template = 'Template 26.png';

        if (str_contains($name, 'aegis matte') || str_contains($name, 'matte')) {
            $template = 'Template 27.png';
        }

        if (str_contains($name, 'aegis plus') || $years >= 7) {
            $template = 'Template 17.png';
        }

        return [$colors, $template];
    }
}
