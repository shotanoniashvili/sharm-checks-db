<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CheckExport implements FromView
{
    private $check;

    public function __construct($check)
    {
        $this->check = $check;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        return view('exports.check-items', [
            'check' => $this->check
        ]);
    }
}
