<?php
require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');
/* =========================
   SOFT DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("
        UPDATE employees 
        SET employee_status='inactive' 
        WHERE id=?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

include(BASE_PATH . '/includes/header.php');
?>

<div class="card shadow">

<div class="card-header bg-dark text-white d-flex justify-content-between">
    <h5 class="mb-0">Employee List (Full Details)</h5>
    <a href="employee.php" class="btn btn-success btn-sm">
        <i class="fa fa-plus"></i> Add Employee
    </a>
</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover table-sm align-middle">

<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Image</th>

    <th>Name</th>
    <th>Father</th>
    <th>Mother</th>

    <th>Email</th>
    <th>Phone</th>
    <th>Personal</th>
    <th>NID</th>

    <th>Department</th>
    <th>Designation</th>
    <th>Shift</th>

    <th>Join Date</th>
    <th>DOB</th>

    <th>Salary</th>

    <th>Address</th>
    <th>Education</th>

    <th>Reference Name</th>
    <th>Reference Mobile</th>
    <th>User Role</th>

    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php

$sql = "
SELECT 
e.*,
d.department_name,
dg.designation_name,
s.shift_name
FROM employees e
LEFT JOIN departments d ON e.department_id = d.id
LEFT JOIN designations dg ON e.designation_id = dg.id
LEFT JOIN shifts s ON e.shift_id = s.id
ORDER BY e.id DESC
";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
?>

<tr>

    <td><?php echo $row['employee_id']; ?></td>

    <td>
        <?php if (!empty($row['employee_image'])) { ?>
            <img src="<?php echo BASE_URL; ?>employee/uploads/<?php echo $row['employee_image']; ?>"
                 style="width:45px;height:45px;object-fit:cover;border-radius:5px;">
        <?php } ?>
    </td>

    <td><?php echo $row['employee_name']; ?></td>
    <td><?php echo $row['employee_father_name']; ?></td>
    <td><?php echo $row['employee_mother_name']; ?></td>

    <td><?php echo $row['employee_email']; ?></td>
    <td><?php echo $row['employee_phone']; ?></td>
    <td><?php echo $row['employee_personal_phone']; ?></td>
    <td><?php echo $row['employee_nid']; ?></td>

    <td><?php echo $row['department_name'] ?? '-'; ?></td>
    <td><?php echo $row['designation_name'] ?? '-'; ?></td>
    <td><?php echo $row['shift_name'] ?? '-'; ?></td>

    <td><?php echo $row['employee_joining_date']; ?></td>
    <td><?php echo $row['employee_date_of_birth']; ?></td>

    <td><?php echo number_format($row['employee_salary'],2); ?></td>

    <td><?php echo $row['employee_address']; ?></td>
    <td><?php echo $row['employee_education']; ?></td>

    <td><?php echo $row['employee_reference_name']; ?></td>
    <td><?php echo $row['employee_reference_mobile']; ?></td>
    <td><?php echo $row['employee_role']; ?></td>


    <td>
        <?php if ($row['employee_status']=='active') { ?>
            <span class="badge bg-success">Active</span>
        <?php } elseif ($row['employee_status']=='inactive') { ?>
            <span class="badge bg-danger">Inactive</span>
        <?php } else { ?>
            <span class="badge bg-warning">Resign</span>
        <?php } ?>
    </td>

    <td style="white-space:nowrap;">

        <a href="employee.php?edit=<?php echo $row['id']; ?>"
           class="btn btn-warning btn-sm">
            <i class="fa fa-edit"></i>
        </a>

        <a href="?delete=<?php echo $row['id']; ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Deactivate employee?')">
            <i class="fa fa-trash"></i>
        </a>

    </td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>