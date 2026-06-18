<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Disiapkan untuk kueri PostGIS nanti

class WebGISController extends Controller
{
    // 1. Fungsi untuk merender halaman HTML (Blade)
    public function beranda() {
        return view('beranda');
    }

    public function peta() {
        return view('peta');
    }

    public function statistik() {
        return view('statistik');
    }

    public function laporan() {
        return view('laporan');
    }

    // 2. Fungsi untuk menerima data dari form HTML
    public function simpanLaporan(Request $request) {
        // Kita akan mengisi logika insert database di tahap selanjutnya
        return "Berhasil menangkap data dari form, siap dimasukkan ke PostGIS!";
    }

    // =========================================================
    // API 1: PETA SPASIAL (POSTGIS INTEGRATION)
    // =========================================================
    public function apiPeta() {
        try {
            /* Kueri spasial cerdas yang menghitung jumlah kejadian 
               dalam radius 200 meter langsung di mesin database.
            */
            $dataLaka = DB::select("
                SELECT 
                    a.latitude, 
                    a.longitude, 
                    a.keparahan, 
                    a.deskripsi, 
                    TO_CHAR(a.waktu_kejadian, 'DD-MM-YYYY HH24:MI') as waktu,
                    (
                        SELECT COUNT(*) 
                        FROM laporan_laka b 
                        WHERE ST_DWithin(a.geom::geography, b.geom::geography, 200)
                    ) as jumlah_sekitar
                FROM laporan_laka a 
                WHERE a.latitude IS NOT NULL AND a.longitude IS NOT NULL;
            ");

            return response()->json($dataLaka);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil data spasial: ' . $e->getMessage()], 500);
        }
    }


    // =========================================================
    // API 2: STATISTIK DASHBOARD (AGGREGATION)
    // =========================================================
    public function apiStatistik() {
        try {
            // Kita akan mengambil tahun-tahun yang unik dari database
            $tahunUnik = DB::table('laporan_laka')
                           ->select(DB::raw('EXTRACT(YEAR FROM waktu_kejadian) as tahun'))
                           ->distinct()
                           ->pluck('tahun')
                           ->toArray();
                           
            // Pastikan 'all' selalu ada
            array_unshift($tahunUnik, 'all');

            $dataMaster = [];

            foreach ($tahunUnik as $tahun) {
                // 1. Filter Basis Kueri (Semua Tahun atau Spesifik)
                $queryDasar = DB::table('laporan_laka');
                if ($tahun !== 'all') {
                    $queryDasar->whereYear('waktu_kejadian', $tahun);
                }

                // 2. Kalkulasi KPI (Indikator Kinerja Utama)
                $totalLaka = (clone $queryDasar)->count();
                $fatalMD = (clone $queryDasar)->where('keparahan', 'Tinggi')->count();
                
                $kecamatanTertinggi = (clone $queryDasar)
                    ->select('kecamatan', DB::raw('count(*) as total'))
                    ->whereNotNull('kecamatan')
                    ->groupBy('kecamatan')
                    ->orderByDesc('total')
                    ->first();
                $namaKecamatan = $kecamatanTertinggi ? $kecamatanTertinggi->kecamatan : '-';

                // Logika sederhana untuk bulan terpadat
                $bulanTerpadat = (clone $queryDasar)
                    ->select(DB::raw('EXTRACT(MONTH FROM waktu_kejadian) as bulan'), DB::raw('count(*) as total'))
                    ->groupBy('bulan')
                    ->orderByDesc('total')
                    ->first();
                $namaBulan = '-';
                if ($bulanTerpadat) {
                    $daftarBulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
                    $namaBulan = $daftarBulan[(int)$bulanTerpadat->bulan];
                }

                // 3. Kalkulasi Grafik Keparahan (Pie Chart)
                $lukaRingan = (clone $queryDasar)->where('keparahan', 'Ringan')->count();
                $cacatTetap = (clone $queryDasar)->where('keparahan', 'Sedang')->count();
                $dataCidera = [$fatalMD, $cacatTetap, $lukaRingan];

                // 4. Kalkulasi Grafik Kecamatan (Bar Chart) - Fixed 5 Kecamatan
                $kecamatans = ['Baruga', 'Puuwatu', 'Ranomeeto', 'Poasia', 'Kadia'];
                $dataKecamatan = [];
                foreach ($kecamatans as $kec) {
                    $dataKecamatan[] = (clone $queryDasar)->where('kecamatan', $kec)->count();
                }

                // 5. Kalkulasi Tren Bulanan (Line Chart)
                $labelsTrend = [];
                $dataTrend = [];
                for ($i = 1; $i <= 12; $i++) {
                    $labelsTrend[] = $daftarBulan[$i] ?? 'Bln';
                    $dataTrend[] = (clone $queryDasar)->whereMonth('waktu_kejadian', $i)->count();
                }

                // Susun dan Masukkan ke Data Master
                $dataMaster[$tahun] = [
                    'kpi' => [
                        'total' => $totalLaka,
                        'fatal' => $fatalMD,
                        'kecamatan' => $namaKecamatan,
                        'bulan' => $namaBulan
                    ],
                    'labelsTrend' => $labelsTrend,
                    'dataTrend' => $dataTrend,
                    'cidera' => $dataCidera,
                    'kecamatan' => $dataKecamatan
                ];
            }

            return response()->json($dataMaster);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghitung statistik: ' . $e->getMessage()], 500);
        }
    }
}