<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;

class NotifikasiController extends Controller
{

    public function index()
    {
        $role = session('user')['role'];
        $notifikasi = Notifikasi::where('role', $role)
            ->orderByDesc('created_at')
            ->get();

        return view('pages.admin.notifikasi.index', compact('notifikasi'));
    }

    public function markAllRead()
    {
        $role = session('user')['role'];
        Notifikasi::where('role', $role)->update(['is_seen' => 'Y']);
        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
    public function baca($id)
    {
        $notif = Notifikasi::findOrFail($id);
        $notif->is_seen = 'Y';
        $notif->save();


        $prefix = session('user')['role']; // diasumsikan role = 'admin' atau lainnya
        if ($notif->surat->tipe_surat === 'masuk') {
            return redirect()->to($prefix . '/surat-masuk/' . $notif->surat_id);
        } else {
            return redirect()->to($prefix . '/surat-keluar/' . $notif->surat_id);
        }
    }
}
