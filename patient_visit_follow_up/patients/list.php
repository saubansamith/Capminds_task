<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

// Include authentication first to start session and check login
include('../includes/auth.php');

// Include database connection
include('../config/db.php');

// Include header (HTML output starts here)
include('../includes/header.php');

// Pagination and search setup
$limit = 5;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$search_param = "%$search%";

// Fetch statistics
$stats = $conn->query("SELECT COUNT(*) as total FROM patients")->fetch_assoc();

$active = $conn->query("
    SELECT COUNT(DISTINCT p.patient_id) as count 
    FROM patients p 
    INNER JOIN visits v ON p.patient_id = v.patient_id 
    WHERE v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
")->fetch_assoc();

$new_this_month = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients 
    WHERE MONTH(join_date) = MONTH(CURDATE()) 
    AND YEAR(join_date) = YEAR(CURDATE())
")->fetch_assoc();

// Main query with pagination + search
$query = "
SELECT 
    p.*,
    TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age_years,
    CONCAT(
        TIMESTAMPDIFF(YEAR, dob, CURDATE()), ' years ',
        TIMESTAMPDIFF(MONTH, dob, CURDATE()) % 12, ' months'
    ) AS full_age,
    YEAR(join_date) AS join_year,
    MONTH(join_date) AS join_month,
    DAY(join_date) AS join_day,
    COUNT(v.visit_id) AS total_visits
FROM patients p
LEFT JOIN visits v ON p.patient_id = v.patient_id
WHERE p.name LIKE ?
GROUP BY p.patient_id
ORDER BY p.name
LIMIT ?, ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $search_param, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-users"></i> Patients</h1>
    <div class="header-actions">
        <a href="add.php" class="btn-modern btn-success-modern">
            <i class="fas fa-plus"></i> Add New Patient
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-gradient);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?= $stats['total'] ?></h3>
                <p>Total Patients</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-gradient);">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <h3><?= $active['count'] ?></h3>
                <p>Active (90 days)</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--info-gradient);">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-content">
                <h3><?= $new_this_month['count'] ?></h3>
                <p>New This Month</p>
            </div>
        </div>
    </div>
</div>

<!-- Patient Table -->
<div class="modern-card">
    <div class="card-header-modern">
        <h4><i class="fas fa-list"></i> Patient List</h4>

        <!-- Search -->
        <form method="GET" class="d-flex">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   class="form-control" placeholder="Search patients..." style="width:300px;">
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Age</th>
                    <th>Join Date</th>
                    <th>Total Visits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()){ ?>
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
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
                    <span class="badge-modern badge-info"><?= $row['full_age'] ?></span>
                </td>
                <td>
                    <?= date('M d, Y', strtotime($row['join_date'])) ?>
                    <br>
                    <small class="text-muted">
                        <?= $row['join_year'] ?>-
                        <?= str_pad($row['join_month'], 2, '0', STR_PAD_LEFT) ?>-
                        <?= str_pad($row['join_day'], 2, '0', STR_PAD_LEFT) ?>
                    </small>
                </td>
                <td>
                    <span class="badge-modern" style="background: var(--success-gradient);">
                        <?= $row['total_visits'] ?> visits
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="view.php?id=<?= $row['patient_id'] ?>" class="btn btn-sm btn-modern btn-info-modern">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="edit.php?id=<?= $row['patient_id'] ?>" class="btn btn-sm btn-modern btn-warning-modern">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Pagination
$totalQuery = $conn->prepare("SELECT COUNT(*) as c FROM patients WHERE name LIKE ?");
$totalQuery->bind_param("s", $search_param);
$totalQuery->execute();
$total = $totalQuery->get_result()->fetch_assoc()['c'];

$pages = ceil($total / $limit);
?>

<div class="mt-4 text-center">
<?php for($i=1; $i<=$pages; $i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
       class="btn btn-sm <?= ($i == $page) ? 'btn-primary' : 'btn-secondary' ?> me-1">
        <?= $i ?>
    </a>
<?php endfor; ?>
</div>

<?php
include('../includes/footer.php');

// Flush output buffer at the end
ob_end_flush();
?>
