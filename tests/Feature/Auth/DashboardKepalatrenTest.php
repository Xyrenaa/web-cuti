<?php

use App\Models\BagianBidang;
use App\Models\JenisCuti;
use App\Models\PengajuanCuti;
use App\Models\SubBagianSeksi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->withoutVite();

    // Dashboard memakai MONTH() (fungsi MySQL). SQLite tidak punya, jadi dipasang pengganti
    // khusus untuk test; di MySQL (produksi) baris ini tidak dijalankan.
    if (DB::connection()->getDriverName() === 'sqlite') {
        DB::connection()->getPdo()->sqliteCreateFunction('MONTH', fn ($d) => (int) date('n', strtotime((string) $d)), 1);
    }

    // Observer pengajuan memberi notifikasi ke role tiap tahap, jadi semua role perlu ada.
    foreach (['Admin Kepegawaian', 'Kepala Kantor', 'Kepala TU', 'Kepala Bidang', 'Kepala Sub-Bagian', 'Kepala Seksi', 'pegawai'] as $role) {
        Role::firstOrCreate(['name' => $role]);
    }
});

function pegawaiUji(string $nama, ?BagianBidang $bagian = null, ?SubBagianSeksi $sub = null): User
{
    $u = User::factory()->create([
        'name' => $nama,
        'bagian_bidang_id' => $bagian?->id,
        'sub_bagian_seksi_id' => $sub?->id,
        'wajib_ganti_kredensial' => false,
    ]);
    $u->assignRole('pegawai');

    return $u;
}

function cutiUji(User $u, int $tahun, int $bulan, int $step, int $hari = 2): PengajuanCuti
{
    static $n = 0;
    $mulai = \Carbon\Carbon::create($tahun, $bulan, 10);

    return PengajuanCuti::create([
        'kode_pengajuan' => 'CT-TREN-' . (++$n),
        'user_id' => $u->id,
        'jenis_cuti_id' => JenisCuti::firstOrCreate(['nama_cuti' => 'Cuti Tahunan'])->id,
        'tanggal_mulai' => $mulai->toDateString(),
        'tanggal_selesai' => $mulai->copy()->addDays($hari - 1)->toDateString(),
        'durasi_hari' => $hari,
        'alasan' => 'Alasan rahasia pribadi',
        'status_pengajuan' => 'Uji',
        'approval_step' => $step,
    ]);
}

test('rincian pegawai per bulan cocok dengan angka batang dan hanya memuat cuti disetujui tahun ini', function () {
    $tahun = now()->year;
    $tu = BagianBidang::create(['nama' => 'Bagian Tata Usaha', 'is_tu' => true]);

    $budi = pegawaiUji('Budi Santoso', $tu);
    $sari = pegawaiUji('Sari Wulandari', $tu);
    $joko = pegawaiUji('Joko Pratama', $tu);
    $eko  = pegawaiUji('Eko Belum Disetujui', $tu);
    $ani  = pegawaiUji('Ani Tahun Lalu', $tu);

    cutiUji($budi, $tahun, 6, 8);          // Juni, disetujui
    cutiUji($sari, $tahun, 6, 8, 3);       // Juni, disetujui
    cutiUji($joko, $tahun, 7, 8);          // Juli, disetujui
    cutiUji($eko, $tahun, 6, 7);           // Juni TAPI belum disetujui -> tidak boleh muncul
    cutiUji($ani, $tahun - 1, 6, 8);       // tahun lalu -> tidak boleh muncul

    $kakan = User::factory()->create(['wajib_ganti_kredensial' => false]);
    $kakan->assignRole('Kepala Kantor');

    $res = $this->actingAs($kakan)->get('/dashboard')->assertOk();

    $tren = $res->viewData('trenBulanan');
    $detail = $res->viewData('detailTren');

    // jumlah baris rincian tiap bulan == angka batangnya (semua 12 bulan)
    foreach (range(0, 11) as $i) {
        expect(count($detail[$i]))->toBe($tren[$i]);
    }

    expect($tren[5])->toBe(2)->and($tren[6])->toBe(1);
    expect(collect($detail[5])->pluck('nama')->all())->toBe(['Budi Santoso', 'Sari Wulandari']);
    expect($detail[5][1])->toMatchArray(['hari' => 3, 'unit' => 'Bagian Tata Usaha', 'jenis' => 'Cuti Tahunan']);

    $res->assertSee('Budi Santoso')->assertSee('Sari Wulandari')->assertSee('Joko Pratama')
        ->assertDontSee('Eko Belum Disetujui')->assertDontSee('Ani Tahun Lalu')
        ->assertSee('data-trend-drop', false)
        ->assertDontSee('Alasan rahasia pribadi') // alasan cuti tidak ikut dimuat
        ->assertDontSee('data-trend-replay', false) // tombol putar ulang animasi sudah dihapus
        ->assertDontSee('Putar ulang animasi');
});

test('kepala seksi hanya melihat nama pegawai di seksinya sendiri', function () {
    $tahun = now()->year;
    $bagian = BagianBidang::create(['nama' => 'Bidang Operasi', 'is_tu' => false]);
    $seksiA = SubBagianSeksi::create(['bagian_bidang_id' => $bagian->id, 'nama' => 'Seksi A']);
    $seksiB = SubBagianSeksi::create(['bagian_bidang_id' => $bagian->id, 'nama' => 'Seksi B']);

    cutiUji(pegawaiUji('Pegawai Seksi A', $bagian, $seksiA), $tahun, 3, 8);
    cutiUji(pegawaiUji('Pegawai Seksi B', $bagian, $seksiB), $tahun, 3, 8);

    $kasi = User::factory()->create([
        'bagian_bidang_id' => $bagian->id, 'sub_bagian_seksi_id' => $seksiA->id,
        'wajib_ganti_kredensial' => false,
    ]);
    $kasi->assignRole('Kepala Seksi');

    $res = $this->actingAs($kasi)->get('/dashboard')->assertOk();

    expect(collect($res->viewData('detailTren')[2])->pluck('nama')->all())->toBe(['Pegawai Seksi A']);
    expect($res->viewData('trenBulanan')[2])->toBe(1);
    $res->assertSee('Pegawai Seksi A')->assertDontSee('Pegawai Seksi B');
});

test('bulan tanpa cuti tetap punya panel dengan pesan kosong', function () {
    $tahun = now()->year;
    cutiUji(pegawaiUji('Satu Orang'), $tahun, 1, 8);

    $kakan = User::factory()->create(['wajib_ganti_kredensial' => false]);
    $kakan->assignRole('Kepala Kantor');

    $this->actingAs($kakan)->get('/dashboard')->assertOk()
        ->assertSee('id="trend-panel-11"', false)
        ->assertSee('Tidak ada cuti yang disetujui pada bulan ini.');
});