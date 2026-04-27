<?php
require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');

$id = $_GET['edit'] ?? null;

/* =========================
   DEFAULT DATA
========================= */
$data = [
    'employee_id' => '',
    'employee_name' => '',
    'employee_father_name' => '',
    'employee_mother_name' => '',
    'employee_email' => '',
    'employee_phone' => '',
    'employee_personal_phone' => '',
    'employee_nid' => '',
    'department_id' => '',
    'designation_id' => '',
    'shift_id' => '',
    'employee_joining_date' => '',
    'employee_date_of_birth' => '',
    'employee_salary' => '',
    'employee_address' => '',
    'employee_education' => '',
    'employee_reference_name' => '',
    'employee_reference_mobile' => '',
    'employee_image' => '',
    'employee_password' => '',
    'employee_role' => 'employee',
    'employee_status' => 'active'
];

/* =========================
   LOAD FOR EDIT
========================= */
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
}

/* =========================
   SAVE / UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? null;

    /* MANUAL EMPLOYEE ID */
    $employee_id = $_POST['employee_id'] ?? '';

    $name = $_POST['employee_name'] ?? '';
    $father = $_POST['employee_father_name'] ?? '';
    $mother = $_POST['employee_mother_name'] ?? '';
    $email = $_POST['employee_email'] ?? '';
    $phone = $_POST['employee_phone'] ?? '';
    $personal_phone = $_POST['employee_personal_phone'] ?? '';
    $nid = $_POST['employee_nid'] ?? '';

    $dept = $_POST['department_id'] ?: null;
    $desig = $_POST['designation_id'] ?: null;
    $shift = $_POST['shift_id'] ?: null;

    $join = $_POST['employee_joining_date'] ?: null;
    $dob = $_POST['employee_date_of_birth'] ?: null;

    $salary = $_POST['employee_salary'] ?: 0;
    $address = $_POST['employee_address'] ?? '';
    $education = $_POST['employee_education'] ?? '';

    $ref_name = $_POST['employee_reference_name'] ?? '';
    $ref_mobile = $_POST['employee_reference_mobile'] ?? '';

    $role = $_POST['employee_role'] ?? 'employee';
    $status = $_POST['employee_status'] ?? 'active';

    /* PASSWORD */
    $password = !empty($_POST['employee_password'])
        ? password_hash($_POST['employee_password'], PASSWORD_BCRYPT)
        : '';

    /* IMAGE */
    $imageName = $data['employee_image'];

    if (!empty($_FILES['employee_image']['name'])) {

        $uploadPath = BASE_PATH . "/employee/uploads/";
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

        $ext = pathinfo($_FILES['employee_image']['name'], PATHINFO_EXTENSION);
        $imageName = "EMP_" . time() . "." . $ext;

        move_uploaded_file($_FILES['employee_image']['tmp_name'], $uploadPath . $imageName);
    }

    /* =========================
       UPDATE
    ========================= */
    if ($id) {

        $sql = "UPDATE employees SET
            employee_id=?,
            employee_name=?,
            employee_father_name=?,
            employee_mother_name=?,
            employee_email=?,
            employee_phone=?,
            employee_personal_phone=?,
            employee_nid=?,
            department_id=?,
            designation_id=?,
            shift_id=?,
            employee_joining_date=?,
            employee_date_of_birth=?,
            employee_salary=?,
            employee_address=?,
            employee_education=?,
            employee_reference_name=?,
            employee_reference_mobile=?,
            employee_image=?,
            employee_password=?,
            employee_role=?,
            employee_status=?
            WHERE id=?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssssssssiiisssssssssssi",
            $employee_id,
            $name,
            $father,
            $mother,
            $email,
            $phone,
            $personal_phone,
            $nid,
            $dept,
            $desig,
            $shift,
            $join,
            $dob,
            $salary,
            $address,
            $education,
            $ref_name,
            $ref_mobile,
            $imageName,
            $password,
            $role,
            $status,
            $id
        );

        $stmt->execute();
    }

    /* =========================
       INSERT
    ========================= */
    else {

        $sql = "INSERT INTO employees (
            employee_id,
            employee_name,
            employee_father_name,
            employee_mother_name,
            employee_email,
            employee_phone,
            employee_personal_phone,
            employee_nid,
            department_id,
            designation_id,
            shift_id,
            employee_joining_date,
            employee_date_of_birth,
            employee_salary,
            employee_address,
            employee_education,
            employee_reference_name,
            employee_reference_mobile,
            employee_image,
            employee_password,
            employee_role,
            employee_status
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $conn->prepare($sql);

        $password = password_hash($_POST['employee_password'], PASSWORD_BCRYPT);

        $stmt->bind_param(
            "ssssssssiiissssssssss",
            $employee_id,
            $name,
            $father,
            $mother,
            $email,
            $phone,
            $personal_phone,
            $nid,
            $dept,
            $desig,
            $shift,
            $join,
            $dob,
            $salary,
            $address,
            $education,
            $ref_name,
            $ref_mobile,
            $imageName,
            $password,
            $role,
            $status
        );

        $stmt->execute();
    }

    header("Location: index.php");
    exit;
}
?>

<?php include(BASE_PATH . '/includes/header.php'); ?>

<div class="card shadow">
<div class="card-header bg-primary text-white">
    <?= $id ? "Edit Employee" : "Add Employee" ?>
</div>

<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?= $id ?>">

<div class="row">

<!-- EMPLOYEE ID -->
<div class="col-md-6 mb-2">
<label>Employee ID</label>
<input type="text" name="employee_id" class="form-control"
value="<?= $data['employee_id'] ?>">
</div>

