<?php
date_default_timezone_set('Asia/Dhaka');

require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');

include(BASE_PATH . '/includes/header.php');

$employee_id = $_SESSION['employee_id'];
$role = $_SESSION['employee_role'] ?? 'user';

$message = "";

/*
========================================
STATUS UPDATE (ADMIN ONLY)
========================================
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $role == 'admin') {

    $id = $_POST['id'];
    $status = $_POST['leave_status'];

    $stmt = $conn->prepare("
        UPDATE leaves
        SET leave_status=?
        WHERE id=?
    ");

    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Status Updated!</div>";
    }
}

/*
========================================
DATA FETCH
========================================
*/
if ($role == 'admin') {

    $sql = "
        SELECT l.*, e.employee_name
        FROM leaves l
        LEFT JOIN employees e 
        ON l.employee_id = e.employee_id
        ORDER BY l.id DESC
    ";

    $stmt = $conn->prepare($sql);

} else {

    $sql = "
        SELECT l.*, e.employee_name
        FROM leaves l
        LEFT JOIN employees e 
        ON l.employee_id = e.employee_id
        WHERE l.employee_id=?
        ORDER BY l.id DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $employee_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4 mb-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4>Leave List</h4>
        </div>

        <div class="card-body">

            <?php echo $message; ?>

            <div class="table-responsive">

                <table class="table table-bordered text-center align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Days</th>
                            <th>Address</th>
                            <th>Reason</th>
                            <th>Status</th>

                            <?php if ($role == 'admin') { ?>
                                <th>Action</th>
                            <?php } ?>
                        </tr>
                    </thead>

                    <tbody>

                    <?php
                    $sl = 1;

                    if ($result->num_rows > 0) {

                        while ($row = $result->fetch_assoc()) {
                    ?>

                        <tr>

                            <td><?php echo $sl++; ?></td>

                            <td><?php echo $row['employee_id']; ?></td>

                            <td><?php echo $row['employee_name']; ?></td>

                            <td><?php echo $row['leave_type']; ?></td>

                            <td>
                                <?php echo $row['start_date'].' - '.$row['end_date']; ?>
                            </td>

                            <td><?php echo $row['total_days']; ?></td>

                            <td><?php echo $row['address_during_leave']; ?></td>

                            <td><?php echo $row['reason']; ?></td>

                            <!-- STATUS -->
                            <td>
                                <?php
                                if ($row['leave_status'] == 'pending') {
                                    echo "<span class='badge bg-warning text-dark'>Pending</span>";
                                } elseif ($row['leave_status'] == 'approved') {
                                    echo "<span class='badge bg-success'>Approved</span>";
                                } else {
                                    echo "<span class='badge bg-danger'>Rejected</span>";
                                }
                                ?>
                            </td>

                            <!-- ADMIN ACTION -->
                            <?php if ($role == 'admin') { ?>
                                <td>

                                    <form method="POST" class="d-flex gap-1">

                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                        <select name="leave_status" class="form-select form-select-sm">

                                            <option value="pending"
                                                <?php if ($row['leave_status'] == 'pending') echo 'selected'; ?>>
                                                Pending
                                            </option>

                                            <option value="approved"
                                                <?php if ($row['leave_status'] == 'approved') echo 'selected'; ?>>
                                                Approved
                                            </option>

                                            <option value="rejected"
                                                <?php if ($row['leave_status'] == 'rejected') echo 'selected'; ?>>
                                                Rejected
                                            </option>

                                        </select>

                                        <button class="btn btn-primary btn-sm">
                                            Save
                                        </button>

                                    </form>

                                </td>
                            <?php } ?>

                        </tr>

                    <?php
                        }
                    } else {
                    ?>

                        <tr>
                            <td colspan="10">No Leave Found</td>
                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>