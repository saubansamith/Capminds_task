<?php
// include('../includes/auth.php');
include('../config/db.php');
include('../includes/header.php');

$id = $_GET['id'];

// Main patient query (prepared)
$query = "
SELECT p.*,
TIMESTAMPDIFF(YEAR,dob,CURDATE()) AS age,
MAX(v.visit_date) AS last_visit,
TIMESTAMPDIFF(DAY,MAX(v.visit_date),CURDATE()) AS days_since,
MAX(v.follow_up_due) AS next_followup,
COUNT(v.visit_id) as total_visits,
CASE 
    WHEN MAX(v.follow_up_due) < CURDATE() THEN 'Overdue' 
    ELSE 'Upcoming' 
END AS status
FROM patients p
LEFT JOIN visits v ON p.patient_id=v.patient_id
WHERE p.patient_id=?
GROUP BY p.patient_id
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>


<div class="page-header">
    <h1><i class="fas fa-user-circle"></i> Patient Profile</h1>
    <div class="header-actions">
        <a href="list.php" class="btn-modern btn-info-modern">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <a href="edit.php?id=<?= $id ?>" class="btn-modern btn-warning-modern">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <!-- Patient Info Card -->
    <div class="col-lg-4">
        <div class="modern-card text-center">
            <div style="width: 120px; height: 120px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: 700; margin: 0 auto 1.5rem;">
                <?= strtoupper(substr($data['name'], 0, 1)) ?>
            </div>
            <h3 class="mb-2"><?= htmlspecialchars($data['name']) ?></h3>
            <p class="text-muted mb-3">Patient ID: <?= $id ?></p>
            
            <div class="d-flex flex-column gap-2">
                <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                    <small class="text-muted d-block mb-1">Age</small>
                    <strong><?= $data['age'] ?> years</strong>
                </div>
                <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                    <small class="text-muted d-block mb-1">Date of Birth</small>
                    <strong><?= date('M d, Y', strtotime($data['dob'])) ?></strong>
                </div>
                <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                    <small class="text-muted d-block mb-1">Join Date</small>
                    <strong><?= date('M d, Y', strtotime($data['join_date'])) ?></strong>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="modern-card">
            <h5 class="mb-3"><i class="fas fa-address-book"></i> Contact Information</h5>
            <div class="mb-3">
                <small class="text-muted d-block mb-1"><i class="fas fa-phone"></i> Phone</small>
                <strong><?= $data['phone'] ? htmlspecialchars($data['phone']) : 'Not provided' ?></strong>
            </div>
            <div>
                <small class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt"></i> Address</small>
                <strong><?= $data['address'] ? htmlspecialchars($data['address']) : 'Not provided' ?></strong>
            </div>
        </div>
    </div>

    <!-- Visit Statistics -->
    <div class="col-lg-8">
        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-4">
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
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--info-gradient);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $data['days_since'] ?? 'N/A' ?></h3>
                        <p>Days Since Last Visit</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?= $data['status'] == 'Overdue' ? 'var(--danger-gradient)' : 'var(--success-gradient)' ?>;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-content">
                        <h3 style="font-size: 1rem;"><?= $data['status'] ?></h3>
                        <p>Follow-up Status</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visit Details -->
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-notes-medical"></i> Visit Details</h4>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="p-4" style="background: linear-gradient(135deg, #4facfe15 0%, #00f2fe15 100%); border-radius: 12px; border-left: 4px solid #4facfe;">
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-calendar"></i> Last Visit
                        </small>
                        <h5 class="mb-0">
                            <?= $data['last_visit'] ? date('M d, Y', strtotime($data['last_visit'])) : 'No visits yet' ?>
                        </h5>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="p-4" style="background: linear-gradient(135deg, <?= $data['status'] == 'Overdue' ? '#ee097915' : '#11998e15' ?> 0%, <?= $data['status'] == 'Overdue' ? '#ff6a0015' : '#38ef7d15' ?> 100%); border-radius: 12px; border-left: 4px solid <?= $data['status'] == 'Overdue' ? '#ee0979' : '#11998e' ?>;">
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-calendar-alt"></i> Next Follow-up
                        </small>
                        <h5 class="mb-0">
                            <?= $data['next_followup'] ? date('M d, Y', strtotime($data['next_followup'])) : 'Not scheduled' ?>
                        </h5>
                        <?php if($data['next_followup']): ?>
                        <span class="badge-modern <?= $data['status'] == 'Overdue' ? 'badge-danger' : 'badge-success' ?> mt-2">
                            <?= $data['status'] ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-tasks"></i> Quick Actions</h4>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <a href="../visits/patient_visits.php?id=<?= $id ?>" class="btn-modern btn-primary-modern w-100">
                        <i class="fas fa-history"></i> View Visit History
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="../visits/add.php?patient_id=<?= $id ?>" class="btn-modern btn-success-modern w-100">
                        <i class="fas fa-plus-circle"></i> Add New Visit
                    </a>
                </div>
            </div>
        </div>

        <!-- Timeline Preview -->
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-timeline"></i> Recent Activity</h4>
            </div>
            
            <?php
            $rv = $conn->prepare("
            SELECT visit_date, consultation_fee, lab_fee 
            FROM visits 
            WHERE patient_id = ?
            ORDER BY visit_date DESC 
            LIMIT 5
            ");
            $rv->bind_param("i", $id);
            $rv->execute();
            $recent_visits = $rv->get_result();
        
            
            if($recent_visits->num_rows > 0):
            ?>
            <div class="timeline">
                <?php while($visit = $recent_visits->fetch_assoc()): ?>
                <div class="p-3 mb-2" style="background: #f8f9fa; border-radius: 12px; border-left: 3px solid #667eea;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="fas fa-calendar-day"></i> <?= date('M d, Y', strtotime($visit['visit_date'])) ?></strong>
                            <br>
                            <small class="text-muted">
                                Fee: $<?= $visit['consultation_fee'] ?> | Lab: $<?= $visit['lab_fee'] ?>
                            </small>
                        </div>
                        <span class="badge-modern badge-success">Visit</span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <p class="text-muted text-center py-4">
                <i class="fas fa-inbox"></i><br>
                No visits recorded yet
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>