<?php include('../config/db.php'); ?>
<?php include('../includes/header.php'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark">Patient List</h2>
    <a href="create.php" class="btn btn-primary btn-action shadow-sm">+ Add New Patient</a>
</div>

<?php
// pagination
$limit = 5;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $limit;
 
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';

// ✅ Sorting logic (SAFE)
$order = "patient_name ASC";

if ($sort == 'name_desc') $order = "patient_name DESC";
if ($sort == 'age_asc') $order = "age ASC";
if ($sort == 'age_desc') $order = "age DESC";

$search_param = "%$search%";

// ORDER BY cannot be parameterized, so we append it safely
$query = "SELECT patients.*, doctors.doctor_name, doctors.specialization
          FROM patients
          LEFT JOIN doctors ON patients.doctor_id = doctors.id
          WHERE patients.patient_name LIKE ? 
          OR patients.diagnosis LIKE ?
          ORDER BY $order
          LIMIT ?, ?";



$stmt = $conn->prepare($query);
$stmt->bind_param("ssii", $search_param, $search_param, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
// Total records count for pagination
$count_query = $conn->prepare("SELECT COUNT(*) as total FROM patients 
                               WHERE patient_name LIKE ? 
                               OR diagnosis LIKE ?");
$count_query->bind_param("ss", $search_param, $search_param);
$count_query->execute();
$count_result = $count_query->get_result()->fetch_assoc();
$total_records = $count_result['total'];
$total_pages = ceil($total_records / $limit);
?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <form method="GET" class="d-flex gap-2 w-75">

        <input type="text" 
               name="search"
               value="<?= htmlspecialchars($search) ?>" 
               class="form-control"
               placeholder="🔍 Search by name or diagnosis">

        <select name="sort" class="form-select" style="max-width:200px;">
            <option value="">Sort By</option>
            <option value="name_asc" <?= ($sort=='name_asc')?'selected':'' ?>>Name A–Z</option>
            <option value="name_desc" <?= ($sort=='name_desc')?'selected':'' ?>>Name Z–A</option>
            <option value="age_asc" <?= ($sort=='age_asc')?'selected':'' ?>>Age Low–High</option>
            <option value="age_desc" <?= ($sort=='age_desc')?'selected':'' ?>>Age High–Low</option>
        </select>

        <button class="btn btn-primary">Search</button>
    </form>

    <!-- <a href="create.php" class="btn btn-success">+ Add Patient</a> -->

</div>


<div class="card shadow-sm">
<div class="card-body p-0">
<table class="table table-striped table-hover mb-0 align-middle">
<thead>
<tr>
    <th>Patient Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Age</th>
    <th>Gender</th>
    <th>Diagnosis</th>
    <th>Doctor</th>
    <th class="text-end">Actions</th>
</tr>
</thead>

    <tbody>
        <?php $serial = $start + 1; ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr class="align-middle">
    <td class="ps-4 fw-bold text-primary"><?= $row['patient_name'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['phone'] ?></td>
    <td><?= $row['age'] ?> Years</td>
    <td>
        <span class="badge bg-info text-white">
            <?= $row['gender'] ?>
        </span>
    </td>
    <td><?= $row['diagnosis'] ?></td>

    <!-- ✅ Doctor Column -->
    <td>
        <?php if($row['doctor_name']): ?>
            Dr. <?= $row['doctor_name'] ?>
            <div class="small text-muted">
                (<?= $row['specialization'] ?>)
            </div>
        <?php else: ?>
            <span class="text-muted">Not Assigned</span>
        <?php endif; ?>
    </td>

    <td class="text-end pe-4">
        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
           onclick="return confirm('Delete this record?')">Delete</a>
    </td>
</tr>

        <?php endwhile; ?>
    </tbody>
</table>
</div>
</div>

<div class="d-flex justify-content-center mt-4">
    <nav>
        <ul class="pagination">
            <!-- Previous Button -->
            <?php if($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?page=<?= $page-1 ?>&search=<?= $search ?>&sort=<?= $sort ?>">
                       Previous
                    </a>
                </li>
            <?php endif; ?>
            <!-- Page Numbers -->
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" 
                       href="?page=<?= $i ?>&search=<?= $search ?>&sort=<?= $sort ?>">
                       <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <!-- Next Button -->
            <?php if($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?page=<?= $page+1 ?>&search=<?= $search ?>&sort=<?= $sort ?>">
                       Next
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<?php include('../includes/footer.php'); ?>
