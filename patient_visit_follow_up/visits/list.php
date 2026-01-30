<?php
include('../includes/auth.php');   // FIRST
include('../config/db.php');
include('../includes/header.php');
?>


<div class="page-header">
    <h1><i class="fas fa-calendar-check"></i> Visits</h1>
    <div class="header-actions">
        <a href="add.php" class="btn-modern btn-success-modern">
            <i class="fas fa-plus"></i> Add New Visit
        </a>
    </div>
</div>

<?php
// Get statistics
$today = $conn->query("SELECT COUNT(*) as count FROM visits WHERE visit_date = CURDATE()")->fetch_assoc();
$this_week = $conn->query("SELECT COUNT(*) as count FROM visits WHERE YEARWEEK(visit_date) = YEARWEEK(CURDATE())")->fetch_assoc();
$this_month = $conn->query("SELECT COUNT(*) as count FROM visits WHERE MONTH(visit_date) = MONTH(CURDATE()) AND YEAR(visit_date) = YEAR(CURDATE())")->fetch_assoc();
?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-gradient);">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3><?= $today['count'] ?></h3>
                <p>Today's Visits</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--info-gradient);">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-content">
                <h3><?= $this_week['count'] ?></h3>
                <p>This Week</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-gradient);">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?= $this_month['count'] ?></h3>
                <p>This Month</p>
            </div>
        </div>
    </div>
</div>

<?php
$query = "
SELECT 
v.visit_id,
p.name,
v.visit_date,
v.follow_up_due,
v.consultation_fee,
v.lab_fee,
TIMESTAMPDIFF(DAY, v.visit_date, CURDATE()) AS days_since_visit,
CASE 
    WHEN v.follow_up_due < CURDATE() THEN 'Overdue'
    WHEN v.follow_up_due BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        THEN 'Upcoming'
    ELSE 'Normal'
END AS followup_status
FROM visits v
JOIN patients p ON p.patient_id = v.patient_id
ORDER BY v.visit_date DESC
LIMIT 100
";

$result = $conn->query($query);
?>

<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-list"></i> Recent Visits</h4>
        <div>
            <input type="text" id="searchInput" class="form-control" placeholder="Search visits..." style="width: 300px;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-modern" id="visitsTable">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Visit Date</th>
                    <th>Days Ago</th>
                    <th>Fees</th>
                    <th>Follow-up Due</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()){ ?>
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                    </div>
                </td>
                <td>
                    <div>
                        <strong><?= date('M d, Y', strtotime($row['visit_date'])) ?></strong>
                        <br>
                        <small class="text-muted"><?= date('l', strtotime($row['visit_date'])) ?></small>
                    </div>
                </td>
                <td>
                    <span class="badge-modern badge-info">
                        <?= $row['days_since_visit'] ?> days
                    </span>
                </td>
                <td>
                    <div>
                        <small class="text-muted">Consult:</small> <strong>$<?= number_format($row['consultation_fee'], 2) ?></strong>
                        <br>
                        <small class="text-muted">Lab:</small> <strong>$<?= number_format($row['lab_fee'], 2) ?></strong>
                    </div>
                </td>
                <td>
                    <?= date('M d, Y', strtotime($row['follow_up_due'])) ?>
                </td>
                <td>
                    <?php
                    $badge_class = 'badge-info';
                    $icon = 'fa-check-circle';
                    if($row['followup_status'] == 'Overdue') {
                        $badge_class = 'badge-danger';
                        $icon = 'fa-exclamation-circle';
                    } elseif($row['followup_status'] == 'Upcoming') {
                        $badge_class = 'badge-warning';
                        $icon = 'fa-clock';
                    } else {
                        $badge_class = 'badge-success';
                    }
                    ?>
                    <span class="badge-modern <?= $badge_class ?>">
                        <i class="fas <?= $icon ?>"></i> <?= $row['followup_status'] ?>
                    </span>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('#visitsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});
</script>

<?php include('../includes/footer.php'); ?>