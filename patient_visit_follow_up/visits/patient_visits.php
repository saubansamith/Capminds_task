<?php
// include('../includes/auth.php');  
include('../config/db.php');
include('../includes/header.php');

$id = $_GET['id'];

$query = "
SELECT 
p.name,
p.patient_id,
COUNT(v.visit_id) AS total_visits,
MIN(v.visit_date) AS first_visit,
MAX(v.visit_date) AS last_visit,
SUM(v.consultation_fee + v.lab_fee) AS total_revenue,
TIMESTAMPDIFF(DAY, MIN(v.visit_date), MAX(v.visit_date)) AS days_between_first_last
FROM visits v
JOIN patients p ON p.patient_id = v.patient_id
WHERE v.patient_id = ?
GROUP BY v.patient_id
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Get all visits
$visits_query = "
SELECT 
v.*,
TIMESTAMPDIFF(DAY, v.visit_date, CURDATE()) as days_ago
FROM visits v
WHERE v.patient_id = ?
ORDER BY v.visit_date DESC
";
$visits_stmt = $conn->prepare($visits_query);
$visits_stmt->bind_param("i", $id);
$visits_stmt->execute();
$visits = $visits_stmt->get_result();
?>

<div class="page-header">
    <h1><i class="fas fa-history"></i> Visit History</h1>
    <div class="header-actions">
        <a href="../patients/view.php?id=<?= $id ?>" class="btn-modern btn-info-modern">
            <i class="fas fa-arrow-left"></i> Back to Patient
        </a>
        <a href="add.php?patient_id=<?= $id ?>" class="btn-modern btn-success-modern">
            <i class="fas fa-plus"></i> Add Visit
        </a>
    </div>
</div>

<!-- Patient Info Banner -->
<div class="modern-card" style="background: var(--primary-gradient); color: white;">
    <div class="d-flex align-items-center gap-3">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700;">
            <?= strtoupper(substr($data['name'], 0, 1)) ?>
        </div>
        <div>
            <h3 class="mb-1"><?= htmlspecialchars($data['name']) ?></h3>
            <p class="mb-0 opacity-75">Patient ID: <?= $data['patient_id'] ?></p>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-gradient);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3><?= $data['total_visits'] ?></h3>
                <p>Total Visits</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-gradient);">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3 style="font-size: 1.2rem;"><?= date('M d, Y', strtotime($data['first_visit'])) ?></h3>
                <p>First Visit</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--info-gradient);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 style="font-size: 1.2rem;"><?= date('M d, Y', strtotime($data['last_visit'])) ?></h3>
                <p>Last Visit</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--warning-gradient);">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h3>$<?= number_format($data['total_revenue'], 2) ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
    </div>
</div>

<!-- Visit Timeline -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-timeline"></i> Visit Timeline</h4>
        <span class="badge-modern badge-info">
            <?= $data['days_between_first_last'] ?> days span
        </span>
    </div>

    <?php if($visits->num_rows > 0): ?>
    <div class="timeline-container">
        <?php while($visit = $visits->fetch_assoc()): ?>
        <div class="visit-card mb-3 p-4" style="background: #f8f9fa; border-radius: 12px; border-left: 4px solid #667eea;">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="text-center p-3" style="background: white; border-radius: 12px;">
                        <h5 class="mb-1" style="color: #667eea;"><?= date('d', strtotime($visit['visit_date'])) ?></h5>
                        <p class="mb-0 text-muted"><?= date('M Y', strtotime($visit['visit_date'])) ?></p>
                        <small class="text-muted"><?= $visit['days_ago'] ?> days ago</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-2"><i class="fas fa-calendar-check"></i> Visit #<?= $visit['visit_id'] ?></h6>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Consultation Fee</small>
                            <p class="mb-0"><strong>$<?= number_format($visit['consultation_fee'], 2) ?></strong></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Lab Fee</small>
                            <p class="mb-0"><strong>$<?= number_format($visit['lab_fee'], 2) ?></strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <small class="text-muted d-block mb-2">Follow-up Due</small>
                    <span class="badge-modern <?= strtotime($visit['follow_up_due']) < time() ? 'badge-danger' : 'badge-success' ?>">
                        <?= date('M d, Y', strtotime($visit['follow_up_due'])) ?>
                    </span>
                    <div class="mt-2">
                        <small class="text-muted">
                            Total: <strong>$<?= number_format($visit['consultation_fee'] + $visit['lab_fee'], 2) ?></strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-calendar-times" style="font-size: 4rem; color: #ddd;"></i>
        <h5 class="mt-3 text-muted">No visits recorded yet</h5>
        <a href="add.php?patient_id=<?= $id ?>" class="btn-modern btn-primary-modern mt-3">
            <i class="fas fa-plus"></i> Add First Visit
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Visit Analysis -->
<?php if($data['total_visits'] > 1): ?>
<div class="row">
    <div class="col-md-6">
        <div class="modern-card" style="background: linear-gradient(135deg, #4facfe15 0%, #00f2fe15 100%);">
            <h5><i class="fas fa-chart-line"></i> Visit Frequency</h5>
            <p class="mb-1">Average time between visits:</p>
            <h3><?= round($data['days_between_first_last'] / ($data['total_visits'] - 1)) ?> days</h3>
        </div>
    </div>
    <div class="col-md-6">
        <div class="modern-card" style="background: linear-gradient(135deg, #11998e15 0%, #38ef7d15 100%);">
            <h5><i class="fas fa-dollar-sign"></i> Average Per Visit</h5>
            <p class="mb-1">Revenue per visit:</p>
            <h3>$<?= number_format($data['total_revenue'] / $data['total_visits'], 2) ?></h3>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include('../includes/footer.php'); ?>