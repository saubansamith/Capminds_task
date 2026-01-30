<?php
include('../includes/auth.php');   // FIRST
include('../config/db.php');
include('../includes/header.php');
?>


<div class="page-header">
    <h1><i class="fas fa-clock"></i> Follow-Up Reports</h1>
    <div class="header-actions">
        <button onclick="window.print()" class="btn-modern btn-primary-modern">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<!-- Statistics Overview -->
<?php
$upcoming = $conn->query("SELECT COUNT(*) as count FROM visits WHERE follow_up_due BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc();
$overdue = $conn->query("SELECT COUNT(*) as count FROM visits WHERE follow_up_due < CURDATE()")->fetch_assoc();
$inactive = $conn->query("
    SELECT COUNT(*) as count 
    FROM (
        SELECT p.patient_id 
        FROM patients p 
        LEFT JOIN visits v ON p.patient_id = v.patient_id 
        GROUP BY p.patient_id 
        HAVING TIMESTAMPDIFF(DAY, MAX(v.visit_date), CURDATE()) >= 180 
        OR MAX(v.visit_date) IS NULL
    ) as inactive_patients
")->fetch_assoc();
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--warning-gradient);">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-content">
                <h3><?= $upcoming['count'] ?></h3>
                <p>Upcoming (7 Days)</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--danger-gradient);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?= $overdue['count'] ?></h3>
                <p>Overdue Follow-ups</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--info-gradient);">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?= $inactive['count'] ?></h3>
                <p>Inactive (180+ Days)</p>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Follow-ups -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-calendar-check"></i> Follow-ups in Next 7 Days</h4>
        <span class="badge-modern badge-warning"><?= $upcoming['count'] ?> appointments</span>
    </div>

    <?php
    $q1 = "
    SELECT p.patient_id, p.name, v.follow_up_due, v.visit_date,
    DATEDIFF(v.follow_up_due, CURDATE()) as days_until
    FROM visits v
    JOIN patients p ON p.patient_id = v.patient_id
    WHERE v.follow_up_due BETWEEN CURDATE() 
    AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY v.follow_up_due
    ";
    $r1 = $conn->query($q1);
    ?>

    <?php if($r1->num_rows > 0): ?>
    <div class="row">
        <?php while($row=$r1->fetch_assoc()){ ?>
        <div class="col-md-6 mb-3">
            <div class="p-3" style="background: linear-gradient(135deg, #f093fb15 0%, #f5576c15 100%); border-radius: 12px; border-left: 4px solid #f093fb;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--warning-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($row['name']) ?></h6>
                            <small class="text-muted">
                                Due: <?= date('M d, Y', strtotime($row['follow_up_due'])) ?>
                            </small>
                            <br>
                            <span class="badge-modern badge-warning mt-1">
                                <?php
                                if($row['days_until'] == 0) echo "Today";
                                elseif($row['days_until'] == 1) echo "Tomorrow";
                                else echo "In " . $row['days_until'] . " days";
                                ?>
                            </span>
                        </div>
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
    <div class="text-center py-4">
        <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745;"></i>
        <h5 class="mt-3 text-muted">No upcoming follow-ups in the next 7 days</h5>
    </div>
    <?php endif; ?>
</div>

<!-- Overdue Follow-ups -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-exclamation-circle"></i> Overdue Follow-ups</h4>
        <span class="badge-modern badge-danger"><?= $overdue['count'] ?> overdue</span>
    </div>

    <?php
    $q2 = "
    SELECT p.patient_id, p.name, v.follow_up_due, v.visit_date,
    DATEDIFF(CURDATE(), v.follow_up_due) as days_overdue
    FROM visits v
    JOIN patients p ON p.patient_id = v.patient_id
    WHERE v.follow_up_due < CURDATE()
    ORDER BY v.follow_up_due
    LIMIT 50
    ";
    $r2 = $conn->query($q2);
    ?>

    <?php if($r2->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Due Date</th>
                    <th>Days Overdue</th>
                    <th>Last Visit</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row=$r2->fetch_assoc()){ ?>
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--danger-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                    </div>
                </td>
                <td><?= date('M d, Y', strtotime($row['follow_up_due'])) ?></td>
                <td>
                    <span class="badge-modern badge-danger">
                        <?= $row['days_overdue'] ?> days
                    </span>
                </td>
                <td><?= date('M d, Y', strtotime($row['visit_date'])) ?></td>
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
    <div class="text-center py-4">
        <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745;"></i>
        <h5 class="mt-3 text-muted">No overdue follow-ups</h5>
    </div>
    <?php endif; ?>
</div>

<!-- Missed Follow-ups -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-times-circle"></i> Missed Follow-ups (No Visit After Due Date)</h4>
    </div>

    <?php
    $q3 = "
    SELECT p.patient_id, p.name, v.follow_up_due,
    DATEDIFF(CURDATE(), v.follow_up_due) as days_missed
    FROM visits v
    JOIN patients p ON p.patient_id = v.patient_id
    LEFT JOIN visits v2 
      ON v.patient_id = v2.patient_id 
      AND v2.visit_date > v.follow_up_due
    WHERE v.follow_up_due < CURDATE()
    AND v2.visit_id IS NULL
    ORDER BY v.follow_up_due
    LIMIT 30
    ";
    $r3 = $conn->query($q3);
    ?>

    <?php if($r3->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Missed Date</th>
                    <th>Days Missed</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row=$r3->fetch_assoc()){ ?>
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                    </div>
                </td>
                <td><?= date('M d, Y', strtotime($row['follow_up_due'])) ?></td>
                <td>
                    <span class="badge-modern badge-danger">
                        <?= $row['days_missed'] ?> days ago
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
    <div class="text-center py-4">
        <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745;"></i>
        <h5 class="mt-3 text-muted">No missed follow-ups</h5>
    </div>
    <?php endif; ?>
</div>

<!-- Inactive Patients -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-user-clock"></i> Patients Inactive for 180+ Days</h4>
        <span class="badge-modern badge-info"><?= $inactive['count'] ?> patients</span>
    </div>

    <?php
    $q4 = "
    SELECT p.patient_id, p.name, MAX(v.visit_date) last_visit,
    TIMESTAMPDIFF(DAY, MAX(v.visit_date), CURDATE()) days_inactive
    FROM patients p
    LEFT JOIN visits v ON p.patient_id = v.patient_id
    GROUP BY p.patient_id
    HAVING days_inactive >= 180 OR last_visit IS NULL
    ORDER BY days_inactive DESC
    LIMIT 50
    ";
    $r4 = $conn->query($q4);
    ?>

    <?php if($r4->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Last Visit</th>
                    <th>Days Inactive</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row=$r4->fetch_assoc()){ ?>
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--info-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                    </div>
                </td>
                <td>
                    <?= $row['last_visit'] ? date('M d, Y', strtotime($row['last_visit'])) : '<span class="text-muted">Never visited</span>' ?>
                </td>
                <td>
                    <span class="badge-modern <?= $row['days_inactive'] > 365 ? 'badge-danger' : 'badge-warning' ?>">
                        <?= $row['days_inactive'] ?? 'N/A' ?> days
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
    <div class="text-center py-4">
        <i class="fas fa-users" style="font-size: 3rem; color: #28a745;"></i>
        <h5 class="mt-3 text-muted">All patients are active</h5>
    </div>
    <?php endif; ?>
</div>

<!-- Patients With No Visits -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-user-slash"></i> Patients With No Visits</h4>
    </div>

    <?php
    $q5 = "
    SELECT p.patient_id, p.name, p.join_date
    FROM patients p
    LEFT JOIN visits v ON p.patient_id = v.patient_id
    WHERE v.visit_id IS NULL
    ORDER BY p.join_date DESC
    ";
    $r5 = $conn->query($q5);
    ?>

    <?php if($r5->num_rows > 0): ?>
    <div class="row">
        <?php while($row=$r5->fetch_assoc()){ ?>
        <div class="col-md-6 mb-3">
            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; border-left: 4px solid #6c757d;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 45px; height: 45px; border-radius: 50%; background: #6c757d; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($row['name']) ?></h6>
                            <small class="text-muted">
                                Joined: <?= date('M d, Y', strtotime($row['join_date'])) ?>
                            </small>
                        </div>
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
    <div class="text-center py-4">
        <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745;"></i>
        <h5 class="mt-3 text-muted">All patients have at least one visit</h5>
    </div>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>