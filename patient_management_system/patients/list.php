<?php include('../config/db.php'); ?>
<?php include('../includes/header.php'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark">Patient Registry</h2>
    <a href="create.php" class="btn btn-primary btn-action shadow-sm">+ Add New Patient</a>
</div>

<?php
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
$query = "SELECT * FROM patients 
          WHERE patient_name LIKE ? 
          OR diagnosis LIKE ?
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

<div class="row mb-4">
    <div class="col-md-6">
        <form method="GET" class="input-group shadow-sm">
        <input name="search" 
           value="<?= htmlspecialchars($search) ?>" 
           placeholder="Search patients..." 
           class="form-control border-0 py-2 ps-3">

      
    <select name="sort" class="form-select border-0" style="max-width:180px;">
        <option value="">Sort By</option>
        <option value="name_asc" <?= ($sort=='name_asc')?'selected':'' ?>>Name (A-Z)</option>
        <option value="name_desc" <?= ($sort=='name_desc')?'selected':'' ?>>Name (Z-A)</option>
        <option value="age_asc" <?= ($sort=='age_asc')?'selected':'' ?>>Age (Low-High)</option>
        <option value="age_desc" <?= ($sort=='age_desc')?'selected':'' ?>>Age (High-Low)</option>

    </select>

    <button class="btn btn-white bg-white border-0 text-primary px-3" type="submit">🔍</button>
        </form>
    </div>
</div>

<div class="table-responsive table-modern">
<table class="table table-hover mb-0">
    <thead>
        <tr>
            <th class="py-3">ID</th>
            <th class="py-3">Patient Name</th>
            <th class="py-3">Email</th>
            <th class="py-3">Phone</th>
            <th class="py-3">Age</th>
            <th class="py-3">Gender</th>
            <th class="py-3">Diagnosis</th>
            <th class="text-end pe-4 py-3">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php $serial = $start + 1; ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $serial++ ?></td>
            <td><?= htmlspecialchars($row['patient_name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= $row['age'] ?> Years</td>
            <td>
                <span class="badge bg-<?= ($row['gender']=='Male') ? 'info' : 'danger' ?>">
                    <?= $row['gender'] ?>
                </span>
            </td>
            <td><?= htmlspecialchars($row['diagnosis']) ?></td>
            <td class="text-end">
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

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