<?php

namespace App\Http\Controllers;

use App\Models\BagianBidang;
use App\Models\SubBagianSeksi;
use App\Models\User;
use App\Models\PenugasanPejabat;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Superadmin: koreksi data akun pegawai (nama, NIP, email, password, unit kerja).
 * ROLE sengaja tidak bisa diubah dari sini.
 */
class SuperadminPegawaiController extends Controller
{
        public function index(Request $request)
    {
        $roles = Role::where('name', '!=', 'Superadmin')->orderBy('name')->pluck('name');

        $pegawais = User::bukanSuperadmin()
            ->with(['roles', 'bagianBidang', 'subBagianSeksi'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $cari = $request->search;
                $q->where(function ($w) use ($cari) {
                    $w->where('name', 'like', "%{$cari}%")
                      ->orWhere('nip', 'like', "%{$cari}%")
                      ->orWhere('email', 'like', "%{$cari}%");
                });
            })
            ->when($request->filled('role') && $roles->contains($request->role), fn ($q) => $q->role($request->role))
            ->when($request->filled('bagian'), fn ($q) => $q->where('bagian_bidang_id', $request->bagian))
            ->when($request->filled('seksi'), fn ($q) => $q->where('sub_bagian_seksi_id', $request->seksi))
            ->orderBy('name')
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString();

        $bagians  = BagianBidang::with('subBagianSeksis')->orderBy('nama')->get();
        $menjabat = PenugasanPejabat::berlaku()->get()->keyBy('pengganti_id'); // siapa yang sedang Plh/Plt

        return view('superadmin.pegawai.index', compact('pegawais', 'bagians', 'roles', 'menjabat'));
    }

        /** Password awal yang sama dengan import Excel Admin; wajib diganti lewat pop-up login pertama. */
    private const PASSWORD_AWAL = 'password123';

    public function create()
    {
        $bagians = BagianBidang::with('subBagianSeksis')->orderBy('nama')->get();

        return view('superadmin.pegawai.create', compact('bagians'));
    }

