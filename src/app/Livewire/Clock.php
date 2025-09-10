<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class Clock extends Component
{
    public $date;
    public $time;

    public function mount()
    {
        $this->updateTime();
    }

    public function updateTime()
    {
        $now = Carbon::now()->locale('ja');

        $dateString = $now->format('Y年n月j日');
        $dayOfWeek = $now->shortDayName; //日本語の曜日取得
        $this->date = "{$dateString} ({$dayOfWeek})";

        $hour = $now->format('H');
        $minute = $now->format('i');
        $this->time = "<span>{$hour}</span><span class=\"colon\">:</span><span>{$minute}</span>";
    }

    public function render()
    {
        $this->updateTime();
        return view('livewire.clock');
    }
}
