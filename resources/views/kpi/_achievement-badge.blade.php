@php $p = (float) $value; $v = $p >= 100 ? 'success' : ($p >= 75 ? 'warning' : 'danger'); @endphp
<x-ui.badge :variant="$v">{{ rtrim(rtrim(number_format($p, 1, '.', ''), '0'), '.') }}%</x-ui.badge>