    public function store(Request $request)
    {
        // Digit polos, sama dengan konvensi import Excel & form login.
        $request->merge(['nip' => User::bersihkanNip($request->input('nip'))]);

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            // 18 digit: syarat agar email sementara dikenali sistem (User::emailSementara)
            'nip'                 => ['required', 'regex:/^\d{18}$/', 'unique:users,nip'],
            'bagian_bidang_id'    => ['required', 'exists:bagian_bidangs,id'],
            'sub_bagian_seksi_id' => [
                'required',
                Rule::exists('sub_bagian_seksis', 'id')->where('bagian_bidang_id', $request->input('bagian_bidang_id')),
            ],
        ], [
            'name.required'                => 'Nama wajib diisi.',
            'nip.regex'                    => 'NIP harus terdiri dari tepat 18 digit angka.',
            'nip.unique'                   => 'NIP ini sudah terdaftar.',
            'bagian_bidang_id.required'    => 'Pilih Bagian/Bidang.',
            'sub_bagian_seksi_id.required' => 'Pilih Sub-Bagian/Seksi.',
            'sub_bagian_seksi_id.exists'   => 'Sub-Bagian/Seksi tidak sesuai dengan Bagian/Bidang yang dipilih.',
        ]);

        $emailSementara = $data['nip'] . '@otban3.com';

        if (User::where('email', $emailSementara)->exists()) {
            return back()->withInput()->withErrors([
                'nip' => 'Email sementara untuk NIP ini sudah dipakai akun lain.',
            ]);
        }

        $pegawai = DB::transaction(function () use ($data, $emailSementara) {
            $user = User::create([
                'name'                   => $data['name'],
                'nip'                    => $data['nip'],
                'email'                  => $emailSementara,
                'password'               => self::PASSWORD_AWAL, // di-hash otomatis oleh cast 'hashed'
                'bagian_bidang_id'       => $data['bagian_bidang_id'],
                'sub_bagian_seksi_id'    => $data['sub_bagian_seksi_id'],
                'level_jabatan'          => 'Pegawai',
                'jatah_cuti'             => 12,
                'status_kepegawaian'     => User::statusKepegawaianDariNip($data['nip']),
                'wajib_ganti_kredensial' => true,
            ]);
            $user->assignRole('Pegawai');

            return $user;
        });

        Log::info('Superadmin menambah pegawai baru', [
            'superadmin_id' => $request->user()->id,
            'pegawai_id'    => $pegawai->id,
        ]);

        return redirect()->route('superadmin.pegawai.index')->with(
            'success',
            "Pegawai {$pegawai->name} berhasil ditambahkan. Login dengan NIP {$pegawai->nip} dan kata sandi " . self::PASSWORD_AWAL
            . '. Saat login pertama, pegawai diminta mengisi email aktif dan mengganti kata sandi.'
        );
    }

    public function edit($id)
    {
        $pegawai  = User::bukanSuperadmin()->with('roles')->findOrFail($id);
        $bagians  = BagianBidang::with('subBagianSeksis')->orderBy('nama')->get();
        $unitTerkunci = $this->unitTerkunci($pegawai);

        return view('superadmin.pegawai.edit', compact('pegawai', 'bagians', 'unitTerkunci'));
    }

    public function update(Request $request, $id)
    {
        $pegawai = User::bukanSuperadmin()->findOrFail($id);

        // Hapus spasi pada NIP agar konsisten dengan data lain (dan mudah dicocokkan).
        $request->merge(['nip' => $request->filled('nip') ? preg_replace('/\s+/', '', $request->nip) : null]);

        $aturan = [
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pegawai->id)],
            'nip'                => ['nullable', 'string', 'max:30', Rule::unique('users', 'nip')->ignore($pegawai->id)],
            'jenis_kelamin'      => ['nullable', Rule::in(['L', 'P'])],
            'status_kepegawaian' => ['nullable', Rule::in(['PNS', 'PPPK'])],
        ];

        $unitTerkunci = $this->unitTerkunci($pegawai);

        if (! $unitTerkunci) {
            $aturan['bagian_bidang_id']    = ['nullable', 'exists:bagian_bidangs,id'];
            $aturan['sub_bagian_seksi_id'] = [
                'nullable',
                Rule::exists('sub_bagian_seksis', 'id')->where('bagian_bidang_id', $request->input('bagian_bidang_id')),
            ];
        }

        $data = $request->validate($aturan);

        // Jangan pernah menyentuh role/level_jabatan, dan kunci unit untuk akun kepala.
        $pegawai->fill($data);

        $berubah = array_keys($pegawai->getDirty());
        $pegawai->save();

        Log::info('Superadmin mengubah data pegawai', [
            'superadmin_id' => $request->user()->id,
            'pegawai_id'    => $pegawai->id,
            'kolom'         => $berubah,
        ]);

        return redirect()->route('superadmin.pegawai.edit', $pegawai->id)
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function resetPassword(Request $request, $id)
    {
        $pegawai = User::bukanSuperadmin()->findOrFail($id);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Cast 'hashed' di model User otomatis meng-hash. remember_token diganti
        // agar sesi "ingat saya" lama tidak lagi berlaku.
        $pegawai->forceFill([
            'password'       => $request->password,
            'remember_token' => Str::random(60),
        ])->save();

        Log::info('Superadmin mereset password pegawai', [
            'superadmin_id' => $request->user()->id,
            'pegawai_id'    => $pegawai->id,
        ]);

        return redirect()->route('superadmin.pegawai.edit', $pegawai->id)
            ->with('success', 'Password ' . $pegawai->name . ' berhasil direset. Sampaikan password baru kepada yang bersangkutan.');
    }

    /** Akun yang memegang role kepala: unit kerjanya menentukan meja approval, jadi dikunci. */
    private function unitTerkunci(User $pegawai): bool
    {
        return $pegawai->hasAnyRole(User::ROLE_KEPALA);
    }
}