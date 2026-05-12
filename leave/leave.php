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


// Leave balance query for report
$current_year = date('Y');

/*
==================================================
GET EMPLOYEE LEAVE BALANCE
==================================================
*/
if ($role == 'admin') {

    $balance_sql = "
        SELECT 
            b.*,
            e.employee_name
        FROM leave_balance b
        LEFT JOIN employees e
            ON b.employee_id = e.employee_id
        WHERE b.year = ?
        ORDER BY e.employee_name ASC
    ";

    $balance_stmt = $conn->prepare($balance_sql);
    $balance_stmt->bind_param("i", $current_year);

} else {

    $balance_sql = "
        SELECT 
            b.*,
            e.employee_name
        FROM leave_balance b
        LEFT JOIN employees e
            ON b.employee_id = e.employee_id
        WHERE b.employee_id = ?
          AND b.year = ?
        ORDER BY e.employee_name ASC
    ";

    $balance_stmt = $conn->prepare($balance_sql);
    $balance_stmt->bind_param("si", $employee_id, $current_year);
}

$balance_stmt->execute();
$balance_result = $balance_stmt->get_result();

/*
==================================================
FUNCTION: GET APPROVED LEAVE DAYS
==================================================
*/
function getApprovedLeaveDays($conn, $employee_id, $leave_type, $year) {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_days), 0) AS used_days
        FROM leaves
        WHERE employee_id = ?
          AND leave_type = ?
          AND leave_status = 'approved'
          AND YEAR(start_date) = ?
    ");

    $stmt->bind_param("ssi", $employee_id, $leave_type, $year);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();

    return (int)$row['used_days'];
}
?>

<div class="container mt-4 mb-5">

<!-- LEAVE BALANCE CARD -->
<div class="card shadow my-4">

    <div class="card-header bg-success text-white">
        <h4 class="mb-0">
            Leave Balance Report (<?php echo $current_year; ?>)
        </h4>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-striped text-center align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Year</th>

                        <th>CL<br><small>Assigned / Used / Remaining</small></th>
                        <th>ML<br><small>Assigned / Used / Remaining</small></th>
                        <th>BL<br><small>Assigned / Used / Remaining</small></th>
                        <th>With Pay<br><small>Assigned / Used / Remaining</small></th>
                        <th>Without Pay<br><small>Assigned / Used / Remaining</small></th>
                        <th>Others<br><small>Assigned / Used / Remaining</small></th>

                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($balance_result->num_rows > 0) { ?>

                    <?php while ($bal = $balance_result->fetch_assoc()) { ?>

                        <?php
                        $year = (int)$bal['year'];
                        $emp_id = $bal['employee_id'];

                        // Approved leave used
                        $cl_used   = getApprovedLeaveDays($conn, $emp_id, 'CL', $year);
                        $ml_used   = getApprovedLeaveDays($conn, $emp_id, 'ML', $year);
                        $bl_used   = getApprovedLeaveDays($conn, $emp_id, 'BL', $year);
                        $wpl_used  = getApprovedLeaveDays($conn, $emp_id, 'WITH PAY LEAVE', $year);
                        $wopl_used = getApprovedLeaveDays($conn, $emp_id, 'WITHOUT PAY LEAVE', $year);
                        $oth_used  = getApprovedLeaveDays($conn, $emp_id, 'OTHERS', $year);

                        // Remaining balance
                        $cl_rem   = $bal['cl_balance'] - $cl_used;
                        $ml_rem   = $bal['ml_balance'] - $ml_used;
                        $bl_rem   = $bal['bl_balance'] - $bl_used;
                        $wpl_rem  = $bal['with_pay_balance'] - $wpl_used;
                        $wopl_rem = $bal['without_pay_balance'] - $wopl_used;
                        $oth_rem  = $bal['others_balance'] - $oth_used;
                        ?>

                        <tr>

                            <td><?php echo htmlspecialchars($emp_id); ?></td>

                            <td><?php echo htmlspecialchars($bal['employee_name']); ?></td>

                            <td><?php echo $year; ?></td>

                            <td>
                                <?php echo $bal['cl_balance']; ?>
                                / <?php echo $cl_used; ?>
                                / <strong><?php echo $cl_rem; ?></strong>
                            </td>

                            <td>
                                <?php echo $bal['ml_balance']; ?>
                                / <?php echo $ml_used; ?>
                                / <strong><?php echo $ml_rem; ?></strong>
                            </td>

                            <td>
                                <?php echo $bal['bl_balance']; ?>
                                / <?php echo $bl_used; ?>
                                / <strong><?php echo $bl_rem; ?></strong>
                            </td>

                            <td>
                                <?php echo $bal['with_pay_balance']; ?>
                                / <?php echo $wpl_used; ?>
                                / <strong><?php echo $wpl_rem; ?></strong>
                            </td>

                            <td>
                                <?php echo $bal['without_pay_balance']; ?>
                                / <?php echo $wopl_used; ?>
                                / <strong><?php echo $wopl_rem; ?></strong>
                            </td>

                            <td>
                                <?php echo $bal['others_balance']; ?>
                                / <?php echo $oth_used; ?>
                                / <strong><?php echo $oth_rem; ?></strong>
                            </td>

                            <td>
                                <?php if ($bal['status'] == 'active') { ?>
                                    <span class="badge bg-success">Active</span>
                                <?php } else { ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php } ?>
                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="10">
                            No leave balance found for <?php echo $current_year; ?>.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            <small class="text-muted">
                Format: <strong>Assigned / Approved Used / Remaining</strong>
            </small>
        </div>

    </div>

</div>


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