<?php
include('../includes/auth.php');   // FIRST
include('../config/db.php');
include('../includes/header.php');
?>


<div class="page-header">
    <h1><i class="fas fa-calendar-alt"></i> Monthly Reports</h1>
    <div class="header-actions">
        <button onclick="window.print()" class="btn-modern btn-primary-modern">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<!-- Visits per Month (Last 6 Months) -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-chart-line"></i> Visits Per Month (Last 6 Months)</h4>
    </div>

    <?php
    $q1 = "
    SELECT DATE_FORMAT(visit_date, '%Y-%m') month,
    DATE_FORMAT(visit_date, '%M %Y') month_name,
    COUNT(*) total
    FROM visits
    WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month DESC
    ";
    $r1 = $conn->query($q1);
    ?>

    <div class="row">
        <?php 
        $colors = [
            'var(--primary-gradient)',
            'var(--success-gradient)',
            'var(--info-gradient)',
            'var(--warning-gradient)',
            'var(--danger-gradient)',
            'var(--primary-gradient)'
        ];
        $index = 0;
        while($row=$r1->fetch_assoc()){ 
        ?>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: <?= $colors[$index % count($colors)] ?>;">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $row['total'] ?></h3>
                    <p><?= $row['month_name'] ?></p>
                </div>
            </div>
        </div>
        <?php 
        $index++;
        } 
        ?>
    </div>

    <?php if($r1->num_rows == 0): ?>
    <div class="text-center py-5">
        <i class="fas fa-chart-line" style="font-size: 4rem; color: #ddd;"></i>
        <h5 class="mt-3 text-muted">No visits in the last 6 months</h5>
    </div>
    <?php endif; ?>
</div>

<!-- Patients Joined per Month -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-user-plus"></i> Patients Joined Per Month</h4>
    </div>

    <?php
    $q2 = "
    SELECT DATE_FORMAT(join_date, '%Y-%m') month,
    DATE_FORMAT(join_date, '%M %Y') month_name,
    COUNT(*) total
    FROM patients
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
    ";
    $r2 = $conn->query($q2);
    ?>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>New Patients</th>
                    <th>Visual</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $max_patients = $conn->query("SELECT MAX(c) as max FROM (SELECT COUNT(*) as c FROM patients GROUP BY DATE_FORMAT(join_date, '%Y-%m')) as counts")->fetch_assoc()['max'];
            while($row=$r2->fetch_assoc()){ 
                $percentage = $max_patients > 0 ? ($row['total'] / $max_patients) * 100 : 0;
            ?>
            <tr>
                <td><strong><?= $row['month_name'] ?></strong></td>
                <td><span class="badge-modern badge-success"><?= $row['total'] ?> patients</span></td>
                <td>
                    <div class="progress" style="height: 25px; border-radius: 12px;">
                        <div class="progress-bar" style="width: <?= $percentage ?>%; background: var(--success-gradient);" role="progressbar">
                            <?= $row['total'] ?>
                        </div>
                    </div>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Visits by Patient Join Month -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-chart-bar"></i> Visits Grouped by Patient Join Month</h4>
    </div>

    <?php
    $q3 = "
    SELECT DATE_FORMAT(p.join_date,'%Y-%m') join_month,
    DATE_FORMAT(p.join_date,'%M %Y') join_month_name,
    COUNT(v.visit_id) total_visits,
    COUNT(DISTINCT p.patient_id) patient_count
    FROM patients p
    LEFT JOIN visits v ON p.patient_id = v.patient_id
    GROUP BY join_month
    ORDER BY join_month DESC
    LIMIT 12
    ";
    $r3 = $conn->query($q3);
    ?>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Join Month</th>
                    <th>Patients</th>
                    <th>Total Visits</th>
                    <th>Avg Visits per Patient</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row=$r3->fetch_assoc()){ 
                $avg = $row['patient_count'] > 0 ? round($row['total_visits'] / $row['patient_count'], 1) : 0;
            ?>
            <tr>
                <td><strong><?= $row['join_month_name'] ?></strong></td>
                <td><span class="badge-modern badge-info"><?= $row['patient_count'] ?></span></td>
                <td><span class="badge-modern badge-success"><?= $row['total_visits'] ?></span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-modern badge-primary"><?= $avg ?></span>
                        <div class="progress flex-grow-1" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar" style="width: <?= min($avg * 10, 100) ?>%; background: var(--primary-gradient);" role="progressbar"></div>
                        </div>
                    </div>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Monthly Trends -->
<div class="row">
    <div class="col-md-6">
        <div class="modern-card" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);">
            <h5><i class="fas fa-trending-up"></i> This Month's Performance</h5>
            <?php
            $this_month = $conn->query("
                SELECT COUNT(*) as visits 
                FROM visits 
                WHERE MONTH(visit_date) = MONTH(CURDATE()) 
                AND YEAR(visit_date) = YEAR(CURDATE())
            ")->fetch_assoc();
            
            $last_month = $conn->query("
                SELECT COUNT(*) as visits 
                FROM visits 
                WHERE MONTH(visit_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                AND YEAR(visit_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            ")->fetch_assoc();
            
            $change = $last_month['visits'] > 0 ? 
                round((($this_month['visits'] - $last_month['visits']) / $last_month['visits']) * 100, 1) : 0;
            ?>
            <h3 class="mb-2"><?= $this_month['visits'] ?> visits</h3>
            <p class="mb-0">
                <?php if($change > 0): ?>
                    <span class="badge-modern badge-success">
                        <i class="fas fa-arrow-up"></i> +<?= $change ?>% vs last month
                    </span>
                <?php elseif($change < 0): ?>
                    <span class="badge-modern badge-danger">
                        <i class="fas fa-arrow-down"></i> <?= $change ?>% vs last month
                    </span>
                <?php else: ?>
                    <span class="badge-modern badge-info">
                        <i class="fas fa-minus"></i> No change vs last month
                    </span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="modern-card" style="background: linear-gradient(135deg, #11998e15 0%, #38ef7d15 100%);">
            <h5><i class="fas fa-users"></i> Patient Growth</h5>
            <?php
            $new_this_month = $conn->query("
                SELECT COUNT(*) as count 
                FROM patients 
                WHERE MONTH(join_date) = MONTH(CURDATE()) 
                AND YEAR(join_date) = YEAR(CURDATE())
            ")->fetch_assoc();
            
            $total = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc();
            ?>
            <h3 class="mb-2"><?= $new_this_month['count'] ?> new patients</h3>
            <p class="mb-0">
                <span class="badge-modern badge-info">
                    Total: <?= $total['count'] ?> patients
                </span>
            </p>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>