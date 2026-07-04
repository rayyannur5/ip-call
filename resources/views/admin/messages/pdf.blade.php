<!DOCTYPE html>
<html>
<head>
    <title>Nurse Call - Log Pesan</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #1a1a2e;
            margin: 0;
            padding: 0;
            position: relative;
        }

        .bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .bg-overlay img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.08;
        }

        .container {
            padding: 30px 40px;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #2c3e50;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .header .subtitle {
            font-size: 10px;
            color: #555;
            margin: 0;
            font-weight: 400;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 8px;
            color: #666;
        }

        .meta-info .generated {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead tr {
            background-color: #2c3e50;
        }

        thead th {
            color: #ffffff;
            font-weight: 600;
            font-size: 9px;
            padding: 8px 10px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        thead th:first-child {
            border-radius: 4px 0 0 0;
        }

        thead th:last-child {
            border-radius: 0 4px 0 0;
        }

        tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }

        tbody tr:nth-child(even) {
            background-color: rgba(44, 62, 80, 0.04);
        }

        tbody tr:nth-child(odd) {
            background-color: rgba(255, 255, 255, 0.7);
        }

        tbody td {
            padding: 6px 10px;
            font-size: 8.5px;
            color: #333;
            border: none;
            vertical-align: middle;
        }

        .no-col {
            width: 30px;
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7.5px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 600;
            color: #fff;
        }

        .total-info {
            font-size: 9px;
            color: #444;
            margin-bottom: 6px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="bg-overlay">
        <img src="{{ public_path('assets/images/bg.JPEG') }}" />
    </div>

    <div class="container">
        <div class="header">
            <h1>Nurse Call - Log Pesan</h1>
            <p class="subtitle">
                @php
                    $filters = [];
                    if (!empty($start_date) && !empty($end_date)) {
                        $filters[] = 'Periode: ' . \Carbon\Carbon::parse($start_date)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($end_date)->format('d M Y');
                    }
                    if (!empty($category_name)) {
                        $filters[] = 'Kategori: ' . $category_name;
                    }
                @endphp
                @if(count($filters) > 0)
                    {{ implode(' | ', $filters) }}
                @else
                    Semua Data (Tanpa Filter)
                @endif
            </p>
        </div>

        <table>
            <tr>
                <td style="border: none; padding: 2px 0;">
                    <span class="total-info">Total Data: {{ count($logs) }} pesan</span>
                </td>
                <td style="border: none; padding: 2px 0; text-align: right;">
                    <span style="font-size: 8px; color: #888;">Dicetak: {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->format('d M Y H:i:s') }} WIB</span>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th class="no-col">No</th>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Nama Ruang</th>
                    <th>Waktu Respon</th>
                    <th>Nurse Presence</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $index => $log)
                <tr>
                    <td class="no-col">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->timestamp)->timezone('Asia/Jakarta')->format('d M Y H:i:s') }}</td>
                    <td>
                        @php
                            $catName = strtoupper($log->category->name ?? '');
                            $catColor = match($catName) {
                                'INFUS' => '#27ae60',
                                'TELEPON' => '#f39c12',
                                'PERAWAT' => '#e67e22',
                                'DARURAT' => '#e74c3c',
                                'CODE BLUE' => '#2980b9',
                                default => '#7f8c8d',
                            };
                        @endphp
                        <span class="badge" style="background-color: {{ $catColor }};">{{ $log->category->name ?? '-' }}</span>
                    </td>
                    <td>{{ $log->bed->username ?? '-' }}</td>
                    <td>
                        @if($log->nurse_presence)
                            {{ \Carbon\CarbonInterval::seconds($log->time)->cascade()->locale('id')->forHumans() }}
                        @else
                            0 detik
                        @endif
                    </td>
                    <td>
                        @if($log->nurse_presence)
                            <span class="badge" style="background-color: #27ae60;">Ya</span>
                        @else
                            <span class="badge" style="background-color: #e74c3c;">Tidak</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Nurse Call System &mdash; Dokumen ini digenerate secara otomatis
        </div>
    </div>
</body>
</html>
