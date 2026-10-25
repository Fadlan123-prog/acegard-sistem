@props(['birthdays'])

@php $hasToday = $birthdays->contains(fn ($e) => $e->days_until_birthday === 0); @endphp

@if($birthdays->isNotEmpty())
<x-ui.card class="mb-4 bday-widget-card" id="bdayWidgetCard">
    @slot('header')
        <div class="ui-card-title">
            <iconify-icon icon="mdi:cake-variant-outline" class="me-1 bday-cake-icon" style="color:#ec4899;"></iconify-icon>
            Ulang Tahun Karyawan
        </div>
    @endslot

    <div class="d-flex flex-column gap-2">
        @foreach($birthdays as $employee)
            <div class="d-flex align-items-center justify-content-between py-1 px-2 rounded {{ !$loop->last ? 'border-bottom' : '' }} {{ $employee->days_until_birthday === 0 ? 'bday-row-today' : '' }}">
                <div>
                    <span class="fw-semibold">{{ $employee->name }}</span>
                    @if($employee->job_position)
                        <span class="text-muted small"> — {{ $employee->job_position }}</span>
                    @endif
                </div>
                @if($employee->days_until_birthday === 0)
                    <x-ui.badge variant="success" class="bday-badge-today">Hari ini 🎉</x-ui.badge>
                @elseif($employee->days_until_birthday === 1)
                    <x-ui.badge variant="warning">Besok</x-ui.badge>
                @else
                    <x-ui.badge variant="neutral">{{ $employee->next_birthday_date->translatedFormat('d M') }}</x-ui.badge>
                @endif
            </div>
        @endforeach
    </div>
</x-ui.card>

<style>
    .bday-widget-card { position: relative; overflow: hidden; }

    .bday-row-today {
        background: linear-gradient(90deg, rgba(236,72,153,.08), rgba(236,72,153,0));
        border-left: 3px solid #ec4899;
    }

    .bday-confetti-piece {
        position: absolute;
        top: -12px;
        width: 8px;
        height: 14px;
        opacity: .9;
        pointer-events: none;
        z-index: 5;
        border-radius: 2px;
    }

    @media (prefers-reduced-motion: no-preference) {
        .bday-cake-icon { display: inline-block; animation: bdayWiggle 1.8s ease-in-out infinite; transform-origin: 50% 85%; }
        .bday-badge-today { display: inline-block; animation: bdayPulse 1.4s ease-in-out infinite; }

        @keyframes bdayWiggle {
            0%, 100% { transform: rotate(0deg); }
            20%      { transform: rotate(-12deg); }
            40%      { transform: rotate(10deg); }
            60%      { transform: rotate(-6deg); }
            80%      { transform: rotate(4deg); }
        }
        @keyframes bdayPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 rgba(34,197,94,.4); }
            50%      { transform: scale(1.08); box-shadow: 0 0 8px rgba(34,197,94,.5); }
        }
        @keyframes bdayConfettiFall {
            0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(160px) rotate(540deg); opacity: 0; }
        }
    }
</style>

@if($hasToday)
<script>
(function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var card = document.getElementById('bdayWidgetCard');
    if (!card || card.dataset.confettiDone) return;
    card.dataset.confettiDone = '1';

    var colors = ['#ec4899', '#f59e0b', '#22c55e', '#3b82f6', '#a855f7'];
    var pieceCount = 24;

    for (var i = 0; i < pieceCount; i++) {
        (function () {
            var piece = document.createElement('span');
            piece.className = 'bday-confetti-piece';
            piece.style.left = Math.random() * 100 + '%';
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.animation = 'bdayConfettiFall ' + (1.4 + Math.random() * 1.1) + 's ease-in forwards';
            piece.style.animationDelay = (Math.random() * 0.6) + 's';
            card.appendChild(piece);
            setTimeout(function () { piece.remove(); }, 3000);
        })();
    }
})();
</script>
@endif
@endif
