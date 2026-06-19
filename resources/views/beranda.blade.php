<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - SafeTraffic Kendari</title>
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        /* CSS Khusus untuk Beranda Minimalis */
        .hero-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 80px 20px 40px;
            max-width: 900px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .hero-title span {
            color: #ef4444; /* Aksen Merah untuk kata Black Spot */
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: #94a3b8;
            font-weight: 300;
            margin-bottom: 40px;
            max-width: 700px;
            line-height: 1.6;
        }

        /* Desain Grid Kartu Menu yang Elegan */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }

        .card-menu {
            background-color: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 30px 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .card-menu:hover {
            transform: translateY(-5px);
            border-color: #3b82f6; /* Aksen biru saat di-hover */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 10px;
        }

        .card-desc {
            font-size: 0.9rem;
            color: #94a3b8;
            line-height: 1.5;
            flex-grow: 1;
        }

        .card-arrow {
            margin-top: 20px;
            color: #3b82f6;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-menu:hover .card-arrow {
            color: #60a5fa;
            transform: translateX(5px);
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body class="dark-mode">

    <nav>
        <div class="logo"><span style="font-size: 1.4rem;">⛨</span> SafeTraffic Kendari</div>
        <div class="nav-menu">
            <a href="{{ route('beranda') }}" class="active">Beranda</a>
            <a href="{{ route('peta') }}">Peta Blackspot</a>
            <a href="{{ route('statistik') }}">Statistik Laka</a>
        </div>
        <a href="{{ route('laporan') }}" class="nav-btn-lapor">🚨 Laporan Kecelakaan</a>
    </nav>

    <main class="hero-section">
        <h1 class="hero-title">Sistem Pemetaan <span>Black Spot</span><br>Kecelakaan Kota Kendari</h1>
        <p class="hero-subtitle">
            Platform WebGIS analitik berbasis data spasial untuk mendukung pengambilan keputusan dan rekayasa lalu lintas proaktif bagi Polresta Kendari dan BPTD Sulawesi Tenggara.
        </p>
    </main>

    <section class="dashboard-grid">
        <a href="{{ route('peta') }}" class="card-menu">
            <div class="card-icon">🗺️</div>
            <h2 class="card-title">Peta Spasial</h2>
            <p class="card-desc">Visualisasi titik rawan kecelakaan (Black Spot) menggunakan analisis kepadatan radius berbasis koordinat geolokasi.</p>
            <div class="card-arrow">Buka Peta &rarr;</div>
        </a>

        <a href="{{ route('statistik') }}" class="card-menu">
            <div class="card-icon">📊</div>
            <h2 class="card-title">Dashboard Statistik</h2>
            <p class="card-desc">Rekapitulasi data kecelakaan tahunan dan bulanan, tingkat keparahan, serta distribusi insiden per kecamatan.</p>
            <div class="card-arrow">Lihat Analisis &rarr;</div>
        </a>

        <a href="{{ route('laporan') }}" class="card-menu" style="border-top: 3px solid #ef4444;">
            <div class="card-icon">📋</div>
            <h2 class="card-title">Entri Data Laka</h2>
            <p class="card-desc">Formulir digital untuk menginput data kejadian kecelakaan baru ke dalam pangkalan data (Database) secara real-time.</p>
            <div class="card-arrow" style="color: #ef4444;">Tambah Data &rarr;</div>
        </a>
    </section>

</body>
</html>