<!-- NAME -->
<div class="col-md-6 mb-2">
<label>Name</label>
<input type="text" name="employee_name" class="form-control"
value="<?= $data['employee_name'] ?>">
</div>

<!-- FATHER -->
<div class="col-md-6 mb-2">
<label>Father Name</label>
<input type="text" name="employee_father_name" class="form-control"
value="<?= $data['employee_father_name'] ?>">
</div>

<!-- MOTHER -->
<div class="col-md-6 mb-2">
<label>Mother Name</label>
<input type="text" name="employee_mother_name" class="form-control"
value="<?= $data['employee_mother_name'] ?>">
</div>

<!-- EMAIL -->
<div class="col-md-6 mb-2">
<label>Email</label>
<input type="email" name="employee_email" class="form-control"
value="<?= $data['employee_email'] ?>">
</div>

<!-- PHONE -->
<div class="col-md-6 mb-2">
<label>Phone</label>
<input type="text" name="employee_phone" class="form-control"
value="<?= $data['employee_phone'] ?>">
</div>

<!-- PERSONAL PHONE -->
<div class="col-md-6 mb-2">
<label>Personal Phone</label>
<input type="text" name="employee_personal_phone" class="form-control"
value="<?= $data['employee_personal_phone'] ?>">
</div>

<!-- NID -->
<div class="col-md-6 mb-2">
<label>NID</label>
<input type="text" name="employee_nid" class="form-control"
value="<?= $data['employee_nid'] ?>">
</div>

<!-- DEPARTMENT -->
<div class="col-md-6 mb-2">
<label>Department</label>
<select name="department_id" class="form-control">
<option value="">Select</option>
<?php
$dept = mysqli_query($conn,"SELECT * FROM departments WHERE status='active'");
while($d = mysqli_fetch_assoc($dept)){
?>
<option value="<?= $d['id'] ?>" <?= $data['department_id']==$d['id']?'selected':'' ?>>
<?= $d['department_name'] ?>
</option>
<?php } ?>
</select>
</div>

<!-- DESIGNATION -->
<div class="col-md-6 mb-2">
<label>Designation</label>
<select name="designation_id" class="form-control">
<option value="">Select</option>
<?php
$des = mysqli_query($conn,"SELECT * FROM designations WHERE status='active'");
while($d = mysqli_fetch_assoc($des)){
?>
<option value="<?= $d['id'] ?>" <?= $data['designation_id']==$d['id']?'selected':'' ?>>
<?= $d['designation_name'] ?>
</option>
<?php } ?>
</select>
</div>

<!-- SHIFT -->
<div class="col-md-6 mb-2">
<label>Shift</label>
<select name="shift_id" class="form-control">
<option value="">Select</option>
<?php
$shift = mysqli_query($conn,"SELECT * FROM shifts WHERE status='active'");
while($s = mysqli_fetch_assoc($shift)){
?>
<option value="<?= $s['id'] ?>" <?= $data['shift_id']==$s['id']?'selected':'' ?>>
<?= $s['shift_name'] ?>
</option>
<?php } ?>
</select>
</div>

<!-- JOINING DATE -->
<div class="col-md-6 mb-2">
<label>Joining Date</label>
<input type="date" name="employee_joining_date" class="form-control"
value="<?= $data['employee_joining_date'] ?>">
</div>

<!-- DOB -->
<div class="col-md-6 mb-2">
<label>Date of Birth</label>
<input type="date" name="employee_date_of_birth" class="form-control"
value="<?= $data['employee_date_of_birth'] ?>">
</div>

<!-- SALARY -->
<div class="col-md-6 mb-2">
<label>Salary</label>
<input type="number" name="employee_salary" class="form-control"
value="<?= $data['employee_salary'] ?>">
</div>

<!-- ADDRESS -->
<div class="col-md-6 mb-2">
<label>Address</label>
<textarea name="employee_address" class="form-control"><?= $data['employee_address'] ?></textarea>
</div>

<!-- EDUCATION -->
<div class="col-md-6 mb-2">
<label>Education</label>
<input type="text" name="employee_education" class="form-control"
value="<?= $data['employee_education'] ?>">
</div>

<!-- REFERENCE -->
<div class="col-md-6 mb-2">
<label>Reference Name</label>
<input type="text" name="employee_reference_name" class="form-control"
value="<?= $data['employee_reference_name'] ?>">
</div>

<div class="col-md-6 mb-2">
<label>Reference Mobile</label>
<input type="text" name="employee_reference_mobile" class="form-control"
value="<?= $data['employee_reference_mobile'] ?>">
</div>

<!-- PASSWORD -->
<div class="col-md-6 mb-2">
<label>Password</label>
<input type="password" name="employee_password" class="form-control">
</div>

<!-- ROLE -->
<div class="col-md-6 mb-2">
<label>Role</label>
<select name="employee_role" class="form-control">
<option value="admin" <?= $data['employee_role']=='admin'?'selected':'' ?>>Admin</option>
<option value="employee" <?= $data['employee_role']=='employee'?'selected':'' ?>>Employee</option>
</select>
</div>

<!-- IMAGE -->
<div class="col-md-6 mb-2">
<label>Image</label>
<input type="file" name="employee_image" class="form-control">
</div>

<!-- STATUS -->
<div class="col-md-6 mb-2">
<label>Status</label>
<select name="employee_status" class="form-control">
<option value="active" <?= $data['employee_status']=='active'?'selected':'' ?>>Active</option>
<option value="inactive" <?= $data['employee_status']=='inactive'?'selected':'' ?>>Inactive</option>
<option value="resign" <?= $data['employee_status']=='resign'?'selected':'' ?>>Resign</option>
</select>
</div>

</div>

<button class="btn btn-success mt-3">
Save Employee
</button>

</form>

</div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>