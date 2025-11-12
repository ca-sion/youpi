<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Contracts\View\View;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;

class VoucherController extends Controller
{
    /**
     * View the resource' share url.
     */
    public function showTshirt(string $code): View
    {
        $voucher = Voucher::where('code_unique', $code)->firstOrFail();

        SEOMeta::setTitle('Bon T-shirt : '.$voucher->athlete_name);
        OpenGraph::setTitle('Bon T-shirt : '.$voucher->athlete_name);

        return view('vouchers.show-tshirt', compact('voucher'));
    }
}
