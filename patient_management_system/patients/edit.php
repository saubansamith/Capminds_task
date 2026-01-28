<?php
include('../config/db.php');
include('../includes/header.php');

$id = $_GET['id'];

// Fetch existing data
$stmt = $conn->prepare("SELECT * FROM patients WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

// Update logic
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $diagnosis = trim($_POST['diagnosis']);

    // ✅ Email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // ✅ Phone 10 digits
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone must be 10 digits";
    }

    // ✅ Unique email check (exclude current patient)
    $check = $conn->prepare("SELECT id FROM patients WHERE email=? AND id!=?");
    $check->bind_param("si", $email, $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $errors[] = "Email already used by another patient";
    }

    // ✅ If no errors → update
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE patients SET patient_name=?, email=?, phone=?, age=?, gender=?, diagnosis=? WHERE id=?");
        $stmt->bind_param("sssissi", $name, $email, $phone, $age, $gender, $diagnosis, $id);
        $stmt->execute();

        echo "<div class='alert alert-success'>Patient Updated Successfully!</div>";

        // Refresh shown data
        $patient = [
            'patient_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'age' => $age,
            'gender' => $gender,
            'diagnosis' => $diagnosis
        ];
    } else {
        foreach($errors as $error){
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
}
?>

<h3>Edit Patient</h3>

<form method="POST">
    <input name="name" value="<?= $patient['patient_name'] ?>" class="form-control" required><br>
    <input name="email" value="<?= $patient['email'] ?>" class="form-control" required><br>
    <input name="phone" value="<?= $patient['phone'] ?>" class="form-control" required><br>
    <input name="age" type="number" value="<?= $patient['age'] ?>" class="form-control" required><br>
    <input name="gender" value="<?= $patient['gender'] ?>" class="form-control" required><br>
    <input name="diagnosis" value="<?= $patient['diagnosis'] ?>" class="form-control" required><br>
    <button class="btn btn-warning">Update Patient</button>
</form>

<?php include('../includes/footer.php'); ?>
