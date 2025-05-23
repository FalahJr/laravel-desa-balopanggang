<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login', [
            'title' => 'Login'
        ]);
    }

    // public function authenticate(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'email' => 'required',
    //         'password' => 'required'
    //     ]);

    //     if (Auth::attempt($credentials)) {
    //         $request->session()->regenerate();
    //         return redirect()->intended('admin/dashboard');
    //     }

    //     return back()->with('loginError', 'Login Failed!');
    // }

    public function login_action(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $request->session()->flash('failed', 'Lengkapi isian form');
            return redirect('login');
        }

        // $karyawan = Karyawan::where([
        //     'email' => $request->email,
        //     'password' => $request->password,
        // ]);

        // $check = $this->checkUser($request, $karyawan, 'Karyawan');
        // if($check != null){
        //     return $check;
        // }

        // $staf_hr = Pejabat_struktural::where([
        //     'email' => $request->email,
        //     'password' => $request->password,
        // ]);

        // $check = $this->checkUser($request, $staf_hr, 'pejabat-struktural');
        // if($check != null){
        //     return $check;
        // }

        // $pegawai = User::where([
        //     'email' => $request->email,
        //     'password' => $request->password,
        // ])->with('divisi', 'jabatan')->first();
        // if ($pegawai) {
        //     // dd($pegawai);

        //     $check = $this->checkUser($request, $pegawai, $pegawai->jabatan->nama);
        //     // dd($check);

        //     if ($check != null) {
        //         return $check;
        //     }
        // }



        $admin = User::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            // Password valid, lanjutkan proses login

            // dd($admin);
            if ($admin) {

                $check = $this->checkUser($request, $admin, $admin->role);
                if ($check != null) {
                    return $check;
                }

                return redirect('/')->with('failed', 'Data User Tidak Ditemukan');
            } else {

                return redirect('/login')->with('failed', 'Data User Tidak Ditemukan');
            }
        } else {
            // Email atau password salah
            dd('Login gagal');
        }
    }

    private function checkUser($request, $user, $role)
    {
        // Session::flush();

        if ($user->exists()) {
            // dd($user);

            // $user = $user->first()->toArray();
            // unset($user['password']);
            $user['role'] = $role;
            $user['id'] = $user['id'] ?? $user['id_admin'];
            $user['nama'] = $user['name'];
            $user['profile'] = $user['profile'];
            $user['email'] = $user['email'];
            // $user['divisi'] = $user['divisi_id'] ?? null;
            Session(['user' => $user]);
            // dd($role);
            switch ($role) {
                case 'admin':
                    return redirect('admin/dashboard');
                    break;

                case 'guru':
                    return redirect('guru/dashboard');
                    break;

                case 'kepala desa':
                    return redirect('kepala-desa/dashboard');
                    break;

                case 'staff administrasi':
                    return redirect('staff/dashboard');
                    break;

                // case 'Karyawan':
                //     return redirect('/karyawan/home');
                //     break;
                // case 'Kepala Bagian':
                //     return redirect('/kepala-bagian/home');
                //     break;

                // case 'Kepala Sub Bagian':
                //     return redirect('/kepala-sub-bagian/home');
                //     break;

                // case 'Direktur':
                //     return redirect('/direktur/home');
                //     break;

                //     // case 'Manager':
                //     //     return redirect('/pejabat-struktural/home');
                //     //     break;

                // case 'admin':
                //     return redirect('/admin/home');
                //     break;

                default:
                    return redirect('/')->with('failed', 'Data User Tidak Ditemukan');
                    break;
            }
        } else {
            return null;
        }
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
