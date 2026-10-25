@props([
    'title' => null,
    'headers' => [],
    'rows' => [], // each row: array of cell strings, or ['cells' => [...], 'class' => 'bg-...']
    'cta' => null, // ['label' => 'Hubungi Kami', 'href' => '#']
])

<div class="ace-spec-table">
    @if($title)
        <div class="px-4 py-3 border-bottom" style="border-color: var(--ace-border);">
            <span class="fw-semibold">{{ $title }}</span>
        </div>
    @endif

    <table class="table mb-0 align-middle">
        @if(count($headers))
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th class="px-4">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            @foreach($rows as $row)
                @php
                    $cells = is_array($row) && array_key_exists('cells', $row) ? $row['cells'] : $row;
                    $rowClass = is_array($row) && array_key_exists('class', $row) ? $row['class'] : null;
                @endphp
                <tr class="{{ $rowClass }}">
                    @foreach($cells as $cell)
                        <td class="px-4">{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($cta)
    <div class="text-center mt-3">
        <x-frontend.button
            variant="outline"
            :href="$cta['href'] ?? '#'"
            :class="!empty($cta['whatsapp']) ? 'whatsapp-btn' : ''"
            :target="!empty($cta['whatsapp']) ? '_blank' : null"
        >{{ $cta['label'] }}</x-frontend.button>
    </div>
@endif
