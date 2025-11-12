<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Contracts\View\View;

class VoucherController extends Controller
{
    /**
     * View the resource' share url.
     */
    public function show(string $code): View
    {
        $voucher = Voucher::where('code_unique', $code)->firstOrFail();

        return view('vouchers.show-tshirt', compact('voucher'));
    }
}
