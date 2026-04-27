<?php
require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');
/* =========================
   LOAD DATA FOR EDIT
========================= */
$id = $_GET['edit'] ?? null;

$department_name = "";
$status = "active";

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM departments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {
        $department_name = $data['department_name'];
        $status = $data['status'];
    }
}

/* =========================
   INSERT / UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['department_name'];
    $status = $_POST['status'];

    // UPDATE
    if (!empty($_POST['id'])) {

        $stmt = $conn->prepare("
            UPDATE departments 
            SET department_name=?, status=? 
            WHERE id=?
        ");
        $stmt->bind_param("ssi", $name, $status, $_POST['id']);
        $stmt->execute();

    } 
    // INSERT
    else {

        $stmt = $conn->prepare("
            INSERT INTO departments (department_name, status)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $name, $status);
        $stmt->execute();
    }

    header("Location: department.php");
    exit;
}

?>

<?php include(BASE_PATH . '/includes/header.php'); ?>

<div class="row">

<!-- =========================
     FORM SECTION
========================= -->
<div class="col-md-4">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <?php echo $id ? "Edit Department" : "Add Department"; ?>
        </div>

        <div class="card-body">

            <form method="POST">

                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <!-- Department Name -->
                <label>Department Name</label>
                <input type="text"
                       name="department_name"
                       class="form-control mb-3"
                       value="<?php echo $department_name; ?>"
                       required>

                <!-- Status -->
                <label>Status</label>
                <select name="status" class="form-control mb-3">
                    <option value="active" <?php if($status=='active') echo 'selected'; ?>>
                        Active
                    </option>
                    <option value="inactive" <?php if($status=='inactive') echo 'selected'; ?>>
                        Inactive
                    </option>
                </select>

                <!-- Button -->
                <button class="btn btn-success">
                    <i class="fa fa-save"></i>
                    <?php echo $id ? "Update" : "Save"; ?>
                </button>

                <a href="department.php" class="btn btn-secondary">Reset</a>

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
            Department List
        </div>

        <div class="card-body">

            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $res = mysqli_query($conn, "SELECT * FROM departments ORDER BY id DESC");

                while ($row = mysqli_fetch_assoc($res)) {
                ?>
                <tr>
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