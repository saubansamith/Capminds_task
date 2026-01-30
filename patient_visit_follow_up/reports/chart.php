<?php
include('../includes/auth.php');   // FIRST
include('../config/db.php');
include('../includes/header.php');

$q = "
SELECT DATE_FORMAT(visit_date,'%Y-%m') AS month,
DATE_FORMAT(visit_date,'%b %Y') AS month_label,
COUNT(*) AS total
FROM visits
GROUP BY month
ORDER BY month
";

$res = $conn->query($q);

$months = [];
$counts = [];

while($row = $res->fetch_assoc()){
    $months[] = $row['month_label'];
    $counts[] = $row['total'];
}
?>

<div class="page-header">
    <h1><i class="fas fa-chart-bar"></i> Visit Analytics</h1>
    <div class="header-actions">
        <button onclick="window.print()" class="btn-modern btn-primary-modern">
            <i class="fas fa-print"></i> Print Chart
        </button>
    </div>
</div>

<!-- Stats Overview -->
<div class="row mb-4">
    <?php
    $total_visits = array_sum($counts);
    $avg_per_month = count($counts) > 0 ? round($total_visits / count($counts), 1) : 0;
    $peak_month = count($counts) > 0 ? $months[array_search(max($counts), $counts)] : 'N/A';
    ?>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-gradient);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3><?= $total_visits ?></h3>
                <p>Total Visits</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-gradient);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3><?= $avg_per_month ?></h3>
                <p>Avg Visits/Month</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--warning-gradient);">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content">
                <h3 style="font-size: 1.3rem;"><?= $peak_month ?></h3>
                <p>Peak Month</p>
            </div>
        </div>
    </div>
</div>

<!-- Bar Chart -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-chart-bar"></i> Monthly Visits - Bar Chart</h4>
    </div>
    
    <canvas id="visitChart" style="max-height: 400px;"></canvas>
</div>

<!-- Line Chart -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-chart-line"></i> Visit Trends - Line Chart</h4>
    </div>
    
    <canvas id="trendChart" style="max-height: 400px;"></canvas>
</div>

<!-- Data Table -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-table"></i> Monthly Breakdown</h4>
    </div>
    
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Total Visits</th>
                    <th>Percentage</th>
                    <th>Visual</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $res = $conn->query($q); // Reset pointer
            $max_visits = max($counts);
            while($row = $res->fetch_assoc()): 
                $percentage = $total_visits > 0 ? round(($row['total'] / $total_visits) * 100, 1) : 0;
                $bar_width = $max_visits > 0 ? round(($row['total'] / $max_visits) * 100) : 0;
            ?>
            <tr>
                <td><strong><?= $row['month_label'] ?></strong></td>
                <td><span class="badge-modern badge-success"><?= $row['total'] ?> visits</span></td>
                <td><?= $percentage ?>%</td>
                <td>
                    <div class="progress" style="height: 25px; border-radius: 12px;">
                        <div class="progress-bar" style="width: <?= $bar_width ?>%; background: var(--primary-gradient);" role="progressbar">
                            <?= $row['total'] ?>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gradient colors
const gradient1 = document.createElement('canvas').getContext('2d').createLinearGradient(0, 0, 0, 400);
gradient1.addColorStop(0, 'rgba(102, 126, 234, 0.8)');
gradient1.addColorStop(1, 'rgba(118, 75, 162, 0.8)');

const gradient2 = document.createElement('canvas').getContext('2d').createLinearGradient(0, 0, 0, 400);
gradient2.addColorStop(0, 'rgba(17, 153, 142, 0.8)');
gradient2.addColorStop(1, 'rgba(56, 239, 125, 0.8)');

// Bar Chart
const ctx1 = document.getElementById('visitChart');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Visits Per Month',
            data: <?= json_encode($counts) ?>,
            backgroundColor: gradient1,
            borderColor: '#667eea',
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                labels: {
                    font: {
                        size: 14,
                        family: 'Inter'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 12,
                titleFont: {
                    size: 14,
                    family: 'Inter'
                },
                bodyFont: {
                    size: 13,
                    family: 'Inter'
                },
                borderColor: '#667eea',
                borderWidth: 1
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        family: 'Inter'
                    }
                },
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                }
            },
            x: {
                ticks: {
                    font: {
                        family: 'Inter'
                    }
                },
                grid: {
                    display: false
                }
            }
        }
    }
});

// Line Chart
const ctx2 = document.getElementById('trendChart');
new Chart(ctx2, {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Visit Trend',
            data: <?= json_encode($counts) ?>,
            backgroundColor: 'rgba(17, 153, 142, 0.1)',
            borderColor: '#11998e',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#11998e',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                labels: {
                    font: {
                        size: 14,
                        family: 'Inter'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 12,
                titleFont: {
                    size: 14,
                    family: 'Inter'
                },
                bodyFont: {
                    size: 13,
                    family: 'Inter'
                },
                borderColor: '#11998e',
                borderWidth: 1
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        family: 'Inter'
                    }
                },
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                }
            },
            x: {
                ticks: {
                    font: {
                        family: 'Inter'
                    }
                },
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>

<?php include('../includes/footer.php'); ?>