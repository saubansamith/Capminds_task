<?php 
session_start();
include('includes/auth.php');
include('config/db.php'); 

// Get user info
$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';

include('includes/header.php'); 
?>

<!-- Welcome Banner -->
<div class="page-header" style="background: var(--primary-gradient); color: white; border: none;">
    <div>
        <h1 style="color: white; margin: 0;">
            <i class="fas fa-chart-line"></i> Dashboard Overview
        </h1>
        <p style="opacity: 0.9; margin: 0.5rem 0 0 0;">
            Welcome back, <strong><?= htmlspecialchars($username) ?></strong>! Here's your system summary.
        </p>
    </div>
    <div style="font-size: 3rem; opacity: 0.3;">
        <?php
        $role_icons = [
            'admin' => 'fa-user-shield',
            'doctor' => 'fa-user-md',
            'receptionist' => 'fa-user-tie'
        ];
        ?>
        <i class="fas <?= $role_icons[$role] ?? 'fa-user' ?>"></i>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-gradient);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <?php
                $total_patients = $conn->query("SELECT COUNT(*) c FROM patients")->fetch_assoc()['c'];
                ?>
                <h3><?= $total_patients ?></h3>
                <p>Total Patients</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-gradient);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <?php
                $total_visits = $conn->query("SELECT COUNT(*) c FROM visits")->fetch_assoc()['c'];
                ?>
                <h3><?= $total_visits ?></h3>
                <p>Total Visits</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--danger-gradient);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <?php
                $overdue = $conn->query("SELECT COUNT(*) c FROM visits 
                                       WHERE follow_up_due < CURDATE()")->fetch_assoc()['c'];
                ?>
                <h3><?= $overdue ?></h3>
                <p>Overdue Follow-ups</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--warning-gradient);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <?php
                $upcoming = $conn->query("SELECT COUNT(*) c FROM visits 
                                        WHERE follow_up_due BETWEEN CURDATE() 
                                        AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['c'];
                ?>
                <h3><?= $upcoming ?></h3>
                <p>Upcoming Follow-ups</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Recent Activity -->
    <div class="col-lg-8 mb-4">
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-history"></i> Recent Visits</h4>
                <a href="visits/list.php" class="btn-modern btn-info-modern btn-sm">
                    View All
                </a>
            </div>

            <?php
            $recent_visits = $conn->query("
                SELECT v.*, p.name as patient_name
                FROM visits v
                JOIN patients p ON v.patient_id = p.patient_id
                ORDER BY v.visit_date DESC
                LIMIT 10
            ");
            ?>

            <?php if ($recent_visits->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Visit Date</th>
                            <th>Fees</th>
                            <th>Follow-up Due</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($visit = $recent_visits->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                                    <?= strtoupper(substr($visit['patient_name'], 0, 1)) ?>
                                </div>
                                <strong><?= htmlspecialchars($visit['patient_name']) ?></strong>
                            </div>
                        </td>
                        <td>
                            <?= date('M d, Y', strtotime($visit['visit_date'])) ?>
                            <br>
                            <small class="text-muted"><?= date('l', strtotime($visit['visit_date'])) ?></small>
                        </td>
                        <td>
                            <strong>$<?= number_format($visit['consultation_fee'] + $visit['lab_fee'], 2) ?></strong>
                        </td>
                        <td>
                            <?php if ($visit['follow_up_due']): ?>
                            <?= date('M d, Y', strtotime($visit['follow_up_due'])) ?>
                            <?php else: ?>
                            <span class="text-muted">Not set</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status = 'Normal';
                            $badge_class = 'badge-success';
                            
                            if ($visit['follow_up_due']) {
                                if (strtotime($visit['follow_up_due']) < time()) {
                                    $status = 'Overdue';
                                    $badge_class = 'badge-danger';
                                } elseif (strtotime($visit['follow_up_due']) <= strtotime('+7 days')) {
                                    $status = 'Upcoming';
                                    $badge_class = 'badge-warning';
                                }
                            }
                            ?>
                            <span class="badge-modern <?= $badge_class ?>">
                                <?= $status ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ddd;"></i>
                <h5 class="mt-3 text-muted">No recent visits</h5>
            </div>
            <?php endif; ?>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-chart-line"></i> Visit Trends (Last 6 Months)</h4>
            </div>

            <?php
            $trend_data = $conn->query("
                SELECT DATE_FORMAT(visit_date, '%b %Y') as month,
                COUNT(*) as count
                FROM visits
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
                ORDER BY DATE_FORMAT(visit_date, '%Y-%m')
            ");

            $months = [];
            $counts = [];
            while($row = $trend_data->fetch_assoc()) {
                $months[] = $row['month'];
                $counts[] = $row['count'];
            }
            ?>

            <canvas id="trendChart" style="max-height: 350px;"></canvas>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-bolt"></i> Quick Actions</h4>
            </div>

            <div class="d-flex flex-column gap-2">
                <a href="patients/add.php" class="btn-modern btn-success-modern w-100">
                    <i class="fas fa-user-plus"></i> Add New Patient
                </a>
                <a href="visits/add.php" class="btn-modern btn-primary-modern w-100">
                    <i class="fas fa-calendar-plus"></i> Record Visit
                </a>
                <a href="patients/list.php" class="btn-modern btn-info-modern w-100">
                    <i class="fas fa-users"></i> View All Patients
                </a>
                <a href="reports/followups.php" class="btn-modern btn-warning-modern w-100">
                    <i class="fas fa-clock"></i> Manage Follow-ups
                </a>
            </div>
        </div>

        <!-- Upcoming Birthdays -->
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-birthday-cake"></i> Upcoming Birthdays</h4>
                <a href="reports/birthdays.php" class="btn-modern btn-info-modern btn-sm">
                    View All
                </a>
            </div>

            <?php
            $upcoming_birthdays = $conn->query("
                SELECT name, dob, TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age
                FROM patients
                WHERE DAYOFYEAR(
                    STR_TO_DATE(
                        CONCAT(YEAR(CURDATE()), '-', MONTH(dob), '-', DAY(dob)),
                        '%Y-%m-%d'
                    )
                ) BETWEEN DAYOFYEAR(CURDATE()) 
                AND DAYOFYEAR(DATE_ADD(CURDATE(), INTERVAL 7 DAY))
                ORDER BY MONTH(dob), DAY(dob)
                LIMIT 5
            ");
            ?>

            <?php if ($upcoming_birthdays->num_rows > 0): ?>
            <div class="d-flex flex-column gap-2">
                <?php while($bday = $upcoming_birthdays->fetch_assoc()): ?>
                <div class="p-3" style="background: linear-gradient(135deg, #f093fb15 0%, #f5576c15 100%); border-radius: 12px; border-left: 4px solid #f093fb;">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--warning-gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="flex-grow-1">
                            <strong><?= htmlspecialchars($bday['name']) ?></strong>
                            <br>
                            <small class="text-muted">
                                <?= date('M d', strtotime($bday['dob'])) ?> • Turning <?= $bday['age'] + 1 ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-birthday-cake" style="font-size: 2.5rem; color: #ddd;"></i>
                <p class="text-muted mb-0 mt-2">No birthdays this week</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- System Stats -->
        <div class="modern-card" style="background: linear-gradient(135deg, #11998e15 0%, #38ef7d15 100%);">
            <h5><i class="fas fa-heartbeat"></i> System Health</h5>
            
            <?php
            $active_patients = $conn->query("
                SELECT COUNT(DISTINCT p.patient_id) as count 
                FROM patients p 
                INNER JOIN visits v ON p.patient_id = v.patient_id 
                WHERE v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            ")->fetch_assoc()['count'];

            $avg_visits = $total_patients > 0 ? round($total_visits / $total_patients, 1) : 0;

            $today_visits = $conn->query("
                SELECT COUNT(*) as count 
                FROM visits 
                WHERE visit_date = CURDATE()
            ")->fetch_assoc()['count'];
            ?>

            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span><i class="fas fa-user-check"></i> Active Patients (90d)</span>
                <strong style="color: #11998e;"><?= $active_patients ?></strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span><i class="fas fa-chart-bar"></i> Avg Visits/Patient</span>
                <strong style="color: #11998e;"><?= $avg_visits ?></strong>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-day"></i> Today's Visits</span>
                <strong style="color: #11998e;"><?= $today_visits ?></strong>
            </div>
        </div>

        <!-- Alert Card -->
        <?php if ($overdue > 0): ?>
        <div class="modern-card" style="background: linear-gradient(135deg, #ee097915 0%, #ff6a0015 100%); border-left: 4px solid #ee0979;">
            <h5><i class="fas fa-exclamation-triangle"></i> Attention Required</h5>
            <p class="mb-2">You have <strong><?= $overdue ?></strong> overdue follow-ups that need attention.</p>
            <a href="reports/followups.php" class="btn-modern btn-danger-modern btn-sm">
                <i class="fas fa-eye"></i> View Details
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Trend Chart
const ctx = document.getElementById('trendChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Visits',
            data: <?= json_encode($counts) ?>,
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderColor: '#667eea',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#667eea',
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
                display: false
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
</script>

<?php include('includes/footer.php'); ?>