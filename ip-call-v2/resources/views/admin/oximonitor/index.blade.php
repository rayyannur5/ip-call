@extends($layout ?? 'layouts.app')

@section('title', 'Oxi-Monitor')

@section('content')
<style>
    .oxi-page {
        color: #182230;
    }

    .oxi-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 24px;
        margin-bottom: 22px;
        border: 1px solid #dbe4ee;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
    }

    .oxi-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: 0;
        color: #111827;
    }

    .oxi-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        color: #0f766e;
        background: #dff7f3;
        border: 1px solid #bcebe4;
    }

    .oxi-subtitle {
        margin: 8px 0 0;
        color: #667085;
        font-size: 0.92rem;
    }

    .unit-panel {
        min-width: 330px;
        padding: 12px;
        border: 1px solid #dbe4ee;
        border-radius: 8px;
        background: #f8fafc;
    }

    .unit-panel-label {
        margin-bottom: 9px;
        color: #475467;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .unit-options {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    .unit-option {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        margin: 0;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: #344054;
        font-weight: 800;
        cursor: pointer;
        user-select: none;
        transition: border-color .18s ease, color .18s ease, background .18s ease, box-shadow .18s ease;
    }

    .unit-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .unit-option:has(input:checked) {
        border-color: #0f766e;
        background: #ecfdf9;
        color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, .12);
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .metric-card {
        min-height: 158px;
        padding: 18px;
        border: 1px solid #dbe4ee;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        border-color: #b6c6d8;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.1);
    }

    .metric-card.primary {
        border-color: #0f766e;
        background: #0f1f1d;
        color: #ffffff;
    }

    .metric-card.primary .metric-label,
    .metric-card.primary .metric-note,
    .metric-card.primary .interval-toggle {
        color: #d6f5ef;
    }

    .metric-topline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 18px;
    }

    .metric-label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #667085;
        font-size: 0.91rem;
        font-weight: 800;
    }

    .metric-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        min-height: 26px;
        padding: 3px 8px;
        border-radius: 7px;
        background: #eef4ff;
        color: #344054;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .metric-card.primary .metric-badge {
        background: rgba(255, 255, 255, .12);
        color: #ffffff;
    }

    .metric-value {
        margin: 0;
        color: #111827;
        font-size: 2rem;
        line-height: 1.05;
        font-weight: 900;
    }

    .metric-card.primary .metric-value {
        color: #ffffff;
    }

    .metric-conversions {
        display: grid;
        gap: 6px;
        margin-top: 12px;
    }

    .conversion-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 6px;
        border-top: 1px solid #edf2f7;
        color: #475467;
        font-size: 0.86rem;
        font-weight: 700;
    }

    .metric-card.primary .conversion-row {
        border-top-color: rgba(255, 255, 255, .16);
        color: #d6f5ef;
    }

    .live-dot {
        display: inline-block;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #12b76a;
        box-shadow: 0 0 0 4px rgba(18, 183, 106, .18);
        animation: livePulse 1.15s infinite;
    }

    @keyframes livePulse {
        50% { opacity: .42; }
    }

    .interval-toggle {
        border: 0;
        padding: 6px 9px;
        border-radius: 7px;
        background: rgba(255, 255, 255, .1);
        color: #d6f5ef;
        font-size: .86rem;
        font-weight: 800;
    }

    .interval-toggle:hover {
        background: rgba(255, 255, 255, .16);
        color: #ffffff;
    }

    .interval-config {
        display: none;
        margin-top: 14px;
    }

    .table-shell {
        margin-top: 22px;
        border: 1px solid #dbe4ee;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    .table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid #edf2f7;
        background: #f8fafc;
    }

    .section-title {
        margin: 0;
        color: #111827;
        font-size: 1.08rem;
        font-weight: 900;
    }

    .daterange-input {
        max-width: 320px;
    }

    .table-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 14px;
        flex-wrap: wrap;
    }

    .refresh-toggle {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: #475467;
        font-size: 0.88rem;
        font-weight: 800;
        user-select: none;
    }

    .refresh-toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .refresh-switch {
        position: relative;
        width: 44px;
        height: 24px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background .18s ease, box-shadow .18s ease;
    }

    .refresh-switch::after {
        content: "";
        position: absolute;
        top: 4px;
        left: 4px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .18);
        transition: transform .18s ease;
    }

    .refresh-toggle input:checked + .refresh-switch {
        background: #0f766e;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, .12);
    }

    .refresh-toggle input:checked + .refresh-switch::after {
        transform: translateX(20px);
    }

    .daily-summary-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        margin: 18px 20px 0;
        padding: 16px 18px;
        border: 1px solid #bcebe4;
        border-radius: 8px;
        background: #ecfdf9;
    }

    .daily-summary-info {
        flex: 1;
    }

    .daily-summary-title {
        margin: 0 0 6px;
        color: #0f766e;
        font-size: 0.82rem;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .daily-summary-range {
        margin: 0;
        color: #344054;
        font-size: 0.96rem;
        font-weight: 800;
    }

    .daily-summary-stats {
        display: flex;
        gap: 32px;
        align-items: flex-start;
    }

    .daily-summary-block {
        display: grid;
        justify-items: end;
        gap: 6px;
        min-width: 200px;
    }

    .daily-summary-label {
        margin: 0 0 2px;
        color: #0f766e;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .daily-summary-main {
        color: #111827;
        font-size: 1.45rem;
        line-height: 1.1;
        font-weight: 900;
        white-space: nowrap;
    }

    .daily-summary-block .usage-stack {
        justify-content: flex-end;
    }

    .oxi-table-wrap {
        padding: 18px 20px 20px;
    }

    #logTable {
        margin-bottom: 0;
    }

    #logTable thead th {
        border-bottom: 1px solid #dbe4ee;
        color: #475467;
        font-size: 0.78rem;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .usage-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .usage-pill {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 5px 9px;
        border: 1px solid #dbe4ee;
        border-radius: 7px;
        background: #f8fafc;
        color: #344054;
        font-weight: 800;
        white-space: nowrap;
    }

    @media (max-width: 1199px) {
        .metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .oxi-header,
        .table-toolbar,
        .daily-summary-card,
        .table-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .daily-summary-card {
            display: flex;
            gap: 16px;
        }

        .daily-summary-stats {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }

        .daily-summary-block {
            justify-items: start;
            min-width: 0;
        }

        .daily-summary-block .usage-stack {
            justify-content: flex-start;
        }

        .unit-panel {
            min-width: 0;
        }

        .unit-options {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .metric-grid {
            grid-template-columns: 1fr;
        }

        .daterange-input {
            max-width: none;
        }
    }
</style>

<div class="oxi-page">
    <div class="oxi-header">
        <div>
            <h3 class="oxi-title">
                <span class="oxi-title-icon"><i class="fas fa-heartbeat"></i></span>
                Oxi-Monitor
            </h3>
            <p class="oxi-subtitle">Pantau pemakaian oksigen dengan konversi satuan yang bisa dipilih.</p>
        </div>

        <div class="unit-panel">
            <div class="unit-panel-label">Satuan ditampilkan</div>
            <div class="unit-options" id="unit-options">
                <label class="unit-option"><input type="checkbox" value="m3" checked>m3</label>
                <label class="unit-option"><input type="checkbox" value="galon">Galon</label>
                <label class="unit-option"><input type="checkbox" value="liter">Liter</label>
                <label class="unit-option"><input type="checkbox" value="kg">Kg</label>
            </div>
        </div>
    </div>

    <div class="metric-grid">
        <div class="metric-card primary">
            <div class="metric-topline">
                <p class="metric-label"><span class="live-dot"></span>Aktual</p>
                <span class="metric-badge">L/min</span>
            </div>
            <h3 class="metric-value" id="current_flow">0,000</h3>
            <button type="button" class="interval-toggle mt-2" id="interval-toggle">
                <i class="fas fa-cog fa-sm me-1"></i> Interval flow
            </button>
            <div class="interval-config" id="interval-config">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">ms</span>
                    <input type="number" id="flow_interval_input" class="form-control" value="500" step="100" min="100">
                </div>
            </div>
        </div>

        <div class="metric-card" data-metric="usage_today">
            <div class="metric-topline">
                <p class="metric-label">Hari ini</p>
                <span class="metric-badge">Live</span>
            </div>
            <h3 class="metric-value metric-main">0,000</h3>
            <div class="metric-conversions"></div>
        </div>

        <div class="metric-card" data-metric="usage_3_days">
            <div class="metric-topline">
                <p class="metric-label">3 Hari Terakhir</p>
                <span class="metric-badge">Total</span>
            </div>
            <h3 class="metric-value metric-main">0,000</h3>
            <div class="metric-conversions"></div>
        </div>

        <div class="metric-card" data-metric="usage_7_days">
            <div class="metric-topline">
                <p class="metric-label">7 Hari Terakhir</p>
                <span class="metric-badge">Total</span>
            </div>
            <h3 class="metric-value metric-main">0,000</h3>
            <div class="metric-conversions"></div>
        </div>

        <div class="metric-card" data-metric="avg_3_days">
            <div class="metric-topline">
                <p class="metric-label"><i class="fas fa-chart-line"></i> Rata-rata 3 Hari</p>
                <span class="metric-badge">Avg</span>
            </div>
            <h3 class="metric-value metric-main">0,000</h3>
            <div class="metric-conversions"></div>
        </div>

        <div class="metric-card" data-metric="avg_7_days">
            <div class="metric-topline">
                <p class="metric-label"><i class="fas fa-chart-line"></i> Rata-rata 7 Hari</p>
                <span class="metric-badge">Avg</span>
            </div>
            <h3 class="metric-value metric-main">0,000</h3>
            <div class="metric-conversions"></div>
        </div>

        <div class="metric-card" data-metric="usage_14_days">
            <div class="metric-topline">
                <p class="metric-label">14 Hari Terakhir</p>
                <span class="metric-badge">Total</span>
            </div>
            <h3 class="metric-value metric-main">0,000</h3>
            <div class="metric-conversions"></div>
        </div>

        <div class="metric-card" data-metric="usage_30_days">
            <div class="metric-topline">
                <p class="metric-label">1 Bulan Terakhir</p>
                <span class="metric-badge">Total</span>
            </div>
            <h3 class="metric-value metric-main">0,000</h3>
            <div class="metric-conversions"></div>
        </div>
    </div>

    <div class="table-shell">
        <div class="table-toolbar">
            <div>
                <h4 class="section-title"><i class="fas fa-list-alt me-2"></i> Tabel Harian</h4>
            </div>
            <div class="table-actions">
                <label class="refresh-toggle">
                    <input type="checkbox" id="table_auto_refresh" checked>
                    <span class="refresh-switch"></span>
                    Auto refresh
                </label>
                <div class="input-group daterange-input">
                    <span class="input-group-text bg-white">
                        <i class="far fa-calendar-alt"></i>
                    </span>
                    <input type="text" class="form-control" id="daterange" placeholder="Pilih Rentang Tanggal">
                </div>
                <button type="button" class="btn btn-success d-inline-flex align-items-center" id="btn-export" style="background: #107c41; border-color: #107c41; font-weight: 800; font-size: 0.88rem; height: 38px; border-radius: 8px;">
                    <i class="fas fa-file-excel me-2"></i> Export Excel
                </button>
            </div>
        </div>

        <div class="daily-summary-card" id="daily-summary-card">
            <div class="daily-summary-info">
                <p class="daily-summary-title">Data Rangkuman Tabel Harian</p>
                <p class="daily-summary-range" id="daily-summary-range">Data dari - ke -</p>
            </div>
            <div class="daily-summary-stats">
                <div class="daily-summary-block" id="daily-summary-total-block">
                    <p class="daily-summary-label">Total Pemakaian</p>
                    <div class="daily-summary-main" id="daily-summary-main">0,000 m3</div>
                    <div id="daily-summary-conversions"></div>
                </div>
                <div class="daily-summary-block" id="daily-summary-avg-block">
                    <p class="daily-summary-label">Rata-rata Harian</p>
                    <div class="daily-summary-main" id="daily-summary-avg-main">0,000 m3</div>
                    <div id="daily-summary-avg-conversions"></div>
                </div>
            </div>
        </div>

        <div class="oxi-table-wrap">
            <table id="logTable" class="table table-hover align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 72px;">No</th>
                        <th>Tanggal</th>
                        <th>Total pemakaian</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@php
    $endpointUrls = $oximonitorUrls ?? [
        'metrics' => url('/admin/oximonitor/metrics'),
        'currentFlow' => url('/admin/oximonitor/current-flow'),
        'data' => url('/admin/oximonitor/data'),
        'export' => url('/admin/oximonitor/export'),
    ];
@endphp

<script src="{{ asset('assets/vendor/moment/moment.min.js') }}"></script>
<script src="{{ asset('assets/vendor/daterangepicker/daterangepicker.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/vendor/daterangepicker/daterangepicker.css') }}" />

<script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/vendor/datatables/dataTables.bootstrap5.min.css') }}" />

<script>
$(document).ready(function() {
    const endpointUrls = @json($endpointUrls);

    const units = {
        m3: { label: 'm3', factor: 1, decimals: 3 },
        galon: { label: 'Galon', factor: 0.2982, decimals: 3 },
        liter: { label: 'Liter', factor: 1.1288, decimals: 3 },
        kg: { label: 'Kg', factor: 1.2876, decimals: 3 }
    };

    let latestMetrics = {};
    let latestTableSummary = null;

    function getSelectedUnits() {
        const selected = $('#unit-options input:checked').map(function() {
            return this.value;
        }).get();

        return selected.length ? selected : ['m3'];
    }

    function formatNumber(value, decimals = 3) {
        const number = Number(value || 0);
        return number.toLocaleString('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    function convertValue(rawM3, unitKey) {
        const unit = units[unitKey] || units.m3;
        return Number(rawM3 || 0) * unit.factor;
    }

    function renderUnitValue(rawM3, unitKey) {
        const unit = units[unitKey] || units.m3;
        return `${formatNumber(convertValue(rawM3, unitKey), unit.decimals)} ${unit.label}`;
    }

    function renderMetricCard(metricKey, rawM3) {
        const selected = getSelectedUnits();
        const mainUnit = selected[0];
        const $card = $(`[data-metric="${metricKey}"]`);
        const $main = $card.find('.metric-main');
        const $badge = $card.find('.metric-badge');
        const $conversions = $card.find('.metric-conversions');

        $main.text(formatNumber(convertValue(rawM3, mainUnit), units[mainUnit].decimals));
        $badge.text(units[mainUnit].label);
        $conversions.empty();

        selected.slice(1).forEach(function(unitKey) {
            $conversions.append(`
                <div class="conversion-row">
                    <span>${units[unitKey].label}</span>
                    <strong>${formatNumber(convertValue(rawM3, unitKey), units[unitKey].decimals)}</strong>
                </div>
            `);
        });
    }

    function renderAllMetrics() {
        Object.keys(latestMetrics).forEach(function(metricKey) {
            renderMetricCard(metricKey, latestMetrics[metricKey]);
        });
    }

    function renderUsageStack(rawM3) {
        return `<div class="usage-stack">${getSelectedUnits().map(function(unitKey) {
            return `<span class="usage-pill">${renderUnitValue(rawM3, unitKey)}</span>`;
        }).join('')}</div>`;
    }

    function renderDailySummary(summary) {
        latestTableSummary = summary || latestTableSummary;

        if (!latestTableSummary) return;

        const selected = getSelectedUnits();
        const mainUnit = selected[0];
        const totalUsage = Number(latestTableSummary.total_usage || 0);
        const days = Number(latestTableSummary.days || 1);
        const avgUsage = totalUsage / (days > 0 ? days : 1);
        const startDate = latestTableSummary.start_date_label || '-';
        const endDate = latestTableSummary.end_date_label || '-';

        $('#daily-summary-range').text(`Data dari ${startDate} ke ${endDate}`);
        $('#daily-summary-main').text(renderUnitValue(totalUsage, mainUnit));

        $('#daily-summary-conversions').html(
            selected.length > 1
                ? `<div class="usage-stack">${selected.slice(1).map(function(unitKey) {
                    return `<span class="usage-pill">${renderUnitValue(totalUsage, unitKey)}</span>`;
                }).join('')}</div>`
                : ''
        );

        $('#daily-summary-avg-main').text(renderUnitValue(avgUsage, mainUnit));

        $('#daily-summary-avg-conversions').html(
            selected.length > 1
                ? `<div class="usage-stack">${selected.slice(1).map(function(unitKey) {
                    return `<span class="usage-pill">${renderUnitValue(avgUsage, unitKey)}</span>`;
                }).join('')}</div>`
                : ''
        );
    }

    function saveSelectedUnits() {
        localStorage.setItem('oximonitor_units', JSON.stringify(getSelectedUnits()));
    }

    function restoreSelectedUnits() {
        let savedUnits = ['m3'];

        try {
            savedUnits = JSON.parse(localStorage.getItem('oximonitor_units')) || ['m3'];
        } catch (e) {
            savedUnits = ['m3'];
        }

        if (!savedUnits.length) savedUnits = ['m3'];

        $('#unit-options input').each(function() {
            $(this).prop('checked', savedUnits.includes(this.value));
        });
    }

    restoreSelectedUnits();

    const savedTableAutoRefresh = localStorage.getItem('oximonitor_table_auto_refresh');
    if (savedTableAutoRefresh !== null) {
        $('#table_auto_refresh').prop('checked', savedTableAutoRefresh === '1');
    }

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

    var table = $('#logTable').DataTable({
        ajax: {
            url: endpointUrls.data,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: function(d) {
                var drp = $('#daterange').data('daterangepicker');
                d.startDate = drp.startDate.format('YYYY-MM-DD');
                d.endDate = drp.endDate.format('YYYY-MM-DD');
            },
            dataSrc: function(json) {
                renderDailySummary(json.summary);
                return json.data || [];
            }
        },
        processing: true,
        serverSide: true,
        searching: false,
        ordering: true,
        order: [[1, 'desc']],
        autoWidth: false,
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
            { data: 'no', orderable: false },
            { data: 'date', orderable: true },
            {
                data: 'usage_raw',
                orderable: false,
                render: function(data) {
                    return renderUsageStack(data);
                }
            }
        ]
    });

    $('#daterange').on('apply.daterangepicker', function() {
        table.ajax.reload(null, false);
    });

    $('#btn-export').on('click', function() {
        var drp = $('#daterange').data('daterangepicker');
        var startDate = drp.startDate.format('YYYY-MM-DD');
        var endDate = drp.endDate.format('YYYY-MM-DD');
        
        var url = endpointUrls.export + '?startDate=' + startDate + '&endDate=' + endDate;
        window.location.href = url;
    });

    $('#unit-options input').on('change', function() {
        if ($('#unit-options input:checked').length === 0) {
            $(this).prop('checked', true);
        }

        saveSelectedUnits();
        renderAllMetrics();
        renderDailySummary();
        table.rows().invalidate('data').draw(false);
    });

    $('#table_auto_refresh').on('change', function() {
        localStorage.setItem('oximonitor_table_auto_refresh', this.checked ? '1' : '0');
    });

    function updateMetrics() {
        $.ajax({
            url: endpointUrls.metrics,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                latestMetrics = data.raw || {};
                renderAllMetrics();
            },
            error: function(xhr) {
                console.error('Failed to fetch metrics:', xhr);
            }
        });
    }

    let flowInterval = localStorage.getItem('oximonitor_flow_interval') || 500;
    $('#flow_interval_input').val(flowInterval);

    let flowTimer;
    let isRequestPending = false;

    function updateCurrentFlow() {
        if (isRequestPending) return;

        let mockFlow = localStorage.getItem('oximonitor_mock_flow');
        if (mockFlow) {
            $('#current_flow').text(mockFlow);
            if (flowTimer) clearTimeout(flowTimer);
            flowTimer = setTimeout(updateCurrentFlow, flowInterval);
            return;
        }

        isRequestPending = true;
        $.ajax({
            url: endpointUrls.currentFlow,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#current_flow').text(data.current_flow);
            },
            error: function(xhr) {
                console.error('Failed to fetch current flow:', xhr);
            },
            complete: function() {
                isRequestPending = false;
                if (flowTimer) clearTimeout(flowTimer);
                flowTimer = setTimeout(updateCurrentFlow, flowInterval);
            }
        });
    }

    function startFlowTimer() {
        if (flowTimer) clearTimeout(flowTimer);
        isRequestPending = false;
        updateCurrentFlow();
    }

    $('#interval-toggle').on('click', function() {
        $('#interval-config').slideToggle(140);
    });

    $('#flow_interval_input').on('change', function() {
        let newVal = Number($(this).val());
        if (newVal >= 100) {
            flowInterval = newVal;
            localStorage.setItem('oximonitor_flow_interval', flowInterval);
            startFlowTimer();
        }
    });

    updateMetrics();
    startFlowTimer();

    setInterval(function() {
        updateMetrics();
        if ($('#table_auto_refresh').is(':checked')) {
            table.ajax.reload(null, false);
        }
    }, 2000);
});
</script>
@endsection
