<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PrintController;
use App\Http\Controllers\Admin\LetterController;
use App\Http\Controllers\Admin\SenderController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FieldDefinitionController;
use App\Http\Controllers\Admin\JenisSuratController;
use App\Http\Controllers\Admin\SuratController;
use App\Http\Controllers\Admin\SuratMasukController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/storage-link', function () {
    $targetFolder = base_path() . '/storage/app/public';
    $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';
    symlink($targetFolder, $linkFolder);
});

Route::get('/clear-cache', function () {
    Artisan::call('route:cache');
});

Route::get('/', [LoginController::class, 'index']);

// Authentication
Route::get('/login', [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login-action', [LoginController::class, 'login_action']);
Route::post('/logout', [LoginController::class, 'logout']);

//Admin
Route::prefix('admin')
    ->middleware('authAdmin')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin-dashboard');
        Route::resource('/department', DepartmentController::class);
        Route::resource('/jenis-surat', JenisSuratController::class);
        // Route untuk Surat (CRUD surat)
        // Surat Masuk
        Route::prefix('surat-masuk')->name('surat-masuk.')->group(function () {
            Route::get('/', [SuratMasukController::class, 'index'])->name('index');
            Route::get('/create', [SuratMasukController::class, 'create'])->name('create');
            Route::post('/', [SuratMasukController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [SuratMasukController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SuratMasukController::class, 'update'])->name('update');
            Route::delete('/{id}', [SuratMasukController::class, 'destroy'])->name('destroy');
            Route::get('/{id}', [SuratMasukController::class, 'show'])->name('show'); // <- Tambahkan ini
            Route::get('/{id}/download', [SuratMasukController::class, 'download'])->name('download');
        });

        // Surat Keluar
        Route::prefix('admin/surat-keluar')->name('surat-keluar.')->group(function () {
            Route::get('/', [SuratKeluarController::class, 'index'])->name('index');
            Route::get('/create', [SuratKeluarController::class, 'create'])->name('create');
            Route::post('/', [SuratKeluarController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [SuratKeluarController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SuratKeluarController::class, 'update'])->name('update');
            Route::delete('/{id}', [SuratKeluarController::class, 'destroy'])->name('destroy');
        });

        Route::get('field-definitions/{jenisSuratId}', [FieldDefinitionController::class, 'byJenisSurat']);


        // Route untuk Field Definitions (kelola jenis field form surat)
        // Route::resource('field-definitions', FieldDefinitionController::class)
        //     ->except(['show']);

        // // Route untuk Field Values (opsional, biasanya tidak full CRUD, 
        // // tapi kalau perlu akses khusus bisa dibuat)
        // Route::resource('field-values', FieldValueController::class)
        //     ->only(['index', 'show', 'update', 'destroy']);


        Route::resource('/sender', SenderController::class);
        Route::resource('/letter', LetterController::class, [
            'except' => ['show']
        ]);
        // Route::get('letter/surat-masuk', [LetterController::class, 'incoming_mail'])->name('surat-masuk');
        // Route::get('letter/surat-keluar', [LetterController::class, 'outgoing_mail'])->name('surat-keluar');

        // Route::get('letter/arsip', [LetterController::class, 'arsip'])->name('arsip');


        // Route::get('letter/surat/{id}', [LetterController::class, 'show']);
        // Route::get('letter/download/{id}', [LetterController::class, 'download_letter'])->name('download-surat-admin');
        // Route::get('letter/surat/{id}/approve', [LetterController::class, 'approve'])->name('approve');
        // Route::get('letter/surat/{id}/reject', [LetterController::class, 'reject'])->name('reject');

        //print
        Route::get('print/surat-masuk', [PrintController::class, 'index'])->name('print-surat-masuk');
        Route::get('print/surat-keluar', [PrintController::class, 'outgoing'])->name('print-surat-keluar');

        Route::resource('user', UserController::class);
        Route::resource('setting', SettingController::class, [
            'except' => ['show']
        ]);
        Route::get('setting/password', [SettingController::class, 'change_password'])->name('change-password');
        Route::post('setting/upload-profile', [SettingController::class, 'upload_profile'])->name('profile-upload');
        Route::post('change-password', [SettingController::class, 'update_password'])->name('update.password');
    });

//Kepala Sekolah
Route::prefix('kepala-desa')
    ->middleware('authKepsek')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin-dashboard');
        // Route::resource('/department', DepartmentController::class);
        Route::resource('/sender', SenderController::class);
        Route::resource('/letter', LetterController::class, [
            'except' => ['show']
        ]);

        Route::prefix('surat-masuk')->name('surat-masuk.')->group(function () {
            Route::get('/', [SuratMasukController::class, 'index']);
            // Route::get('/create', [SuratMasukController::class, 'create'])->name('create');
            // Route::post('/', [SuratMasukController::class, 'store'])->name('store');
            // Route::get('/{id}/edit', [SuratMasukController::class, 'edit'])->name('edit');
            // Route::put('/{id}', [SuratMasukController::class, 'update'])->name('update');
            // Route::delete('/{id}', [SuratMasukController::class, 'destroy'])->name('destroy');
            Route::get('/{id}', [SuratMasukController::class, 'show'])->name('show'); // <- Tambahkan ini
            Route::get('/{id}/download', [SuratMasukController::class, 'download'])->name('download');
            Route::get('/{id}/approve', [SuratMasukController::class, 'approve'])->name('approve');
            Route::get('/{id}/reject', [SuratMasukController::class, 'reject'])->name('reject');
        });

        Route::get('letter/surat-masuk', [LetterController::class, 'incoming_mail']);
        Route::get('letter/surat-keluar', [LetterController::class, 'outgoing_mail']);

        Route::get('letter/surat/{id}', [LetterController::class, 'show']);
        Route::get('letter/download/{id}', [LetterController::class, 'download_letter'])->name('download-surat-kepsek');

        //print
        Route::get('print/surat-masuk', [PrintController::class, 'index']);
        Route::get('print/surat-keluar', [PrintController::class, 'outgoing'])->name('print-surat-keluar');

        Route::resource('user', UserController::class);
        Route::resource('setting', SettingController::class, [
            'except' => ['show']
        ]);
        Route::get('setting/password', [SettingController::class, 'change_password'])->name('change-password');
        Route::post('setting/upload-profile', [SettingController::class, 'upload_profile'])->name('profile-upload');
        Route::post('change-password', [SettingController::class, 'update_password'])->name('update.password');
    });


