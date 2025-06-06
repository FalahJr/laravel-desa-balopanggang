<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Letter;
use App\Models\Surat;

class DashboardController extends Controller
{
    public function index()
    {
        $masuk = Surat::where('tipe_surat', 'Surat Masuk')->get()->count();
        $keluar = Surat::where('tipe_surat', 'Surat Keluar')->get()->count();

        return view('pages.admin.dashboard', [
            'masuk' => $masuk,
            'keluar' => $keluar
        ]);
    }
}
