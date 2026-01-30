<?php

include('../includes/auth.php');   // FIRST
include('../config/db.php');
include('../includes/header.php');
?>

<div class="page-header">
    <h1><i class="fas fa-birthday-cake"></i> Birthday Report</h1>
    <div class="header-actions">
        <button onclick="window.print()" class="btn-modern btn-primary-modern">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<!-- Birthdays in Next 30 Days -->
<?php
$q1 = "
SELECT 
name, 
dob,
patient_id,
TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age,
DATEDIFF(
    STR_TO_DATE(
        CONCAT(YEAR(CURDATE()), '-', MONTH(dob), '-', DAY(dob)),
        '%Y-%m-%d'
    ),
    CURDATE()
) AS days_until
FROM patients
WHERE 
DAYOFYEAR(
    STR_TO_DATE(
        CONCAT(YEAR(CURDATE()), '-', MONTH(dob), '-', DAY(dob)),
        '%Y-%m-%d'
    )
)
BETWEEN DAYOFYEAR(CURDATE()) 
AND DAYOFYEAR(DATE_ADD(CURDATE(), INTERVAL 30 DAY))
ORDER BY days_until, MONTH(dob), DAY(dob)
";

$r1 = $conn->query($q1);
$upcoming_count = $r1->num_rows;
?>

<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-calendar-day"></i> Upcoming Birthdays (Next 30 Days)</h4>
        <span class="badge-modern badge-success"><?= $upcoming_count ?> birthdays</span>
    </div>

    <?php if($upcoming_count > 0): ?>
    <div class="row">
        <?php while($row = $r1->fetch_assoc()){ ?>
        <div class="col-md-6 mb-3">
            <div class="p-3" style="background: linear-gradient(135deg, #f093fb15 0%, #f5576c15 100%); border-radius: 12px; border-left: 4px solid #f093fb;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--warning-gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><?= htmlspecialchars($row['name']) ?></h6>
                        <small class="text-muted">
                            <?= date('F d', strtotime($row['dob'])) ?> • Turning <?= $row['age'] + 1 ?>
                        </small>
                        <br>
                        <span class="badge-modern badge-warning mt-1">
                            <?php
                            if($row['days_until'] == 0) echo "Today!";
                            elseif($row['days_until'] == 1) echo "Tomorrow";
                            else echo "In " . $row['days_until'] . " days";
                            ?>
                        </span>
                    </div>
                    <a href="../patients/view.php?id=<?= $row['patient_id'] ?>" class="btn-modern btn-info-modern btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-birthday-cake" style="font-size: 4rem; color: #ddd;"></i>
        <h5 class="mt-3 text-muted">No birthdays in the next 30 days</h5>
    </div>
    <?php endif; ?>
</div>

<!-- Milestone Birthdays -->
<?php
$q2 = "
SELECT 
name, 
dob,
patient_id,
TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age
FROM patients
WHERE 
TIMESTAMPDIFF(YEAR, dob, CURDATE()) IN (40,50,60)
ORDER BY age DESC
";

$r2 = $conn->query($q2);
$milestone_count = $r2->num_rows;
?>

<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-award"></i> Milestone Birthdays (40, 50, 60)</h4>
        <span class="badge-modern badge-info"><?= $milestone_count ?> patients</span>
    </div>

    <?php if($milestone_count > 0): ?>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Date of Birth</th>
                    <th>Current Age</th>
                    <th>Milestone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $r2->fetch_assoc()){ ?>
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                    </div>
                </td>
                <td><?= date('F d, Y', strtotime($row['dob'])) ?></td>
                <td><span class="badge-modern badge-info"><?= $row['age'] ?> years</span></td>
                <td>
                    <span class="badge-modern" style="background: var(--warning-gradient);">
                        <i class="fas fa-trophy"></i> <?= $row['age'] ?> Years
                    </span>
                </td>
                <td>
                    <a href="../patients/view.php?id=<?= $row['patient_id'] ?>" class="btn-modern btn-info-modern btn-sm">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-award" style="font-size: 4rem; color: #ddd;"></i>
        <h5 class="mt-3 text-muted">No milestone birthdays this year</h5>
    </div>
    <?php endif; ?>
</div>

<!-- All Birthdays by Month -->
<?php
$monthly_birthdays = $conn->query("
    SELECT 
        MONTH(dob) as birth_month,
        COUNT(*) as count
    FROM patients
    GROUP BY MONTH(dob)
    ORDER BY MONTH(dob)
");
?>

<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-calendar-alt"></i> Birthdays by Month</h4>
    </div>

    <div class="row">
        <?php 
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $month_counts = array_fill(0, 12, 0);
        
        while($month = $monthly_birthdays->fetch_assoc()) {
            $month_counts[$month['birth_month'] - 1] = $month['count'];
        }
        
        foreach($months as $index => $month_name): 
            $count = $month_counts[$index];
        ?>
        <div class="col-md-3 mb-3">
            <div class="text-center p-3" style="background: <?= $count > 0 ? 'linear-gradient(135deg, #667eea15 0%, #764ba215 100%)' : '#f8f9fa' ?>; border-radius: 12px;">
                <h6 class="mb-2"><?= $month_name ?></h6>
                <h3 class="mb-0" style="color: #667eea;"><?= $count ?></h3>
                <small class="text-muted">birthday<?= $count != 1 ? 's' : '' ?></small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>