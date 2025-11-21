<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function scanShipment(Request $request)
    {
        return view('user.scanShipment');
    }

    public function scanDocument(Request $request)
    {
        return view('user.scanDocument');
    }

    public function scanCodeProduct(Request $request)
    {
        return view('user.scanCodeProduct');
    }
}
