<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Intervention\Image\Laravel\Facades\Image;

class PublicWarrantyCardController extends Controller
{
    public function previewFilm(Request $request, string $cardNumber): BinaryFileResponse
    {
        $cp   = $request->query('cp');
        $side = $request->query('side', 'front');

        [$tmpPath, $filename] = $this->renderFilmCard($cardNumber, $cp ? (int) $cp : null, $side);

        return response()->file($tmpPath, [
            'Content-Type'        => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ])->deleteFileAfterSend(true);
    }

    public function downloadFilm(Request $request, string $cardNumber): BinaryFileResponse
    {
        $cp   = $request->query('cp');
        $side = $request->query('side', 'front');

        [$tmpPath, $filename] = $this->renderFilmCard($cardNumber, $cp ? (int) $cp : null, $side);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    protected function renderFilmCard(string $cardNumber, ?int $cp = null, string $side = 'front'): array
    {
        $normalize = preg_replace('/[\s-]+/', '', trim((string) $cardNumber));
        $side = strtolower($side) === 'back' ? 'back' : 'front';

        // =========================
        // CAR FILM
        // =========================
        $car = DB::table('customers as c')
            ->leftJoin('customer_product as cp', 'cp.customer_id', '=', 'c.id')
            ->leftJoin('category_products as cat', 'cat.id', '=', 'cp.category_product_id')
            ->leftJoin('products as p', 'p.id', '=', 'cp.product_id')
            ->when($cp, fn ($q) => $q->where('cp.id', $cp))
            ->where(function ($q) use ($normalize) {
                if (ctype_digit($normalize)) {
                    $q->where('c.card_number', (int) $normalize);
                }

                $q->orWhereRaw('CAST(c.card_number AS CHAR) = ?', [$normalize]);
            })
            ->select([
                'c.id as customer_id',
                'c.name',
                DB::raw('CAST(c.card_number AS CHAR) as card_number'),
                'c.vehicle_brand',
                'c.plat_number',
                'c.warantee_start',
                'c.warantee_end',
                'c.warantee_duration',

                'cat.id as cat_id',
                'cat.name as category_name',
                'cat.duration as cat_duration',

                DB::raw('COALESCE(p.name, "") as product_name'),
            ])
            ->first();

        $isBuilding = false;
        $row = $car;

        // =========================
        // BUILDING FILM FALLBACK
        // =========================
        if (!$row) {
            $building = DB::table('customer_building as cb')
                ->leftJoin('customer_building_product as cbp', 'cbp.customer_building_id', '=', 'cb.id')
                ->leftJoin('category_product_building as ccat', 'ccat.id', '=', 'cbp.category_product_building_id')
                ->leftJoin('product_building as pb', 'pb.id', '=', 'cbp.product_building_id')
                ->whereRaw('REPLACE(REPLACE(cb.card_number, " ", ""), "-", "") = ?', [$normalize])
                ->select([
                    'cb.id as customer_building_id',
                    'cb.name',
                    'cb.card_number',
                    'cb.address',
                    'cb.city',

                    'cbp.warantee_start as warantee_start',
                    'cbp.warantee_end as warantee_end',
                    'cbp.warantee_duration as warantee_duration',

                    'ccat.id as cat_id',
                    'ccat.name as category_name',
                    'ccat.duration as cat_duration',

                    DB::raw('COALESCE(pb.name, "") as product_name'),
                ])
                ->first();

            if (!$building) {
                abort(404);
            }

            $isBuilding = true;
            $row = $building;
        }

        // =========================
        // SIDE BACK
        // =========================
        if ($side === 'back') {
            $productIdentity = trim(
                (string) ($row->category_name ?? '') . ' ' . (string) ($row->product_name ?? '')
            );

            $template = $this->filmBackTemplateByProduct($productIdentity);
            $templatePath = public_path('assets/' . $template);

            if (!file_exists($templatePath)) {
                abort(404, "Template not found: {$template}");
            }

            $img = Image::read($templatePath);

            $tmpPath  = tempnam(sys_get_temp_dir(), 'card_back_') . '.jpg';
            $filename = 'card_back_' . Str::slug((string) ($row->card_number ?? 'acegard')) . '.jpg';

            $img->save($tmpPath, 90, 'jpg');

            return [$tmpPath, $filename];
        }

        // =========================
        // SIDE FRONT
        // =========================
        [$colors, $template] = $this->styleByCategory((int) ($row->cat_id ?? 1));

        $templatePath = public_path('assets/' . $template);

        if (!file_exists($templatePath)) {
            abort(404, "Template not found: {$template}");
        }

        $img = Image::read($templatePath);

        // Sesuaikan dengan nama file font yang ada di server
        $fontSemiBold = resource_path('fonts/Montserrat-semibold.ttf');
        $fontRegular  = resource_path('fonts/Montserrat-regular.ttf');
        $fontHeavy    = resource_path('fonts/Mont-HeavyDEMO.otf');

        foreach ([$fontSemiBold, $fontRegular, $fontHeavy] as $fontPath) {
            if (!file_exists($fontPath)) {
                abort(500, "Font file not found: {$fontPath}");
            }
        }

        $putText = function (
            $text,
            string $fontPath,
            int $size,
            string $hexColor,
            string $align,
            int $xOffset,
            int $yOffset
        ) use ($img) {
            $baseX = match ($align) {
                'left'   => 0,
                'center' => (int) ($img->width() / 2),
                'right'  => $img->width(),
                default  => (int) ($img->width() / 2),
            };

            $img->text($text ?: '-', $baseX + $xOffset, $yOffset, function ($font) use ($fontPath, $size, $hexColor, $align) {
                $font->filename($fontPath);
                $font->size($size);
                $font->color('#' . ltrim($hexColor, '#'));
                $font->align(match ($align) {
                    'left'  => 'left',
                    'right' => 'right',
                    default => 'center',
                });
                $font->valign('top');
            });
        };

        $putText($row->name,        $fontSemiBold, 40, $colors['text1'], 'center', -13, 280);
        $putText($row->card_number, $fontSemiBold, 30, $colors['text2'], 'center', -13, 340);

        if (!$isBuilding) {
            $putText($row->vehicle_brand, $fontRegular, 30, $colors['left1'], 'left', 80, 411);
            $putText($row->plat_number,   $fontRegular, 30, $colors['left2'], 'left', 80, 450);
        } else {
            $putText($row->city,    $fontRegular, 30, $colors['left1'], 'left', 80, 411);
            $putText($row->address, $fontRegular, 30, $colors['left2'], 'left', 80, 450);
        }

        $putText('Valid From', $fontRegular, 30, $colors['center1'], 'center', 5, 411);
        $putText(
            $row->warantee_start ? date('d/m/Y', strtotime($row->warantee_start)) : '-',
            $fontRegular,
            30,
            $colors['center2'],
            'center',
            5,
            450
        );

        $putText('Valid Until', $fontRegular, 30, $colors['right1'], 'right', -80, 411);
        $putText(
            $row->warantee_end ? date('d/m/Y', strtotime($row->warantee_end)) : '-',
            $fontRegular,
            30,
            $colors['right2'],
            'right',
            -80,
            450
        );

        $putText(
            (string) ($row->warantee_duration ?? $row->cat_duration ?? '-'),
            $fontHeavy,
            90,
            $colors['year'],
            'right',
            -140,
            60
        );

        $tmpPath  = tempnam(sys_get_temp_dir(), 'card_front_') . '.jpg';
        $filename = 'card_front_' . Str::slug((string) ($row->card_number ?? 'acegard')) . '.jpg';

        $img->save($tmpPath, 90, 'jpg');

        return [$tmpPath, $filename];
    }

    /**
     * Back template film:
     * Notch UV / NV => Template 18.jpg
     * 4K / Alpha / AP => Template 29.jpg
     */
    protected function filmBackTemplateByProduct(string $productName): string
    {
        $p = strtolower(trim($productName));

        if (
            str_contains($p, 'notch') ||
            str_contains($p, 'uv') ||
            str_contains($p, 'nv')
        ) {
            return 'Template 18.jpg';
        }

        if (
            str_contains($p, '4k') ||
            str_contains($p, 'alpha') ||
            str_contains($p, 'ap')
        ) {
            return 'Template 29.jpg';
        }

        return 'Template 18.jpg';
    }

    private function styleByCategory(int $catId): array
    {
        $colors = [
            'text1'   => 'ffffff',
            'text2'   => 'ffffff',
            'left1'   => '000000',
            'left2'   => '000000',
            'center1' => '000000',
            'center2' => '000000',
            'right1'  => '000000',
            'right2'  => '000000',
            'year'    => '055995',
        ];

        $template = 'kartu1.jpg';

        if ($catId === 2) {
            $colors = [
                'text1'   => '000000',
                'text2'   => '000000',
                'left1'   => 'ffffff',
                'left2'   => 'ffffff',
                'center1' => 'ffffff',
                'center2' => 'ffffff',
                'right1'  => 'ffffff',
                'right2'  => 'ffffff',
                'year'    => 'ffffff',
            ];

            $template = 'kartu2.jpg';
        } elseif ($catId === 3) {
            $colors = [
                'text1'   => '000000',
                'text2'   => '000000',
                'left1'   => 'ffffff',
                'left2'   => 'ffffff',
                'center1' => 'ffffff',
                'center2' => 'ffffff',
                'right1'  => 'ffffff',
                'right2'  => 'ffffff',
                'year'    => 'ffffff',
            ];

            $template = 'kartu3.jpg';
        }

        return [$colors, $template];
    }

    /**
     * ============================
     * PPF FRONT + BACK
     * ============================
     */

    public function previewPpf(Request $request, int $installationId): BinaryFileResponse
    {
        $side = strtolower((string) $request->query('side', 'front'));

        if (!in_array($side, ['front', 'back'], true)) {
            $side = 'front';
        }

        [$tmpPath, $filename] = $this->renderPpfCard($installationId, $side);

        return response()->file($tmpPath, [
            'Content-Type'        => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ])->deleteFileAfterSend(true);
    }

    public function downloadPpf(Request $request, int $installationId): BinaryFileResponse
    {
        $side = strtolower((string) $request->query('side', 'front'));

        if (!in_array($side, ['front', 'back'], true)) {
            $side = 'front';
        }

        [$tmpPath, $filename] = $this->renderPpfCard($installationId, $side);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    protected function renderPpfCard(int $installationId, string $side = 'front'): array
    {
        $row = DB::table('customer_ppf_installations as i')
            ->join('customer_ppf as c', 'c.id', '=', 'i.customer_ppf_id')
            ->leftJoin('ppf_products as p', 'p.id', '=', 'i.ppf_product_id')
            ->select([
                'i.id as installation_id',
                'i.install_date',
                'i.warranty_end',
                'c.name',
                'c.vehicle_brand',
                'c.vehicle_model',
                'c.vehicle_plate',
                DB::raw('COALESCE(p.name, "") as product_name'),
            ])
            ->where('i.id', $installationId)
            ->first();

        if (!$row) {
            abort(404);
        }

        $start = $row->install_date ? Carbon::parse($row->install_date) : null;
        $end   = $row->warranty_end ? Carbon::parse($row->warranty_end) : null;

        $frontTemplate = $this->ppfTemplateByProduct((string) $row->product_name, $start, $end);
        $backTemplate  = $this->ppfBackTemplateByProduct((string) $row->product_name);

        $template = $side === 'back' ? $backTemplate : $frontTemplate;

        $templatePath = public_path('assets/' . $template);

        if (!file_exists($templatePath)) {
            abort(404, "Template not found: {$template}");
        }

        $img = Image::read($templatePath);

        if ($side !== 'back') {
            $fontBold = resource_path('fonts/Montserrat-semibold.ttf');
            $fontReg  = resource_path('fonts/Montserrat-regular.ttf');

            foreach ([$fontBold, $fontReg] as $fontPath) {
                if (!file_exists($fontPath)) {
                    abort(500, "Font file not found: {$fontPath}");
                }
            }

            $put = function (
                string $text,
                string $fontPath,
                int $size,
                string $hexColor,
                string $align,
                int $xOffset,
                int $yOffset
            ) use ($img) {
                $baseX = match ($align) {
                    'left'   => 0,
                    'center' => (int) ($img->width() / 2),
                    'right'  => $img->width(),
                    default  => (int) ($img->width() / 2),
                };

                $img->text($text ?: '-', $baseX + $xOffset, $yOffset, function ($font) use ($fontPath, $size, $hexColor, $align) {
                    $font->filename($fontPath);
                    $font->size($size);
                    $font->color('#' . ltrim($hexColor, '#'));
                    $font->align(match ($align) {
                        'left'  => 'left',
                        'right' => 'right',
                        default => 'center',
                    });
                    $font->valign('top');
                });
            };

            $textColor = 'cfd6e6';

            $name = strtoupper(trim((string) ($row->name ?? '-')));
            $nameSize = $this->fitFontSize($name, $fontBold, 700, 44, 22);

            $put($name, $fontBold, $nameSize, $textColor, 'center', 0, 312);

            $vehicle = strtoupper(trim((string) ($row->vehicle_brand ?? '') . ' ' . (string) ($row->vehicle_model ?? '')));
            $plate   = strtoupper(trim((string) ($row->vehicle_plate ?? '-')));

            $vehSize = $this->fitFontSize($vehicle ?: '-', $fontReg, 520, 26, 18);

            $put($vehicle ?: '-', $fontReg, $vehSize, $textColor, 'center', 0, 432);
            $put($plate,          $fontReg, 24,       $textColor, 'center', 0, 462);

            $put('Valid From', $fontReg, 22, $textColor, 'left', 62, 430);
            $put($start ? $start->format('m/d/Y') : '-', $fontReg, 22, $textColor, 'left', 62, 460);

            $put('Valid Until', $fontReg, 22, $textColor, 'right', -62, 430);
            $put($end ? $end->format('m/d/Y') : '-', $fontReg, 22, $textColor, 'right', -62, 460);
        }

        $tmpPath  = tempnam(sys_get_temp_dir(), 'ppf_card_') . '.jpg';
        $filename = 'ppf_card_' . $row->installation_id . '_' . $side . '.jpg';

        $img->save($tmpPath, 90, 'jpg');

        return [$tmpPath, $filename];
    }

    protected function ppfTemplateByProduct(string $productName, ?Carbon $start, ?Carbon $end): string
    {
        $p = strtolower($productName);

        if (str_contains($p, '8.5')) {
            return 'Template 17.png';
        }

        if (str_contains($p, '7.5')) {
            return 'Template 26.png';
        }

        $years = 0;

        if ($start && $end) {
            $years = max(1, $start->diffInYears($end));
        }

        return $years >= 7 ? 'Template 17.png' : 'Template 26.png';
    }

    protected function ppfBackTemplateByProduct(string $productName): string
    {
        $p = strtolower($productName);

        if (str_contains($p, 'matte')) {
            return 'Template 28.jpg';
        }

        return 'Template 16.jpg';
    }

    protected function fitFontSize(string $text, string $fontPath, int $maxWidth, int $startSize, int $minSize = 16): int
    {
        $size = $startSize;

        while ($size > $minSize) {
            $box = imagettfbbox($size, 0, $fontPath, $text);
            $w = abs($box[2] - $box[0]);

            if ($w <= $maxWidth) {
                break;
            }

            $size--;
        }

        return $size;
    }
}
