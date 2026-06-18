<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Laka - SafeTraffic Kendari</title>
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <style>
        .filter-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .select-minimalis {
            background-color: #111827;
            color: #f3f4f6;
            border: 1px solid #1f2937;
            padding: 8px 16px;
            border-radius: 6px;
            outline: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        /* Tampilan Grid Indikator Utama (KPI) */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .kpi-box {
            background-color: #111827;
            border: 1px solid #1f2937;
            border-radius: 8px;
            padding: 20px;
        }

        .kpi-box p {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .kpi-box h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #f3f4f6;
        }

        /* Grid Komponen Grafik */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .chart-card {
            background-color: #111827;
            border: 1px solid #1f2937;
            border-radius: 8px;
            padding: 25px;
            height: 380px;
            display: flex;
            flex-direction: column;
        }

        .chart-card h3 {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 20px;
            color: #f3f4f6;
        }

        .chart-container {
            position: relative;
            flex-grow: 1;
            height: 100%;
        }

        .full-width {
            grid-column: span 2;
        }

        @media (max-width: 90cpx) {
            .charts-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo"><span>⛨</span> SafeTraffic Kendari</div>
        <div class="nav-menu">
            <a href="{{ route('beranda') }}">Beranda</a>
            <a href="{{ route('peta') }}">Peta Blackspot</a>
            <a href="{{ route('statistik') }}" class="active">Statistik Laka</a>
        </div>
        <a href="{{ route('laporan') }}" class="nav-btn-lapor">🚨 Laporan Kecelakaan</a>
    </nav>

    <main class="container-page">
        <div class="filter-wrapper">
            <div>
                <h1 class="page-title">Dashboard Analisis</h1>
                <p class="page-subtitle" style="margin-bottom:0;">Pemantauan parameter tren kecelakaan secara real-time.</p>
            </div>
            <select id="filter-tahun" class="select-minimalis">
                <option value="all">Semua Periode (2023 - 2026)</option>
                <option value="2026">Tahun 2026</option>
                <option value="2025">Tahun 2025</option>
                <option value="2024">Tahun 2024</option>
                <option value="2023">Tahun 2023</option>
            </select>
        </div>

        <div class="kpi-grid">
            <div class="kpi-box" style="border-left: 4px solid #3b82f6;">
                <p>Total Insiden</p>
                <h2 id="kpi-total">-</h2>
            </div>
            <div class="kpi-box" style="border-left: 4px solid #ef4444;">
                <p>Fatalitas (MD)</p>
                <h2 id="kpi-fatal">-</h2>
            </div>
            <div class="kpi-box">
                <p>Kecamatan Tertinggi</p>
                <h2 id="kpi-kecamatan" style="font-size: 1.4rem; padding-top: 8px;">-</h2>
            </div>
            <div class="kpi-box">
                <p>Bulan Terpadat</p>
                <h2 id="kpi-bulan" style="font-size: 1.4rem; padding-top: 8px;">-</h2>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3>Tren Insiden Bulanan</h3>
                <div class="chart-container"><canvas id="chartTrend"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Tingkat Keparahan Korban</h3>
                <div class="chart-container"><canvas id="chartCidera"></canvas></div>
            </div>
            <div class="chart-card full-width">
                <h3>Distribusi Kecelakaan Per Kecamatan</h3>
                <div class="chart-container"><canvas id="chartKecamatan"></canvas></div>
            </div>
        </div>
    </main>

    <script>
        Chart.register(ChartDataLabels);
        Chart.defaults.color = '#9ca3af'; 
        Chart.defaults.borderColor = '#1f2937'; 

        const labelsKecamatan = ['Baruga', 'Puuwatu', 'Ranomeeto', 'Poasia', 'Kadia'];
        const labelsCidera = ['MD (Meninggal Dunia)', 'CT (Cacat/Cidera)', 'LL (Luka-Luka)'];
        let chartTrend, chartCidera, chartKecamatan, dataMaster = {};

        let ctxTrend = document.getElementById('chartTrend').getContext('2d');
        let ctxCidera = document.getElementById('chartCidera').getContext('2d');
        let ctxKecamatan = document.getElementById('chartKecamatan').getContext('2d');

        fetch('/api/statistik')
            .then(res => res.json())
            .then(data => {
                if(data.error) return;
                dataMaster = data; 
                initGrafik();
            });

        function initGrafik() {
            let d = dataMaster['all'];
            document.getElementById('kpi-total').innerText = d.kpi.total;
            document.getElementById('kpi-fatal').innerText = d.kpi.fatal;
            document.getElementById('kpi-kecamatan').innerText = d.kpi.kecamatan;
            document.getElementById('kpi-bulan').innerText = d.kpi.bulan;

            chartTrend = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: d.labelsTrend,
                    datasets: [{
                        label: 'Insiden', data: d.dataTrend, borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.05)', borderWidth: 2, tension: 0.2, fill: true
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { datalabels: { display: false } } }
            });

            chartCidera = new Chart(ctxCidera, {
                type: 'pie',
                data: {
                    labels: labelsCidera,
                    datasets: [{ data: d.cidera, backgroundColor: ['#ef4444', '#f97316', '#eab308'], borderWidth: 0 }]
                },
                options: { 
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12 } },
                        datalabels: {
                            color: '#ffffff', font: { weight: '600', size: 10 },
                            formatter: (val, ctx) => {
                                if (val === 0) return '';
                                let sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                return ((val * 100) / sum).toFixed(1) + "%";
                            }
                        }
                    }
                }
            });

            chartKecamatan = new Chart(ctxKecamatan, {
                type: 'bar',
                data: {
                    labels: labelsKecamatan,
                    datasets: [{ label: 'Kasus', data: d.kecamatan, backgroundColor: '#3b82f6', borderRadius: 4 }]
                },
                options: { 
                    responsive: true, maintainAspectRatio: false,
                    plugins: { datalabels: { anchor: 'end', align: 'top', color: '#f3f4f6', font: { weight: '600' } } }
                }
            });
        }

        function updateGrafik(tahun) {
            let d = dataMaster[tahun]; if (!d) return;
            document.getElementById('kpi-total').innerText = d.kpi.total;
            document.getElementById('kpi-fatal').innerText = d.kpi.fatal;
            document.getElementById('kpi-kecamatan').innerText = d.kpi.kecamatan;
            document.getElementById('kpi-bulan').innerText = d.kpi.bulan;

            chartTrend.data.labels = d.labelsTrend; chartTrend.data.datasets[0].data = d.dataTrend; chartTrend.update();
            chartCidera.data.datasets[0].data = d.cidera; chartCidera.update();
            chartKecamatan.data.datasets[0].data = d.kecamatan; chartKecamatan.update();
        }

        document.getElementById('filter-tahun').addEventListener('change', function() {
            if(Object.keys(dataMaster).length > 0) updateGrafik(this.value);
        });
    </script>
</body>
</html>