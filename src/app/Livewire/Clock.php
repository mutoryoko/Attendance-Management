<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class Clock extends Component
{
    public function render()
    {
        $now = Carbon::now()->locale('ja');

        return view('livewire.clock', [
            'date' => $now->translatedFormat('Y年m月d日 (D)'),
            'time' => $now->format('H:i'),
        ]);
    }
}
