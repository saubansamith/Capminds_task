<?php
include('../includes/auth.php');   // FIRST
include('../config/db.php');
include('../includes/header.php');

$query = "
SELECT 
p.patient_id,
p.name,
TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age,
COUNT(v.visit_id) AS total_visits,
MAX(v.visit_date) AS last_visit,
TIMESTAMPDIFF(DAY, MAX(v.visit_date), CURDATE()) AS days_since_last_visit,
MAX(v.follow_up_due) AS next_followup,
CASE 
    WHEN MAX(v.follow_up_due) < CURDATE() THEN 'Overdue'
    WHEN MAX(v.follow_up_due) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'Upcoming'
    ELSE 'Normal'
END AS followup_status
FROM patients p
LEFT JOIN visits v ON p.patient_id = v.patient_id
GROUP BY p.patient_id
ORDER BY p.name
";

$result = $conn->query($query);

// Get overall statistics
$total_patients = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc();
$total_visits = $conn->query("SELECT COUNT(*) as count FROM visits")->fetch_assoc();
$avg_visits = $total_patients['count'] > 0 ? round($total_visits['count'] / $total_patients['count'], 1) : 0;
$overdue = $conn->query("
    SELECT COUNT(*) as count 
    FROM (
        SELECT DISTINCT patient_id 
        FROM visits 
        WHERE follow_up_due < CURDATE()
    ) as overdue_patients
")->fetch_assoc();
?>

<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Summary Report</h1>
    <div class="header-actions">
        <button onclick="window.print()" class="btn-modern btn-primary-modern">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<!-- Overview Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-gradient);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?= $total_patients['count'] ?></h3>
                <p>Total Patients</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-gradient);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3><?= $total_visits['count'] ?></h3>
                <p>Total Visits</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--info-gradient);">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="stat-content">
                <h3><?= $avg_visits ?></h3>
                <p>Avg Visits/Patient</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
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
</div>

<!-- Detailed Report Table -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-table"></i> Patient Summary</h4>
        <div>
            <input type="text" id="searchInput" class="form-control" placeholder="Search patients..." style="width: 300px;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-modern" id="summaryTable">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Age</th>
                    <th>Total Visits</th>
                    <th>Last Visit</th>
                    <th>Days Since</th>
                    <th>Next Follow-up</th>
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
                        <div>
                            <strong><?= htmlspecialchars($row['name']) ?></strong>
                            <br>
                            <small class="text-muted">ID: <?= $row['patient_id'] ?></small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge-modern badge-info"><?= $row['age'] ?> yrs</span>
                </td>
                <td>
                    <strong><?= $row['total_visits'] ?></strong>
                </td>
                <td>
                    <?php if($row['last_visit']): ?>
                        <?= date('M d, Y', strtotime($row['last_visit'])) ?>
                    <?php else: ?>
                        <span class="text-muted">No visits</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($row['days_since_last_visit'] !== null): ?>
                        <span class="badge-modern <?= $row['days_since_last_visit'] > 180 ? 'badge-danger' : 'badge-success' ?>">
                            <?= $row['days_since_last_visit'] ?> days
                        </span>
                    <?php else: ?>
                        <span class="text-muted">N/A</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($row['next_followup']): ?>
                        <?= date('M d, Y', strtotime($row['next_followup'])) ?>
                    <?php else: ?>
                        <span class="text-muted">Not set</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $badge_class = 'badge-info';
                    $icon = 'fa-check-circle';
                    $status = $row['followup_status'] ?? 'N/A';
                    
                    if($status == 'Overdue') {
                        $badge_class = 'badge-danger';
                        $icon = 'fa-exclamation-circle';
                    } elseif($status == 'Upcoming') {
                        $badge_class = 'badge-warning';
                        $icon = 'fa-clock';
                    } elseif($status == 'Normal') {
                        $badge_class = 'badge-success';
                    }
                    ?>
                    <span class="badge-modern <?= $badge_class ?>">
                        <i class="fas <?= $icon ?>"></i> <?= $status ?>
                    </span>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Insights -->
<div class="row">
    <div class="col-md-6">
        <div class="modern-card" style="background: linear-gradient(135deg, #ee097915 0%, #ff6a0015 100%);">
            <h5><i class="fas fa-exclamation-circle"></i> Attention Needed</h5>
            <?php
            $attention = $conn->query("
                SELECT COUNT(*) as count 
                FROM (
                    SELECT p.patient_id
                    FROM patients p 
                    LEFT JOIN visits v ON p.patient_id = v.patient_id 
                    GROUP BY p.patient_id
                    HAVING TIMESTAMPDIFF(DAY, MAX(v.visit_date), CURDATE()) > 180 
                    OR MAX(v.follow_up_due) < CURDATE()
                    OR MAX(v.visit_date) IS NULL
                ) as attention_patients
            ")->fetch_assoc();
            ?>
            <h3 class="mb-0"><?= $attention['count'] ?> patients</h3>
            <p class="mb-0 text-muted">Require follow-up or inactive</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="modern-card" style="background: linear-gradient(135deg, #11998e15 0%, #38ef7d15 100%);">
            <h5><i class="fas fa-check-circle"></i> Active Patients</h5>
            <?php
            $active = $conn->query("
                SELECT COUNT(DISTINCT p.patient_id) as count 
                FROM patients p 
                INNER JOIN visits v ON p.patient_id = v.patient_id 
                WHERE v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            ")->fetch_assoc();
            ?>
            <h3 class="mb-0"><?= $active['count'] ?> patients</h3>
            <p class="mb-0 text-muted">Visited in last 90 days</p>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('#summaryTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});
</script>

<?php include('../includes/footer.php'); ?>