//Guru
Route::prefix('guru')
    ->middleware('authGuru')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin-dashboard');
        // Route::resource('/department', DepartmentController::class);
        Route::resource('/sender', SenderController::class);
        Route::resource('/letter', LetterController::class, [
            'except' => ['show']
        ]);
        Route::get('letter/surat-masuk', [LetterController::class, 'incoming_mail']);
        Route::get('letter/surat-keluar', [LetterController::class, 'outgoing_mail']);

        Route::get('letter/surat/{id}', [LetterController::class, 'show']);
        Route::get('letter/download/{id}', [LetterController::class, 'download_letter'])->name('download-surat-guru');

        //print
        Route::get('print/surat-masuk', [PrintController::class, 'index']);
        Route::get('print/surat-keluar', [PrintController::class, 'outgoing'])->name('print-surat-keluar');

        Route::resource('user', UserController::class);
        Route::resource('setting', SettingController::class, [
            'except' => ['show']
        ]);
        Route::get('setting/password', [SettingController::class, 'change_password'])->name('change-password');
        Route::post('setting/upload-profile', [SettingController::class, 'upload_profile'])->name('profile-upload');
        Route::post('change-password', [SettingController::class, 'update_password'])->name('update.password');
    });


//Guru
Route::prefix('staff')
    ->middleware('authStaff')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin-dashboard');
        // Route::resource('/department', DepartmentController::class);
        Route::resource('/sender', SenderController::class);
        Route::resource('/letter', LetterController::class, [
            'except' => ['show']
        ]);
        Route::get('letter/surat-masuk', [LetterController::class, 'incoming_mail']);
        Route::get('letter/surat-keluar', [LetterController::class, 'outgoing_mail']);

        Route::get('letter/surat/{id}', [LetterController::class, 'show']);
        Route::get('letter/download/{id}', [LetterController::class, 'download_letter'])->name('download-surat-staff');

        //print
        Route::get('print/surat-masuk', [PrintController::class, 'index']);
        Route::get('print/surat-keluar', [PrintController::class, 'outgoing'])->name('print-surat-keluar');

        Route::resource('user', UserController::class);
        Route::resource('setting', SettingController::class, [
            'except' => ['show']
        ]);
        Route::get('setting/password', [SettingController::class, 'change_password'])->name('change-password');
        Route::post('setting/upload-profile', [SettingController::class, 'upload_profile'])->name('profile-upload');
        Route::post('change-password', [SettingController::class, 'update_password'])->name('update.password');
    });
