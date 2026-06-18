<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Spasial - SafeTraffic Kendari</title>
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        .peta-wrapper {
            position: relative;
            height: calc(100vh - 70px);
            width: 100%;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        .panel-peta {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid #1f2937;
            padding: 20px;
            border-radius: 8px;
            color: #f3f4f6;
            z-index: 1000;
            width: 290px;
        }

        .section-panel {
            margin-bottom: 20px;
        }

        .section-panel h3 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .flex-grup {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .opsi-item {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #d1d5db;
        }

        .opsi-item input {
            cursor: pointer;
            accent-color: #3b82f6;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo"><span>⛨</span> SafeTraffic Kendari</div>
        <div class="nav-menu">
            <a href="{{ route('beranda') }}">Beranda</a>
            <a href="{{ route('peta') }}" class="active">Peta Blackspot</a>
            <a href="{{ route('statistik') }}">Statistik Laka</a>
        </div>
        <a href="{{ route('laporan') }}" class="nav-btn-lapor">🚨 Laporan Kecelakaan</a>
    </nav>

    <div class="peta-wrapper">
        <div id="map"></div>

        <div class="panel-peta">
            <div class="section-panel">
                <h3>Visualisasi</h3>
                <div class="flex-grup">
                    <label class="opsi-item">
                        <input type="radio" name="mode-peta" value="titik" checked> Sebaran Koordinat
                    </label>
                    <label class="opsi-item">
                        <input type="radio" name="mode-peta" value="heatmap"> Area Rawan (Radius 50m)
                    </label>
                </div>
            </div>

            <div class="section-panel" id="panel-filter">
                <h3>Filter Fatalitas</h3>
                <div class="flex-grup">
                    <label class="opsi-item">
                        <input type="checkbox" id="chk-tinggi" checked> 
                        <span class="dot" style="background-color: #ef4444;"></span> Tinggi (MD)
                    </label>
                    <label class="opsi-item">
                        <input type="checkbox" id="chk-sedang" checked> 
                        <span class="dot" style="background-color: #f97316;"></span> Sedang (CT)
                    </label>
                    <label class="opsi-item">
                        <input type="checkbox" id="chk-ringan" checked> 
                        <span class="dot" style="background-color: #eab308;"></span> Ringan (LL)
                    </label>
                </div>
            </div>

            <div class="section-panel" id="panel-legenda-heatmap" style="display: none; border-top: 1px solid #1f2937; padding-top: 15px;">
                <h3>Pusat Kepadatan (Radius 200m)</h3>
                <p style="font-size: 0.75rem; color: #9ca3af; margin-bottom: 12px; line-height: 1.4;">
                    <i>*Sistem menggunakan agregasi spasial. Titik pinggiran akan ditarik ke pusat gravitasi klaster terpadat.</i>
                </p>
                <div class="flex-grup">
                    <div class="opsi-item">
                        <span class="dot" style="background-color: #ef4444; box-shadow: 0 0 8px #ef4444;"></span> 
                        Zona Merah (Pusat Utama ≥ 8 Kasus)
                    </div>
                    <div class="opsi-item">
                        <span class="dot" style="background-color: #f97316; box-shadow: 0 0 8px #f97316;"></span> 
                        Zona Oranye (Pusat Rawan 5 - 7 Kasus)
                    </div>
                    <div class="opsi-item">
                        <span class="dot" style="background-color: #eab308; box-shadow: 0 0 8px #eab308;"></span> 
                        Zona Kuning (Pusat Waspada 3 - 4 Kasus)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inisialisasi Peta Google Maps
        let map = L.map('map', { zoomControl: false }).setView([-3.9881, 122.5137], 13); 
        L.control.zoom({ position: 'bottomleft' }).addTo(map);

        L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            maxZoom: 20, attribution: '&copy; Google Maps'
        }).addTo(map);

        let groupTinggi = L.layerGroup();
        let groupSedang = L.layerGroup();
        let groupRingan = L.layerGroup();
        let layerCustomHeatmap = L.layerGroup();

        let dataLakaMentah = [];

        // Tarik data dari Database (API Flask)
        fetch('/api/peta')
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    console.error("Gagal memuat data:", data.error);
                    return;
                }
                dataLakaMentah = data;
                prosesDataSpasial();
                aplikasikanFilter();
            });

        function prosesDataSpasial() {
            // Memori anti-duplikasi untuk mencegah Heatmap bertumpuk dan menjadi hitam/pekat
            let lokasiSelesaiDigambar = new Set();

            dataLakaMentah.forEach(titik => {
                let lat = parseFloat(titik.latitude);
                let lng = parseFloat(titik.longitude);
                if (isNaN(lat) || isNaN(lng)) return;

                // --- 1. RENDER TITIK PIN KOORDINAT ---
                let warnaPin = '#eab308';
                let grupTarget = groupRingan;
                
                if (titik.keparahan === 'Tinggi') { warnaPin = '#ef4444'; grupTarget = groupTinggi; }
                else if (titik.keparahan === 'Sedang') { warnaPin = '#f97316'; grupTarget = groupSedang; }

                let marker = L.circleMarker([lat, lng], {
                    radius: 6, fillColor: warnaPin, color: '#090d16', weight: 1, opacity: 1, fillOpacity: 0.9
                });

                // Popup yang menampilkan jumlah kasus (Rahasia PostGIS)
                let popupHtml = `
                    <div style="font-family: 'Poppins', sans-serif; color: #111827; font-size:0.85rem; line-height:1.4;">
                        <span style="color:${warnaPin}; font-weight:700; display:block; margin-bottom:4px;">🚨 Kejadian ${titik.keparahan}</span>
                        <b>Waktu:</b> ${titik.waktu}<br>
                        <b>Lokasi:</b> ${titik.deskripsi}<br>
                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e5e7eb; font-weight: 600; color: #3b82f6;">
                            🔍 Terdapat ${titik.jumlah_sekitar} kasus di radius 50m ini
                        </div>
                    </div>`;
                marker.bindPopup(popupHtml).addTo(grupTarget);


                // --- 2. RENDER LINGKARAN HEATMAP (DEDUPLIKASI) ---
                let kunciKoordinat = `${lat},${lng}`; // Buat ID unik dari koordinat

                // Hanya gambar jika koordinat ini belum digambar DAN jumlah kasus sekitarnya > 2
                if (!lokasiSelesaiDigambar.has(kunciKoordinat) && titik.jumlah_sekitar > 2) {
                    
                    let warnaPanas = '#eab308'; // Waspada
                    if (titik.jumlah_sekitar >= 8) warnaPanas = '#ef4444'; // Sangat Rawan
                    else if (titik.jumlah_sekitar >= 5) warnaPanas = '#f97316'; // Rawan

                    L.circle([lat, lng], {
                        radius: 200, 
                        fillColor: warnaPanas, 
                        color: 'transparent', 
                        fillOpacity: 0.50 
                    }).addTo(layerCustomHeatmap);

                    // Masukkan ke memori agar tidak tergambar berulang kali
                    lokasiSelesaiDigambar.add(kunciKoordinat);
                }
            });
        }

        // Fungsi mengatur perpindahan Mode
        function aplikasikanFilter() {
            // Bersihkan kanvas peta
            map.removeLayer(groupTinggi); 
            map.removeLayer(groupSedang); 
            map.removeLayer(groupRingan); 
            map.removeLayer(layerCustomHeatmap);

            let mode = document.querySelector('input[name="mode-peta"]:checked').value;
            
            let pFilter = document.getElementById('panel-filter');
            let pLegenda = document.getElementById('panel-legenda-heatmap');

            if (mode === 'heatmap') {
                // Mode Heatmap aktif
                if (pFilter) pFilter.style.display = 'none';
                if (pLegenda) pLegenda.style.display = 'block';
                
                layerCustomHeatmap.addTo(map);
            } else {
                // Mode Titik aktif
                if (pFilter) pFilter.style.display = 'block';
                if (pLegenda) pLegenda.style.display = 'none';
                
                if (document.getElementById('chk-tinggi').checked) groupTinggi.addTo(map);
                if (document.getElementById('chk-sedang').checked) groupSedang.addTo(map);
                if (document.getElementById('chk-ringan').checked) groupRingan.addTo(map);
            }
        }

        // Listener Event Panel Kontrol
        document.querySelectorAll('input[name="mode-peta"]').forEach(r => r.addEventListener('change', aplikasikanFilter));
        document.getElementById('chk-tinggi').addEventListener('change', aplikasikanFilter);
        document.getElementById('chk-sedang').addEventListener('change', aplikasikanFilter);
        document.getElementById('chk-ringan').addEventListener('change', aplikasikanFilter);
    </script>
</body>
</html>