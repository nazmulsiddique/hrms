<?php
require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');

/* =========================
   LOAD FOR EDIT
========================= */
$id = $_GET['edit'] ?? null;

$designation_name = "";
$department_id = "";
$status = "active";

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM designations WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {
        $designation_name = $data['designation_name'];
        $department_id = $data['department_id'];
        $status = $data['status'];
    }
}

/* =========================
   INSERT / UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['designation_name'];
    $dept = $_POST['department_id'];
    $status = $_POST['status'];

    // UPDATE
    if (!empty($_POST['id'])) {

        $stmt = $conn->prepare("
            UPDATE designations 
            SET designation_name=?, department_id=?, status=? 
            WHERE id=?
        ");
        $stmt->bind_param("sisi", $name, $dept, $status, $_POST['id']);
        $stmt->execute();

    } 
    // INSERT
    else {

        $stmt = $conn->prepare("
            INSERT INTO designations (designation_name, department_id, status)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sis", $name, $dept, $status);
        $stmt->execute();
    }

    header("Location: designation.php");
    exit;
}

?>

<?php include(BASE_PATH . '/includes/header.php'); ?>

<div class="row">

<!-- =========================
     FORM
========================= -->
<div class="col-md-4">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <?php echo $id ? "Edit Designation" : "Add Designation"; ?>
        </div>

        <div class="card-body">

            <form method="POST">

                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <!-- NAME -->
                <label>Designation Name</label>
                <input type="text"
                       name="designation_name"
                       class="form-control mb-3"
                       value="<?php echo $designation_name; ?>"
                       required>

                <!-- DEPARTMENT -->
                <label>Department</label>
                <select name="department_id" class="form-control mb-3" required>
                    <option value="">Select Department</option>

                    <?php
                    $dept = mysqli_query($conn, "SELECT * FROM departments WHERE status='active'");
                    while ($d = mysqli_fetch_assoc($dept)) {
                    ?>
                        <option value="<?php echo $d['id']; ?>"
                            <?php if($department_id == $d['id']) echo 'selected'; ?>>
                            <?php echo $d['department_name']; ?>
                        </option>
                    <?php } ?>

                </select>

                <!-- STATUS -->
                <label>Status</label>
                <select name="status" class="form-control mb-3">
                    <option value="active" <?php if($status=='active') echo 'selected'; ?>>
                        Active
                    </option>
                    <option value="inactive" <?php if($status=='inactive') echo 'selected'; ?>>
                        Inactive
                    </option>
                </select>

                <button class="btn btn-success">
                    <i class="fa fa-save"></i>
                    <?php echo $id ? "Update" : "Save"; ?>
                </button>

                <a href="designation.php" class="btn btn-secondary">Reset</a>

            </form>

        </div>
    </div>

</div>

<!-- =========================
     LIST
========================= -->
<div class="col-md-8">

    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            Designation List
        </div>

        <div class="card-body">

            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $sql = "
                    SELECT d.*, dep.department_name
                    FROM designations d
                    LEFT JOIN departments dep ON d.department_id = dep.id
                    ORDER BY d.id DESC
                ";

                $res = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($res)) {
                ?>
                <tr>
                    <td><?php echo $row['designation_name']; ?></td>

                    <td><?php echo $row['department_name']; ?></td>

                    <td>
                        <?php if ($row['status'] == 'active') { ?>
                            <span class="badge bg-success">Active</span>
                        <?php } else { ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php } ?>
                    </td>

                    <td>

                        <!-- EDIT -->
                        <a href="?edit=<?php echo $row['id']; ?>"
                           class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i>
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

<?php include(BASE_PATH . '/includes/footer.php'); ?>