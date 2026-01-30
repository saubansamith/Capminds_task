<?php include('../includes/auth.php'); ?>
<?php include('../config/db.php'); ?>
<?php include('../includes/header.php'); ?>

<?php
if(isset($_POST['save'])){
    // Validation
    if($_POST['dob'] > date('Y-m-d')){
        echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> DOB cannot be a future date</div>";
    }
    elseif(empty($_POST['join_date'])){
        echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Join date is required</div>";
    }
    else{
        $stmt = $conn->prepare("
            INSERT INTO patients (name,dob,join_date,phone,address) 
            VALUES (?,?,?,?,?)
        ");
        $stmt->bind_param(
            "sssss",
            $_POST['name'],
            $_POST['dob'],
            $_POST['join_date'],
            $_POST['phone'],
            $_POST['address']
        );
        $stmt->execute();

        echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Patient Added Successfully!</div>";
    }
}
?>

<div class="page-header">
    <h1><i class="fas fa-user-plus"></i> Add New Patient</h1>
    <div class="header-actions">
        <a href="list.php" class="btn-modern btn-info-modern">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="modern-card">
            <div class="card-header-modern">
                <h4><i class="fas fa-file-medical"></i> Patient Information</h4>
            </div>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Full Name *
                        </label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter patient name">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="text" name="phone" class="form-control" placeholder="Enter phone number">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-birthday-cake"></i> Date of Birth *
                        </label>
                        <input type="date" name="dob" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-calendar-check"></i> Join Date *
                        </label>
                        <input type="date" name="join_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Address
                        </label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Enter complete address"></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="list.php" class="btn-modern" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button name="save" class="btn-modern btn-success-modern">
                        <i class="fas fa-save"></i> Save Patient
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Tips Card -->
        <div class="modern-card" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);">
            <h5><i class="fas fa-lightbulb"></i> Quick Tips</h5>
            <ul style="margin: 0; padding-left: 1.5rem;">
                <li>All fields marked with * are required</li>
                <li>Date of birth cannot be in the future</li>
                <li>Join date defaults to today's date</li>
                <li>Phone number and address are optional but recommended</li>
            </ul>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>