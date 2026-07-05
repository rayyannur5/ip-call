@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    /* Premium Dashboard Styles (Keeping original layout background) */
    .dashboard-title {
        font-weight: 800;
        font-size: 1.85rem;
        letter-spacing: -0.5px;
        color: #1e293b;
    }

    /* Light Glassmorphism Cards matching the Login quality but on Light BG */
    .card-glass {
        background: rgba(255, 255, 255, 0.75) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
    }

    .card-glass:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    }

    /* Glowing Stats Card Decor */
    .card-stat {
        border-left: 4px solid var(--accent-color) !important;
    }

    .stat-icon-wrapper {
        width: 54px;
        height: 54px;
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.04);
    }

    .stat-icon-wrapper i {
        font-size: 22px;
        color: var(--accent-color);
    }

    .stat-label {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #475569;
        margin-bottom: 0.25rem;
    }

    .stat-number {
        font-size: 2.25rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: #0f172a;
    }

    /* Clean Card Headers */
    .card-header-premium {
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }

    .card-header-premium h6 {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.3px;
        margin: 0;
    }

    .chart-area {
        position: relative;
        height: 20rem;
        width: 100%;
    }

    /* Recent Activity Feed */
    .activity-feed {
        padding: 5px 0;
    }

    .activity-item {
        position: relative;
        padding-left: 32px;
        padding-bottom: 1.25rem;
        border-left: 2px solid rgba(0, 0, 0, 0.05);
    }

    .activity-item:last-child {
        padding-bottom: 0;
        border-left: 2px solid transparent;
    }

    .activity-badge {
        position: absolute;
        left: -8px;
        top: 2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 1px rgba(0,0,0,0.05);
        z-index: 5;
    }

    .activity-item.call .activity-badge {
        background-color: #ef4444;
        box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
    }

    .activity-item.message .activity-badge {
        background-color: #0ea5e9;
        box-shadow: 0 0 8px rgba(14, 165, 233, 0.4);
    }

    .activity-time {
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 0.15rem;
    }

    .activity-desc {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
    }

    .activity-meta {
        font-size: 0.775rem;
        font-weight: 500;
        color: #475569;
        margin-top: 0.1rem;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 pt-3 dashboard-header">
    <h1 class="dashboard-title">Dashboard Overview</h1>
</div>

<!-- Stats Row -->
<div class="row">
    <!-- Calls Today -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card card-glass card-stat h-100 py-3" style="--accent-color: #ef4444;">
            <div class="card-body py-2">
                <div class="row align-items-center no-gutters">
                    <div class="col mr-2">
                        <div class="stat-label">Total Panggilan (Hari Ini)</div>
                        <div class="stat-number">{{ $totalCallsToday }}</div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-phone"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Today -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card card-glass card-stat h-100 py-3" style="--accent-color: #0ea5e9;">
            <div class="card-body py-2">
                <div class="row align-items-center no-gutters">
                    <div class="col mr-2">
                        <div class="stat-label">Total Pesan (Hari Ini)</div>
                        <div class="stat-number">{{ $totalMessagesToday }}</div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Rooms -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card card-glass card-stat h-100 py-3" style="--accent-color: #6366f1;">
            <div class="card-body py-2">
                <div class="row align-items-center no-gutters">
                    <div class="col mr-2">
                        <div class="stat-label">Total Kamar Terdaftar</div>
                        <div class="stat-number">{{ $totalRooms }}</div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-bed"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Area Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card card-glass mb-4">
            <div class="card-header-premium d-flex flex-row align-items-center justify-content-between">
                <h6><i class="fas fa-chart-line text-primary me-2"></i>Statistik 7 Hari Terakhir</h6>
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
        <div class="card card-glass mb-4">
            <div class="card-header-premium d-flex flex-row align-items-center justify-content-between">
                <h6><i class="fas fa-history text-primary me-2"></i>Aktivitas Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                    @if($recentActivity->count() > 0)
                        @foreach($recentActivity as $activity)
                            <div class="activity-item {{ $activity->type }}">
                                <div class="activity-badge"></div>
                                <div class="activity-time">
                                    <i class="far fa-clock"></i>
                                    {{ \Carbon\Carbon::parse($activity->timestamp)->format('H:i') }}
                                    <span class="badge {{ $activity->type == 'call' ? 'bg-danger' : 'bg-info' }} rounded-pill ms-1" style="font-size: 0.65rem; font-weight: 700;">
                                        {{ $activity->type == 'call' ? 'Panggilan' : 'Pesan' }}
                                    </span>
                                </div>
                                <div class="activity-desc">
                                    @if($activity->type == 'call')
                                        {{ $activity->bed->username ?? '-' }}
                                        <span class="text-muted fw-normal" style="font-size: 0.85rem;">
                                            ({{ \Illuminate\Support\Str::limit($activity->duration, 30) }})
                                        </span>
                                    @else
                                        {{ $activity->bed->username ?? $activity->toilet->username ?? '-' }}
                                    @endif
                                </div>
                                <div class="activity-meta">
                                    <i class="fas fa-tag me-1" style="font-size: 0.7rem; opacity: 0.7;"></i>
                                    {{ $activity->category ? $activity->category->name : 'Unknown Category' }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted my-5">Belum ada aktivitas hari ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Use system font stack
    Chart.defaults.font.family = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.color = '#475569';

    const ctx = document.getElementById("myAreaChart");
    var myLineChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: {!! json_encode($dates) !!},
        datasets: [{
          label: "Panggilan",
          lineTension: 0.3,
          backgroundColor: "rgba(239, 68, 68, 0.05)",
          borderColor: "rgba(239, 68, 68, 1)",
          pointRadius: 4,
          pointBackgroundColor: "rgba(239, 68, 68, 1)",
          pointBorderColor: "#fff",
          pointBorderWidth: 2,
          pointHoverRadius: 6,
          pointHoverBackgroundColor: "rgba(239, 68, 68, 1)",
          pointHoverBorderColor: "#fff",
          pointHoverBorderWidth: 2,
          pointHitRadius: 10,
          data: {!! json_encode($callsData) !!},
          fill: true,
        },
        {
          label: "Pesan",
          lineTension: 0.3,
          backgroundColor: "rgba(14, 165, 233, 0.05)",
          borderColor: "rgba(14, 165, 233, 1)",
          pointRadius: 4,
          pointBackgroundColor: "rgba(14, 165, 233, 1)",
          pointBorderColor: "#fff",
          pointBorderWidth: 2,
          pointHoverRadius: 6,
          pointHoverBackgroundColor: "rgba(14, 165, 233, 1)",
          pointHoverBorderColor: "#fff",
          pointHoverBorderWidth: 2,
          pointHitRadius: 10,
          data: {!! json_encode($messagesData) !!},
          fill: true,
        }],
      },
      options: {
        maintainAspectRatio: false,
        layout: {
          padding: {
            left: 5,
            right: 20,
            top: 20,
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
              maxTicksLimit: 7,
              color: '#475569',
              font: {
                weight: '600'
              }
            }
          },
          y: {
            ticks: {
              maxTicksLimit: 5,
              padding: 10,
              color: '#475569',
              font: {
                weight: '600'
              },
              callback: function(value, index, values) {
                return value;
              }
            },
            grid: {
              color: "rgba(0, 0, 0, 0.05)",
              zeroLineColor: "rgba(0, 0, 0, 0.05)",
              drawBorder: false,
              borderDash: [4, 4],
              zeroLineBorderDash: [4, 4]
            }
          },
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    boxWidth: 12,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    color: '#475569',
                    font: {
                        weight: '700'
                    }
                }
            },
            tooltip: {
                backgroundColor: "rgba(30, 41, 59, 0.95)",
                titleColor: "#fff",
                bodyColor: "#cbd5e1",
                titleMarginBottom: 8,
                titleFont: {
                  size: 13,
                  weight: '700'
                },
                bodyFont: {
                  size: 13,
                  weight: '600'
                },
                borderColor: 'rgba(0, 0, 0, 0.05)',
                borderWidth: 1,
                padding: 12,
                displayColors: true,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
            }
        }
      }
    });
</script>
@endsection
