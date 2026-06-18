<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entri Data Laka - SafeTraffic Kendari</title>
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        .lapor-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 40px;
            background-color: #111827;
            border: 1px solid #1f2937;
            border-radius: 12px;
            padding: 35px;
            margin-top: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .form-grup { margin-bottom: 22px; }
        .form-grup label {
            display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;
            color: #9ca3af; margin-bottom: 8px; font-weight: 500;
        }

        .input-minimalis {
            width: 100%; background-color: #090d16; border: 1px solid #1f2937; color: #f3f4f6;
            padding: 12px 16px; border-radius: 6px; font-size: 0.95rem; outline: none; transition: border-color 0.2s ease;
        }
        .input-minimalis:focus { border-color: #3b82f6; }
        textarea.input-minimalis { resize: vertical; min-height: 100px; }
        .koordinat-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        .map-picker-container { display: flex; flex-direction: column; height: 100%; min-height: 450px; }
        #map-picker { width: 100%; flex-grow: 1; border-radius: 8px; border: 1px solid #1f2937; margin-top: 10px; }

        .map-instruction { font-size: 0.8rem; color: #9ca3af; margin-top: 8px; display: flex; align-items: center; gap: 5px; }

        .btn-submit-lapor {
            width: 100%; background-color: #ef4444; color: #ffffff; border: none; padding: 14px;
            border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; margin-top: 10px;
        }
        .btn-submit-lapor:hover { background-color: #dc2626; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); }

        /* Desain Tombol Geolocation */
        .btn-lokasi {
            background-color: transparent; color: #3b82f6; border: 1px solid #3b82f6; padding: 10px 15px;
            border-radius: 6px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease;
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-bottom: 5px;
        }
        .btn-lokasi:hover { background-color: #1e3a8a; color: #ffffff; }
        .btn-lokasi:disabled { opacity: 0.6; cursor: not-allowed; }

        @media (max-width: 850px) {
            .lapor-grid { grid-template-columns: 1fr; gap: 30px; padding: 20px; }
            .map-picker-container { min-height: 350px; }
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo"><span>⛨</span> SafeTraffic Kendari</div>
        <div class="nav-menu">
            <a href="{{ route('beranda') }}">Beranda</a>
            <a href="{{ route('peta') }}">Peta Blackspot</a>
            <a href="{{ route('statistik') }}">Statistik Laka</a>
        </div>
        <a href="{{ route('laporan') }}" class="nav-btn-lapor" style="background-color: #ef4444; color: #ffffff;">🚨 Laporan Kecelakaan</a>
    </nav>

    <main class="container-page">
        <h1 class="page-title">Formulir Laporan Insiden</h1>
        <p class="page-subtitle">Pencatatan data taktis kecelakaan lalu lintas baru secara real-time ke dalam pangkalan data PostGIS.</p>

        <form action="{{ route('laporan') }}" method="POST" class="lapor-grid">
            
            <div class="form-kolom-kiri">
                <div class="form-grup">
                    <label for="waktu_kejadian">Waktu Kejadian</label>
                    <input type="datetime-local" id="waktu_kejadian" name="waktu_kejadian" class="input-minimalis" required>
                </div>

                <div class="form-grup">
                    <label for="keparahan">Tingkat Fatalitas (Keparahan)</label>
                    <select id="keparahan" name="keparahan" class="input-minimalis" required>
                        <option value="" disabled selected>Pilih tingkat keparahan...</option>
                        <option value="Tinggi">Tinggi (Meninggal Dunia / MD)</option>
                        <option value="Sedang">Sedang (Cacat Tetap / Luka Berat)</option>
                        <option value="Ringan">Ringan (Luka Ringan / LL)</option>
                    </select>
                </div>

                <div class="form-grup">
                    <label for="deskripsi">Deskripsi Lokasi & Kejadian</label>
                    <textarea id="deskripsi" name="deskripsi" class="input-minimalis" placeholder="Contoh: Terjadi tabrakan antara roda dua di dekat Bundaran Pesawat Wuawua, Jl. M.T. Haryono..." required></textarea>
                </div>

                <div class="koordinat-row">
                    <div class="form-grup">
                        <label for="latitude">Latitude</label>
                        <input type="text" id="latitude" name="latitude" class="input-minimalis" placeholder="Otomatis terisi..." readonly required>
                    </div>
                    <div class="form-grup">
                        <label for="longitude">Longitude</label>
                        <input type="text" id="longitude" name="longitude" class="input-minimalis" placeholder="Otomatis terisi..." readonly required>
                    </div>
                </div>

                <button type="submit" class="btn-submit-lapor">Kirim Laporan Valid</button>
            </div>

            <div class="map-picker-container">
                <label style="display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 8px; font-weight: 500;">
                    📍 Penentuan Titik Spasial
                </label>
                
                <button type="button" id="btn-lokasi-terkini" class="btn-lokasi">
                    🎯 Gunakan Lokasi Saya Saat Ini
                </button>

                <div id="map-picker"></div>
                <div class="map-instruction">
                    <span>💡</span> <i>Atau klik manual di atas peta satelit untuk menetapkan lokasi kejadian.</i>
                </div>
            </div>

        </form>
    </main>

    <script>
        // 1. Inisialisasi Peta Google Maps
        let mapPicker = L.map('map-picker', { zoomControl: false }).setView([-3.9881, 122.5137], 13);
        L.control.zoom({ position: 'bottomleft' }).addTo(mapPicker);
        L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', { maxZoom: 20, attribution: '&copy; Google Maps' }).addTo(mapPicker);

        let markerMovable = null;

        // FUNGSI UTAMA: Memperbarui Form & Peta
        function updateTitikLokasi(lat, lng, zoomLevel = 13) {
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            mapPicker.setView([lat, lng], zoomLevel); // Pindahkan kamera peta

            if (markerMovable === null) {
                markerMovable = L.marker([lat, lng], { bounceOnAdd: true }).addTo(mapPicker);
            } else {
                markerMovable.setLatLng([lat, lng]);
            }
        }

        // 2. Event Listener: Klik Manual di Peta
        mapPicker.on('click', function(e) {
            updateTitikLokasi(e.latlng.lat.toFixed(6), e.latlng.lng.toFixed(6), mapPicker.getZoom());
        });

        // 3. FITUR GEOLOCATION (Mencari Lokasi Terkini User)
        const btnLokasi = document.getElementById('btn-lokasi-terkini');
        btnLokasi.addEventListener('click', function() {
            if (navigator.geolocation) {
                btnLokasi.innerHTML = "⏳ Sedang mencari satelit GPS...";
                btnLokasi.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Berhasil mendapatkan lokasi
                        let currentLat = position.coords.latitude.toFixed(6);
                        let currentLng = position.coords.longitude.toFixed(6);
                        
                        updateTitikLokasi(currentLat, currentLng, 17); // Zoom level 17 agar sangat dekat dengan jalan
                        
                        btnLokasi.innerHTML = "✅ Lokasi ditemukan!";
                        setTimeout(() => { 
                            btnLokasi.innerHTML = "🎯 Gunakan Lokasi Saya Saat Ini"; 
                            btnLokasi.disabled = false;
                        }, 3000);
                    }, 
                    function(error) {
                        // Gagal mendapatkan lokasi (Ditolak user atau error jaringan)
                        alert("Gagal mengakses GPS: " + error.message + "\nPastikan Anda mengizinkan akses lokasi pada browser.");
                        btnLokasi.innerHTML = "🎯 Gunakan Lokasi Saya Saat Ini";
                        btnLokasi.disabled = false;
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                alert("Browser Anda terlalu lawas dan tidak mendukung fitur GPS Geolocation.");
            }
        });
    </script>
</body>
</html>