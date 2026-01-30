<?php
// include('../includes/auth.php');   
include('../config/db.php');
include('../includes/header.php');
?>

<?php
if(isset($_POST['save'])){
    $stmt = $conn->prepare("
        INSERT INTO visits (patient_id, visit_date, consultation_fee, lab_fee, follow_up_due)
        VALUES (?, ?, ?, ?, DATE_ADD(?, INTERVAL 7 DAY))
    ");
    $stmt->bind_param(
        "isdds",
        $_POST['patient_id'],
        $_POST['visit_date'],
        $_POST['fee'],
        $_POST['lab'],
        $_POST['visit_date']
    );
    $stmt->execute();

    echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Visit Added Successfully!</div>";
}

// Get patient list for dropdown
$patients = $conn->query("SELECT patient_id, name FROM patients ORDER BY name");
$patient_id = $_GET['patient_id'] ?? null;
?>

<div class="page-header">
    <h1><i class="fas fa-calendar-plus"></i> Add New Visit</h1>
    <div class="header-actions">
        <a href="list.php" class="btn-modern btn-info-modern">
            <i class="fas fa-arrow-left"></i> Back to Visits
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-file-medical-alt"></i> Visit Information</h4>
            </div>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Patient *
                        </label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">Select a patient</option>
                            <?php while($p = $patients->fetch_assoc()): ?>
                            <option value="<?= $p['patient_id'] ?>" <?= $patient_id == $p['patient_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-calendar"></i> Visit Date *
                        </label>
                        <input type="date" name="visit_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-dollar-sign"></i> Consultation Fee
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius: 12px 0 0 12px;">$</span>
                            <input type="number" name="fee" class="form-control" placeholder="0.00" step="0.01">
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-flask"></i> Lab Fee
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius: 12px 0 0 12px;">$</span>
                            <input type="number" name="lab" class="form-control" placeholder="0.00" step="0.01">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-3" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-radius: 12px; border-left: 4px solid #667eea;">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Note:</strong> Follow-up date will be automatically set to 7 days after the visit date.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="list.php" class="btn-modern" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button name="save" class="btn-modern btn-success-modern">
                        <i class="fas fa-save"></i> Save Visit
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Stats -->
        <div class="row">
            <div class="col-md-6">
                <div class="modern-card" style="background: linear-gradient(135deg, #4facfe15 0%, #00f2fe15 100%);">
                    <h6><i class="fas fa-calendar-day"></i> Today's Visits</h6>
                    <?php
                    $today_visits = $conn->query("SELECT COUNT(*) as count FROM visits WHERE visit_date = CURDATE()")->fetch_assoc();
                    ?>
                    <h3 class="mb-0"><?= $today_visits['count'] ?></h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="modern-card" style="background: linear-gradient(135deg, #11998e15 0%, #38ef7d15 100%);">
                    <h6><i class="fas fa-chart-line"></i> This Month</h6>
                    <?php
                    $month_visits = $conn->query("
                        SELECT COUNT(*) as count 
                        FROM visits 
                        WHERE MONTH(visit_date) = MONTH(CURDATE()) 
                        AND YEAR(visit_date) = YEAR(CURDATE())
                    ")->fetch_assoc();
                    ?>
                    <h3 class="mb-0"><?= $month_visits['count'] ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>