<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SendemailController;
use Illuminate\Http\Request;

use App\Models\Department;
use App\Models\Letter;
use App\Models\Notifikasi;
use App\Models\Sender;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class LetterController extends Controller
{

    public function index()
    {
        //
    }

    public function create()
    {
        $departments = Department::all();
        $senders = Sender::all();

        return view('pages.admin.letter.create', [
            'departments' => $departments,
            'senders' => $senders,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'letter_no' => 'required',
            'letter_date' => 'required',
            'date_received' => 'required',
            'regarding' => 'required',
            'department_id' => 'required',
            'sender_id' => 'required',
            'letter_file' => 'required|mimes:pdf|file',
            'letter_type' => 'required',
        ]);

        if ($request->file('letter_file')) {
            $validatedData['letter_file'] = $request->file('letter_file')->store('public/assets/letter-file');
        }

        if ($validatedData['letter_type'] == 'Surat Masuk') {
            $redirect = 'surat-masuk';
        } else {
            $redirect = 'surat-keluar';
        }

        $validatedData['status'] = 'diproses';

        if (Letter::create($validatedData)) {
            SendemailController::Send($cekDataUser->nama_lengkap, "Selamat! Pengajuan surat Anda telah sukses diajukan. Kami akan melakukan Validasi Operator.<br><br> Mohon tunggu pemberitahuan selanjutnya yaa", "Permohonan Perizinan Berhasil Diajukan", $cekDataUser->email);

            $notifikasi = new Notifikasi;
            $notifikasi->role = "Murid";
            $notifikasi->judul = "Materi baru dengan judul '" . $request->judul  . "' telah diunggah, yuk pelajari !!!";
            $notifikasi->is_seen = "N";
            $notifikasi->created_at = Carbon::now();
            $notifikasi->updated_at = Carbon::now();

            $notifikasi->save();
            return redirect()
                ->route($redirect)
                ->with('success', 'Sukses! 1 Data Berhasil Disimpan');
        }
        // ;

    }

    public function incoming_mail()
    {
        if (request()->ajax()) {
            $query = Letter::with(['department', 'sender'])->where('letter_type', 'Surat Masuk')->where('status', 'diproses')->latest()->get();


            return Datatables::of($query)
                ->addColumn('action', function ($item) {
                    $rolePrefix = [
                        'admin' => 'admin',
                        'guru' => 'guru',
                        'staff administrasi' => 'staff',
                        'kepala sekolah' => 'kepala-sekolah'
                    ];

                    $prefix = $rolePrefix[Session('user')['role']] ?? 'default'; // default jika role tidak dikenali
                    // dd($prefix);

                    return '
                       

<a class="btn btn-success btn-xs" href="' . url($prefix . '/letter/surat', $item->id) . ' ">
    <i class="fa fa-search-plus"></i> &nbsp; Detail
</a>

                        <a class="btn btn-primary btn-xs" href="' . url($prefix . '/letter/' . $item->id . '/edit') .  '">
                            <i class="fas fa-edit"></i> &nbsp; Ubah
                        </a>
                        <form action="' . route('letter.destroy', $item->id) . '" method="POST" onsubmit="return confirm(' . "'Anda akan menghapus item ini dari situs anda?'" . ')">
                            ' . method_field('delete') . csrf_field() . '
                            <button class="btn btn-danger btn-xs">
                                <i class="far fa-trash-alt"></i> &nbsp; Hapus
                            </button>
                        </form>
                    ';
                })
                ->editColumn('post_status', function ($item) {
                    return $item->post_status == 'Published' ? '<div class="badge bg-green-soft text-green">' . $item->post_status . '</div>' : '<div class="badge bg-gray-200 text-dark">' . $item->post_status . '</div>';
                })
                ->addIndexColumn()
                ->removeColumn('id')
                ->rawColumns(['action', 'post_status'])
                ->make();
        }

        return view('pages.admin.letter.incoming');
    }

    public function outgoing_mail()
    {
        if (request()->ajax()) {
            $query = Letter::with(['department', 'sender'])->where('letter_type', 'Surat Keluar')->latest()->get();

            return Datatables::of($query)
                ->addColumn('action', function ($item) {
                    $rolePrefix = [
                        'admin' => 'admin',
                        'guru' => 'guru',
                        'staff' => 'staff',
                        'kepala sekolah' => 'kepala-sekolah'
                    ];

                    $prefix = $rolePrefix[Session('user')['role']] ?? 'default'; // default jika role tidak dikenali

                    return '
                   

<a class="btn btn-success btn-xs" href="' . url($prefix . '/letter/surat', $item->id) . ' ">
                            <i class="fa fa-search-plus"></i> &nbsp; Detail
                        </a>
                        <a class="btn btn-primary btn-xs" href="' . route('letter.edit', $item->id) . '">
                            <i class="fas fa-edit"></i> &nbsp; Ubah
                        </a>
                        <form action="' . route('letter.destroy', $item->id) . '" method="POST" onsubmit="return confirm(' . "'Anda akan menghapus item ini dari situs anda?'" . ')">
                            ' . method_field('delete') . csrf_field() . '
                            <button class="btn btn-danger btn-xs">
                                <i class="far fa-trash-alt"></i> &nbsp; Hapus
                            </button>
                        </form>
                    ';
                })
                ->editColumn('post_status', function ($item) {
                    return $item->post_status == 'Published' ? '<div class="badge bg-green-soft text-green">' . $item->post_status . '</div>' : '<div class="badge bg-gray-200 text-dark">' . $item->post_status . '</div>';
                })
                ->addIndexColumn()
                ->removeColumn('id')
                ->rawColumns(['action', 'post_status'])
                ->make();
        }

        return view('pages.admin.letter.outgoing');
    }

    public function arsip()
    {
        if (request()->ajax()) {
            $query = Letter::with(['department', 'sender'])->where('status', '!=', 'diproses')->latest()->get();

            return Datatables::of($query)
                ->addColumn('action', function ($item) {
                    $rolePrefix = [
                        'admin' => 'admin',
                        'guru' => 'guru',
                        'staff' => 'staff',
                        'kepala sekolah' => 'kepala-sekolah'
                    ];

                    $prefix = $rolePrefix[Session('user')['role']] ?? 'default'; // default jika role tidak dikenali

                    return '
                       

<a class="btn btn-success btn-xs" href="' . url($prefix . '/letter/surat', $item->id) . ' ">
                            <i class="fa fa-search-plus"></i> &nbsp; Detail
                        </a>
                        <a class="btn btn-primary btn-xs" href="' . route('letter.edit', $item->id) . '">
                            <i class="fas fa-edit"></i> &nbsp; Ubah
                        </a>
                        <form action="' . route('letter.destroy', $item->id) . '" method="POST" onsubmit="return confirm(' . "'Anda akan menghapus item ini dari situs anda?'" . ')">
                            ' . method_field('delete') . csrf_field() . '
                            <button class="btn btn-danger btn-xs">
                                <i class="far fa-trash-alt"></i> &nbsp; Hapus
                            </button>
                        </form>
                    ';
                })
                ->editColumn('post_status', function ($item) {
                    return $item->post_status == 'Published' ? '<div class="badge bg-green-soft text-green">' . $item->post_status . '</div>' : '<div class="badge bg-gray-200 text-dark">' . $item->post_status . '</div>';
                })
                ->addIndexColumn()
                ->removeColumn('id')
                ->rawColumns(['action', 'post_status'])
                ->make();
        }

        return view('pages.admin.letter.arsip');
    }

    public function show($id)
    {
        // dd($id);
        $item = Letter::with(['department', 'sender'])->findOrFail($id);

        return view('pages.admin.letter.show', [
            'item' => $item,
        ]);
    }

    public function edit($id)
    {
        $item = Letter::findOrFail($id);

        $departments = Department::all();
        $senders = Sender::all();

        return view('pages.admin.letter.edit', [
            'departments' => $departments,
            'senders' => $senders,
            'item' => $item,
        ]);
    }

    public function download_letter($id)
    {
        $item = Letter::findOrFail($id);
        // dd($item->letter_file);
        // dd(Storage::download('storage/' . $item->letter_file));

        return Storage::download($item->letter_file);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'letter_no' => 'required',
            'letter_date' => 'required',
            'date_received' => 'required',
            'regarding' => 'required',
            'department_id' => 'required',
            'sender_id' => 'required',
            'letter_file' => 'mimes:pdf|file',
            'letter_type' => 'required',
        ]);

        $item = Letter::findOrFail($id);

        if ($request->file('letter_file')) {
            $validatedData['letter_file'] = $request->file('letter_file')->store('public/assets/letter-file');
        }

        if ($validatedData['letter_type'] == 'Surat Masuk') {
            $redirect = 'surat-masuk';
        } else {
            $redirect = 'surat-keluar';
        }

        $item->update($validatedData);

        return redirect()
            ->route($redirect)
            ->with('success', 'Sukses! 1 Data Berhasil Diubah');
    }

    public function approve(Request $request, $id)
    {



        $item = Letter::findOrFail($id);

        // dd($item);
        $item->update(['status' => 'diterima']);


        return redirect()->back()
            ->with('success', 'Sukses! 1 Data Berhasil Diubah');
    }

    public function reject(Request $request, $id)
    {



        $item = Letter::findOrFail($id);

        // dd($item);
        $item->update(['status' => 'ditolak']);


        return redirect()->back()
            ->with('success', 'Sukses! 1 Data Berhasil Diubah');
    }
    public function destroy($id)
    {
        $item = Letter::findorFail($id);

        if ($item->letter_type == 'Surat Masuk') {
            $redirect = 'surat-masuk';
        } else {
            $redirect = 'surat-keluar';
        }

        Storage::delete($item->letter_file);

        $item->delete();

        return redirect()
            ->route($redirect)
            ->with('success', 'Sukses! 1 Data Berhasil Dihapus');
    }
}
