@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    .bg-gradient-primary-to-secondary {
        background: linear-gradient(45deg, #4e73df 0%, #224abe 100%);
    }
    .bg-gradient-info-to-teal {
        background: linear-gradient(45deg, #36b9cc 0%, #258391 100%);
    }
    .bg-gradient-success-to-green {
        background: linear-gradient(45deg, #1cc88a 0%, #13855c 100%);
    }
    .card-dashboard {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transition: transform 0.2s;
        color: white;
    }
    .card-dashboard:hover {
        transform: translateY(-5px);
    }
    .text-xs {
        font-size: .7rem;
    }
    .fw-bold {
        font-weight: 700!important;
    }
    .chart-area {
        position: relative;
        height: 20rem;
        width: 100%;
    }
    .card-header-clean {
        background-color: transparent;
        border-bottom: 1px solid rgba(0,0,0,.05);
        padding: 1.5rem;
    }
    .recent-activity-item {
        border-left: 2px solid #e3e6f0;
        padding-left: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }
    .recent-activity-item::before {
        content: '';
        position: absolute;
        left: -5px;
        top: 6px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #4e73df;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #e3e6f0;
    }
    .recent-activity-item.call::before { background-color: #e74a3b; }
    .recent-activity-item.message::before { background-color: #36b9cc; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 pt-3">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
    <!-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Generate Report</a> -->
</div>

<!-- Stats Row -->
<div class="row">
    <!-- Calls Today -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card card-dashboard bg-gradient-primary-to-secondary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-uppercase mb-1" style="opacity: 0.8;">
                            Total Panggilan (Hari Ini)</div>
                        <div class="h2 mb-0 fw-bold">{{ $totalCallsToday }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-phone fa-2x text-gray-300" style="opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Today -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card card-dashboard bg-gradient-info-to-teal h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-uppercase mb-1" style="opacity: 0.8;">
                            Total Pesan (Hari Ini)</div>
                        <div class="h2 mb-0 fw-bold">{{ $totalMessagesToday }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-envelope fa-2x text-gray-300" style="opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Rooms -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card card-dashboard bg-gradient-success-to-green h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-uppercase mb-1" style="opacity: 0.8;">
                            Total Kamar Terdaftar</div>
                        <div class="h2 mb-0 fw-bold">{{ $totalRooms }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bed fa-2x text-gray-300" style="opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Area Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4" style="border-radius: 1rem; border: none;">
            <div class="card-header-clean d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Statistik 7 Hari Terakhir</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4" style="border-radius: 1rem; border: none;">
            <div class="card-header-clean d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terbaru</h6>
            </div>
            <div class="card-body">
                @if($recentActivity->count() > 0)
                    @foreach($recentActivity as $activity)
                        <div class="recent-activity-item {{ $activity->type }}">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ \Carbon\Carbon::parse($activity->timestamp)->format('H:i') }}
                                <span class="badge {{ $activity->type == 'call' ? 'bg-danger' : 'bg-info' }} ms-1">
                                    {{ $activity->type == 'call' ? 'Panggilan' : 'Pesan' }}
                                </span>
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                @if($activity->type == 'call')
                                    Device/Bed ID: {{ $activity->bed_id }}
                                @else
                                    {{ \Illuminate\Support\Str::limit($activity->value, 30) }}
                                @endif
                            </div>
                            <small class="text-muted">
                                @if($activity->type == 'call')
                                    {{ $activity->category ? $activity->category->name : 'Unknown Category' }}
                                @else
                                    {{ $activity->category ? $activity->category->name : 'Unknown Category' }}
                                @endif
                            </small>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-muted my-5">Belum ada aktivitas hari ini.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.font.family = 'Nunito, -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.color = '#858796';

    const ctx = document.getElementById("myAreaChart");
    var myLineChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: {!! json_encode($dates) !!},
        datasets: [{
          label: "Panggilan",
          lineTension: 0.3,
          backgroundColor: "rgba(231, 74, 59, 0.05)",
          borderColor: "rgba(231, 74, 59, 1)",
          pointRadius: 3,
          pointBackgroundColor: "rgba(231, 74, 59, 1)",
          pointBorderColor: "rgba(231, 74, 59, 1)",
          pointHoverRadius: 3,
          pointHoverBackgroundColor: "rgba(231, 74, 59, 1)",
          pointHoverBorderColor: "rgba(231, 74, 59, 1)",
          pointHitRadius: 10,
          pointBorderWidth: 2,
          data: {!! json_encode($callsData) !!},
          fill: true,
        },
        {
          label: "Pesan",
          lineTension: 0.3,
          backgroundColor: "rgba(54, 185, 204, 0.05)",
          borderColor: "rgba(54, 185, 204, 1)",
          pointRadius: 3,
          pointBackgroundColor: "rgba(54, 185, 204, 1)",
          pointBorderColor: "rgba(54, 185, 204, 1)",
          pointHoverRadius: 3,
          pointHoverBackgroundColor: "rgba(54, 185, 204, 1)",
          pointHoverBorderColor: "rgba(54, 185, 204, 1)",
          pointHitRadius: 10,
          pointBorderWidth: 2,
          data: {!! json_encode($messagesData) !!},
          fill: true,
        }],
      },
      options: {
        maintainAspectRatio: false,
        layout: {
          padding: {
            left: 10,
            right: 25,
            top: 25,
            bottom: 0
          }
        },
        scales: {
          x: {
            grid: {
              display: false,
              drawBorder: false
            },
            ticks: {
              maxTicksLimit: 7
            }
          },
          y: {
            ticks: {
              maxTicksLimit: 5,
              padding: 10,
              callback: function(value, index, values) {
                return value;
              }
            },
            grid: {
              color: "rgb(234, 236, 244)",
              zeroLineColor: "rgb(234, 236, 244)",
              drawBorder: false,
              borderDash: [2],
              zeroLineBorderDash: [2]
            }
          },
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                backgroundColor: "rgb(255,255,255)",
                bodyColor: "#858796",
                titleMarginBottom: 10,
                titleColor: '#6e707e',
                titleFont: {
                  size: 14
                },
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
            }
        }
      }
    });
</script>
@endsection
