<?php
include('../includes/auth.php');   // FIRST
include('../config/db.php');
include('../includes/header.php');




$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if(isset($_POST['update'])){
    $stmt = $conn->prepare("UPDATE patients SET name=?,dob=?,join_date=?,phone=?,address=? WHERE patient_id=?");
    $stmt->bind_param("sssssi",$_POST['name'],$_POST['dob'],$_POST['join_date'],$_POST['phone'],$_POST['address'],$id);
    $stmt->execute();
    
    echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Patient Updated Successfully!</div>";
    
    // Refresh patient data
    $stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
}
?>

<div class="page-header">
    <h1><i class="fas fa-user-edit"></i> Edit Patient</h1>
    <div class="header-actions">
        <a href="list.php" class="btn-modern btn-info-modern">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <a href="view.php?id=<?= $id ?>" class="btn-modern btn-primary-modern">
            <i class="fas fa-eye"></i> View Details
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-edit"></i> Update Patient Information</h4>
                <span class="badge-modern badge-info">ID: <?= $patient['patient_id'] ?></span>
            </div>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Full Name *
                        </label>
                        <input type="text" name="name" value="<?= htmlspecialchars($patient['name']) ?>" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($patient['phone']) ?>" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-birthday-cake"></i> Date of Birth *
                        </label>
                        <input type="date" name="dob" value="<?= $patient['dob'] ?>" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-calendar-check"></i> Join Date *
                        </label>
                        <input type="date" name="join_date" value="<?= $patient['join_date'] ?>" class="form-control" required>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Address
                        </label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($patient['address']) ?></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="list.php" class="btn-modern" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button name="update" class="btn-modern btn-primary-modern">
                        <i class="fas fa-save"></i> Update Patient
                    </button>
                </div>
            </form>
        </div>

        <!-- Patient Stats Card -->
        <?php
        $stats_query = "
            SELECT 
                COUNT(v.visit_id) as total_visits,
                MAX(v.visit_date) as last_visit
            FROM visits v 
            WHERE v.patient_id = ?
        ";
        $stats_stmt = $conn->prepare($stats_query);
        $stats_stmt->bind_param("i", $id);
        $stats_stmt->execute();
        $stats = $stats_stmt->get_result()->fetch_assoc();
        ?>

        <div class="modern-card" style="background: linear-gradient(135deg, #11998e15 0%, #38ef7d15 100%);">
            <h5><i class="fas fa-chart-line"></i> Patient Statistics</h5>
            <div class="row mt-3">
                <div class="col-md-6">
                    <p class="mb-1 text-muted">Total Visits</p>
                    <h4 class="mb-0"><?= $stats['total_visits'] ?></h4>
                </div>
                <div class="col-md-6">
                    <p class="mb-1 text-muted">Last Visit</p>
                    <h4 class="mb-0"><?= $stats['last_visit'] ? date('M d, Y', strtotime($stats['last_visit'])) : 'No visits' ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>