<?php

use App\Services\NetworkStatus;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function isOnline(): bool
    {
        return app(NetworkStatus::class)->isOnline();
    }

    #[Computed]
    public function connectionType(): string
    {
        return app(NetworkStatus::class)->getConnectionType();
    }

    #[Computed]
    public function connectionLabel(): string
    {
        return match ($this->connectionType) {
            'wifi' => 'Wi-Fi',
            'cellular' => 'Cellular',
            'ethernet' => 'Ethernet',
            default => 'Online',
        };
    }
};
?>

<div wire:poll.30s class="network-status-indicator">
    @if ($this->isOnline)
        <span class="inline-flex items-center gap-1.5 rounded-full bg-mint-100 px-3 py-1 text-xs font-semibold text-mint-700">
            <span class="h-2 w-2 rounded-full bg-mint-500"></span>
            {{ $this->connectionLabel }}
        </span>
    @else
        <span class="inline-flex items-center gap-1.5 rounded-full bg-candy-100 px-3 py-1 text-xs font-semibold text-candy-700">
            <span class="h-2 w-2 rounded-full bg-candy-500"></span>
            Offline
        </span>
    @endif
</div>
