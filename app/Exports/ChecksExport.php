<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ChecksExport implements FromView
{
    private $checks;

    public function __construct($checks)
    {
        $this->checks = $checks;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        return view('exports.checks', [
            'checks' => $this->checks
        ]);
    }
}
