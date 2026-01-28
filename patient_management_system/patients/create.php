<?php include('../config/db.php'); ?>
<?php include('../includes/header.php'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-custom">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Register New Patient</h3>
                    <p class="text-muted">Enter the details below to add a new record to the system.</p>
                </div>

                <form method="POST" class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label small text-uppercase fw-bold text-muted">Full Name</label>
                        <input name="name" class="form-control bg-light border-0 py-3" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Email Address</label>
                        <input name="email" type="email" class="form-control bg-light border-0 py-3" placeholder="email@hospital.com" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Phone Number</label>
                        <input name="phone" class="form-control bg-light border-0 py-3" placeholder="+1 234 567 890" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-uppercase fw-bold text-muted">Age</label>
                        <input name="age" type="number" class="form-control bg-light border-0 py-3" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-uppercase fw-bold text-muted">Gender</label>
                        <select name="gender" class="form-control bg-light border-0 py-3" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-uppercase fw-bold text-muted">Diagnosis</label>
                        <input name="diagnosis" class="form-control bg-light border-0 py-3" placeholder="Condition" required>
                    </div>
                    <div class="col-12 text-center mt-5">
                        <button class="btn btn-primary btn-action w-100 py-3 shadow">Add Patient to System</button>
                        <a href="list.php" class="btn btn-link mt-2 text-decoration-none text-muted">Back to Registry</a>
                    </div>
                </form>

                <?php
                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    $stmt = $conn->prepare("INSERT INTO patients (patient_name,email,phone,age,gender,diagnosis) VALUES (?,?,?,?,?,?)");
                    $stmt->bind_param("sssiss", $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['age'], $_POST['gender'], $_POST['diagnosis']);
                    $stmt->execute();
                    echo "<div class='alert alert-success mt-4 rounded-pill text-center'>Patient added successfully!</div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>