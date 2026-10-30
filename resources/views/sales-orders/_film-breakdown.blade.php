@php /** @var array $breakdown  @var string $variant  web|print */ $variant = $variant ?? 'web'; @endphp
@if($variant === 'print')
    @foreach($breakdown as $pack)
        <div style="margin-top:3pt">
            @if($pack['vehicle'] || $pack['package'])
                <div style="font-weight:bold">{{ trim(($pack['vehicle'] ? $pack['vehicle'] . ' — ' : '') . $pack['package'], ' —') }}</div>
            @endif
            <table style="width:100%;border-collapse:collapse;font-size:9pt">
                @foreach($pack['parts'] as $part)
                    <tr>
                        <td style="padding:1pt 4pt 1pt 0;width:38%;color:#555">{{ $part['label'] }}</td>
                        <td style="padding:1pt 0">{{ $part['film'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach
@else
    @foreach($breakdown as $pack)
        <div class="border rounded-3 p-2 mb-2">
            @if($pack['vehicle'] || $pack['package'])
                <div class="small fw-semibold mb-1">
                    <iconify-icon icon="mdi:car-outline" class="me-1" aria-hidden="true"></iconify-icon>{{ trim(($pack['vehicle'] ? $pack['vehicle'] . ' — ' : '') . $pack['package'], ' —') }}
                </div>
            @endif
            <table class="table table-sm mb-0 align-middle" style="font-size:13px">
                <tbody>
                @foreach($pack['parts'] as $part)
                    <tr>
                        <td class="text-muted" style="width:38%">{{ $part['label'] }}</td>
                        <td class="{{ $part['film'] ? 'fw-semibold' : 'text-muted' }}">{{ $part['film'] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
@endif
