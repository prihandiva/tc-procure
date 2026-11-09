<?php

namespace App\Livewire\Help;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    #[Url]
    public string $tab = 'flow'; // 'flow', 'tc', 'vendor', 'faq'

    public string $search = '';

    public function mount(): void
    {
        if (request()->missing('tab') && Auth::user()?->isVendor()) {
            $this->tab = 'vendor';
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.help.index', [
            'currentUser' => $user,
            'isVendor' => $user?->isVendor(),
            'isTc' => $user?->isSuperAdmin() || $user?->isSpv(),
        ]);
    }
}

