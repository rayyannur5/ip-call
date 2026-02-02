@extends('layouts.app')

@section('title', 'Oxi-Monitor')

@section('content')
<style>
    /* Metric Cards Styling */
    .metric-card {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        background: white;
        padding: 20px;
        margin-bottom: 20px;
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    .metric-card h3 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        color: #333;
    }
    .metric-card p {
        font-size: 0.95rem;
        margin-bottom: 5px;
        font-weight: 600;
        color: #666;
    }
    .metric-unit {
        font-size: 0.85rem;
        float: right;
        color: #888;
        background: #f8f9fa;
        padding: 2px 8px;
        border-radius: 4px;
    }
    
    /* Header Styling */
    .page-header {
        background: linear-gradient(135deg, #4B39C6 0%, #6352E5 100%);
        color: white;
        padding: 20px 25px;
        margin-bottom: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(75, 57, 198, 0.3);
    }
    .page-header h3 {
        margin: 0;
        font-weight: 600;
    }
    
    /* Live Indicator */
    .live-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        background-color: #28a745;
        border-radius: 50%;
        margin-right: 6px;
        animation: blink 1s infinite;
    }
    @keyframes blink {
        50% { opacity: 0; }
    }
    
    /* Primary Metric Card */
    .metric-card.primary {
        background: linear-gradient(135deg, #4B39C6 0%, #6352E5 100%);
    }
    .metric-card.primary h3,
    .metric-card.primary p {
        color: white;
    }
    .metric-card.primary .metric-unit {
        background: rgba(255,255,255,0.2);
        color: white;
    }
    .metric-card.primary .live-indicator {
        background-color: #7CFC00;
    }
    
    /* Card Table */
    .card-table {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .card-table .card-body {
        padding: 25px;
    }
    
    /* Section Title */
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
    }
    
    /* Date Range Picker Styling */
    .daterange-input {
        max-width: 300px;
    }
</style>

<div class="page-header">
    <h3><i class="fas fa-heartbeat me-2"></i> Oxi-Monitor</h3>
</div>

<!-- Metrics Row 1 -->
<div class="row">
    <div class="col-md-3">
        <div class="metric-card primary">
            <span class="metric-unit">L/min</span>
            <p><span class="live-indicator"></span>Aktual</p>
            <h3 id="current_flow">0,00</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <span class="metric-unit">m³</span>
            <p>Hari ini</p>
            <h3 id="usage_today">0,00</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <span class="metric-unit">m³</span>
            <p>3 Hari Terakhir</p>
            <h3 id="usage_3_days">0,00</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <span class="metric-unit">m³</span>
            <p>7 Hari Terakhir</p>
            <h3 id="usage_7_days">0,00</h3>
        </div>
    </div>
</div>

<!-- Metrics Row 2 -->
<div class="row">
    <div class="col-md-6">
        <div class="metric-card">
            <span class="metric-unit">m³</span>
            <p><i class="fas fa-chart-line me-1"></i> Rata-rata 3 Hari</p>
            <h3 id="avg_3_days">0,00</h3>
        </div>
    </div>
    <div class="col-md-6">
        <div class="metric-card">
            <span class="metric-unit">m³</span>
            <p><i class="fas fa-chart-line me-1"></i> Rata-rata 7 Hari</p>
            <h3 id="avg_7_days">0,00</h3>
        </div>
    </div>
</div>

<!-- Metrics Row 3 -->
<div class="row">
    <div class="col-md-6">
        <div class="metric-card">
            <span class="metric-unit">m³</span>
            <p>14 Hari Terakhir</p>
            <h3 id="usage_14_days">0,00</h3>
        </div>
    </div>
    <div class="col-md-6">
        <div class="metric-card">
            <span class="metric-unit">m³</span>
            <p>1 Bulan Terakhir</p>
            <h3 id="usage_30_days">0,00</h3>
        </div>
    </div>
</div>

<!-- Log Data Section -->
<h4 class="section-title mt-4"><i class="fas fa-list-alt me-2"></i> Log Data</h4>

<div class="card card-table">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div></div>
            <div class="input-group daterange-input">
                <span class="input-group-text bg-white">
                    <i class="far fa-calendar-alt"></i>
                </span>
                <input type="text" class="form-control" id="daterange" placeholder="Pilih Rentang Tanggal">
            </div>
        </div>
        
        <table id="logTable" class="table table-striped table-hover" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Total (m³)</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<!-- Date Range Picker Dependencies -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

<script>
$(document).ready(function() {
    // Initialize Date Range Picker
    $('#daterange').daterangepicker({
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        locale: {
            format: 'YYYY-MM-DD',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            fromLabel: 'Dari',
            toLabel: 'Sampai',
            customRangeLabel: 'Kustom',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                         'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            firstDay: 1
        },
        ranges: {
            'Hari Ini': [moment(), moment()],
            'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
            '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    // Initialize DataTable
    var table = $('#logTable').DataTable({
        ajax: {
            url: '{{ url("/admin/oximonitor/data") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: function(d) {
                var drp = $('#daterange').data('daterangepicker');
                d.startDate = drp.startDate.format('YYYY-MM-DD');
                d.endDate = drp.endDate.format('YYYY-MM-DD');
            }
        },
        processing: true,
        serverSide: true,
        searching: false,
        ordering: false,
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Memuat...',
            lengthMenu: 'Tampilkan _MENU_ data',
            zeroRecords: 'Tidak ada data ditemukan',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(filter dari _MAX_ total data)',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Selanjutnya',
                previous: 'Sebelumnya'
            }
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 }
        ]
    });

    // Reload table when date range changes
    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        table.ajax.reload();
    });

    // Real-time Metrics Update
    function updateMetrics() {
        $.ajax({
            url: '{{ url("/admin/oximonitor/metrics") }}',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#current_flow').text(data.current_flow);
                $('#usage_today').text(data.usage_today);
                $('#usage_3_days').text(data.usage_3_days);
                $('#usage_7_days').text(data.usage_7_days);
                $('#avg_3_days').text(data.avg_3_days);
                $('#avg_7_days').text(data.avg_7_days);
                $('#usage_14_days').text(data.usage_14_days);
                $('#usage_30_days').text(data.usage_30_days);
            },
            error: function(xhr) {
                console.error('Failed to fetch metrics:', xhr);
            }
        });
    }

    // Initial load
    updateMetrics();

    // Poll every 2 seconds
    setInterval(updateMetrics, 2000);
});
</script>
@endsection
