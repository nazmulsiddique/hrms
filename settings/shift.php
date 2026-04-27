<?php
require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');
$id = $_GET['edit'] ?? null;

/* =========================
   DEFAULT DATA
========================= */
$data = [
    'shift_name' => '',
    'start_time' => '',
    'end_time' => '',
    'late_after' => '',
    'early_leave_before' => '',
    'status' => 'active'
];

/* =========================
   LOAD FOR EDIT
========================= */
if (!empty($id)) {
    $stmt = $conn->prepare("SELECT * FROM shifts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
}

/* =========================
   INSERT / UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $shift_name = $_POST['shift_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $late_after = !empty($_POST['late_after']) ? $_POST['late_after'] : null;
    $early_leave_before = !empty($_POST['early_leave_before']) ? $_POST['early_leave_before'] : null;
    $status = $_POST['status'];

    if (!empty($_POST['id'])) {

        // UPDATE
        $stmt = $conn->prepare("
            UPDATE shifts SET
                shift_name=?,
                start_time=?,
                end_time=?,
                late_after=?,
                early_leave_before=?,
                status=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "ssssssi",
            $shift_name,
            $start_time,
            $end_time,
            $late_after,
            $early_leave_before,
            $status,
            $_POST['id']
        );

        $stmt->execute();

    } else {

        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO shifts 
            (shift_name, start_time, end_time, late_after, early_leave_before, status)
            VALUES (?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "ssssss",
            $shift_name,
            $start_time,
            $end_time,
            $late_after,
            $early_leave_before,
            $status
        );

        $stmt->execute();
    }

    header("Location: shift.php");
    exit;
}

/* =========================
   DELETE (soft optional)
========================= */
if (!empty($_GET['delete'])) {
    $delId = (int) $_GET['delete'];
    mysqli_query($conn, "UPDATE shifts SET status='inactive' WHERE id=$delId");
    header("Location: shift.php");
    exit;
}
?>

<?php include(BASE_PATH . '/includes/header.php'); ?>

<div class="container-fluid">

<div class="row">

<!-- =========================
     FORM SECTION
========================= -->
<div class="col-md-4">

<div class="card shadow">
<div class="card-header bg-primary text-white">
    <?php echo !empty($id) ? "Edit Shift" : "Add Shift"; ?>
</div>

<div class="card-body">

<form method="POST">

<input type="hidden" name="id" value="<?php echo $id ?? ''; ?>">

<div class="mb-2">
<label>Shift Name</label>
<input type="text" name="shift_name" class="form-control"
value="<?php echo $data['shift_name']; ?>" required>
</div>

<div class="mb-2">
<label>Start Time</label>
<input type="time" name="start_time" class="form-control"
value="<?php echo $data['start_time']; ?>" required>
</div>

<div class="mb-2">
<label>End Time</label>
<input type="time" name="end_time" class="form-control"
value="<?php echo $data['end_time']; ?>" required>
</div>

<div class="mb-2">
<label>Late After</label>
<input type="time" name="late_after" class="form-control"
value="<?php echo $data['late_after']; ?>">
</div>

<div class="mb-2">
<label>Early Leave Before</label>
<input type="time" name="early_leave_before" class="form-control"
value="<?php echo $data['early_leave_before']; ?>">
</div>

<div class="mb-2">
<label>Status</label>
<select name="status" class="form-control">
<option value="active" <?php if($data['status']=='active') echo 'selected'; ?>>Active</option>
<option value="inactive" <?php if($data['status']=='inactive') echo 'selected'; ?>>Inactive</option>
</select>
</div>

<button class="btn btn-success w-100 mt-2">
Save Shift
</button>

</form>

</div>
</div>

</div>

<!-- =========================
     LIST SECTION
========================= -->
<div class="col-md-8">

<div class="card shadow">

<div class="card-header bg-dark text-white">
    Shift List
</div>

<div class="card-body p-0">

<table class="table table-bordered table-striped mb-0">

<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Shift</th>
    <th>Start</th>
    <th>End</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
$result = mysqli_query($conn, "SELECT * FROM shifts ORDER BY id DESC");
while ($row = mysqli_fetch_assoc($result)) {
?>

<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['shift_name']; ?></td>
    <td><?php echo $row['start_time']; ?></td>
    <td><?php echo $row['end_time']; ?></td>
    <td>
        <span class="badge bg-<?php echo $row['status']=='active'?'success':'danger'; ?>">
            <?php echo $row['status']; ?>
        </span>
    </td>
    <td>
        <a href="shift.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
            Edit
        </a>

        <a href="shift.php?delete=<?php echo $row['id']; ?>" 
           onclick="return confirm('Are you sure?')" 
           class="btn btn-sm btn-danger">
            Delete
        </a>
    </td>
</tr>

<?php } ?>

</tbody>

</table>

</div>
</div>

</div>

</div>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>