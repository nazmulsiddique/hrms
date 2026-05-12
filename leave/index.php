<?php
session_start();
date_default_timezone_set('Asia/Dhaka');

require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');

if (!isset($_SESSION['employee_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$employee_id   = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];
$role          = $_SESSION['employee_role'] ?? 'user';

$message = "";

/*
==================================================
INSERT / UPDATE LEAVE
==================================================
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $_POST['id'] ?? '';

    $leave_type = trim($_POST['leave_type']);
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $total_days = (int)($_POST['total_days'] ?? 0);
    $address    = trim($_POST['address_during_leave']);
    $reason     = trim($_POST['reason']);
    $leave_status = $_POST['leave_status'] ?? 'pending';

    /*
    ==================================================
    UPDATE EXISTING LEAVE
    ==================================================
    */
    if (!empty($id)) {

        if ($role == 'admin') {

            $stmt = $conn->prepare("
                UPDATE leaves
                SET
                    leave_type=?,
                    start_date=?,
                    end_date=?,
                    total_days=?,
                    address_during_leave=?,
                    reason=?,
                    leave_status=?,
                    updated_at=NOW()
                WHERE id=?
            ");

            $stmt->bind_param(
                "sssisssi",
                $leave_type,
                $start_date,
                $end_date,
                $total_days,
                $address,
                $reason,
                $leave_status,
                $id
            );

        } else {

            $stmt = $conn->prepare("
                UPDATE leaves
                SET
                    leave_type=?,
                    start_date=?,
                    end_date=?,
                    total_days=?,
                    address_during_leave=?,
                    reason=?,
                    updated_at=NOW()
                WHERE id=? AND employee_id=?
            ");

            $stmt->bind_param(
                "sssissis",
                $leave_type,
                $start_date,
                $end_date,
                $total_days,
                $address,
                $reason,
                $id,
                $employee_id
            );
        }

        if ($stmt && $stmt->execute()) {
            $message = "<div class='alert alert-success'>Leave Updated Successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Update Failed: " . $conn->error . "</div>";
        }
    }

    /*
    ==================================================
    INSERT NEW LEAVE (WITH BALANCE CHECK)
    ==================================================
    */
    else {

        $year = date('Y', strtotime($start_date));

        // Get leave balance for this employee and year
        $bal = $conn->prepare("
            SELECT *
            FROM leave_balance
            WHERE employee_id=?
              AND year=?
              AND status='active'
            LIMIT 1
        ");

        $bal->bind_param("si", $employee_id, $year);
        $bal->execute();
        $balance = $bal->get_result()->fetch_assoc();

        if (!$balance) {

            $message = "<div class='alert alert-danger'>
                            No active leave balance assigned for year {$year}.
                        </div>";

        } else {

            // Map leave type to balance column
            $column_map = [
                'CL'                 => 'cl_balance',
                'ML'                 => 'ml_balance',
                'BL'                 => 'bl_balance',
                'WITH PAY LEAVE'     => 'with_pay_balance',
                'WITHOUT PAY LEAVE'  => 'without_pay_balance',
                'OTHERS'             => 'others_balance'
            ];

            if (!isset($column_map[$leave_type])) {

                $message = "<div class='alert alert-danger'>Invalid Leave Type.</div>";

            } else {

                $balance_column = $column_map[$leave_type];
                $available_balance = (int)$balance[$balance_column];

                // Sum of already approved leaves of same type in this year
                $used_stmt = $conn->prepare("
                    SELECT COALESCE(SUM(total_days), 0) AS used_days
                    FROM leaves
                    WHERE employee_id=?
                      AND leave_type=?
                      AND leave_status='approved'
                      AND YEAR(start_date)=?
                ");

                $used_stmt->bind_param("ssi", $employee_id, $leave_type, $year);
                $used_stmt->execute();
                $used_row = $used_stmt->get_result()->fetch_assoc();

                $used_days = (int)$used_row['used_days'];
                $remaining_balance = $available_balance - $used_days;

                if ($total_days > $remaining_balance) {

                    $message = "<div class='alert alert-danger'>
                                    Insufficient Leave Balance.<br>
                                    Assigned: {$available_balance} day(s)<br>
                                    Used: {$used_days} day(s)<br>
                                    Remaining: {$remaining_balance} day(s)<br>
                                    Requested: {$total_days} day(s)
                                </div>";

                } else {

                    $stmt = $conn->prepare("
                        INSERT INTO leaves
                        (
                            employee_id,
                            leave_type,
                            start_date,
                            end_date,
                            total_days,
                            address_during_leave,
                            reason,
                            leave_status,
                            created_at
                        )
                        VALUES
                        (
                            ?, ?, ?, ?, ?, ?, ?, 'pending', NOW()
                        )
                    ");

                    $stmt->bind_param(
                        "ssssiss",
                        $employee_id,
                        $leave_type,
                        $start_date,
                        $end_date,
                        $total_days,
                        $address,
                        $reason
                    );

                    if ($stmt->execute()) {
                        $message = "<div class='alert alert-success'>
                                        Leave Applied Successfully (Pending).
                                    </div>";
                    } else {
                        $message = "<div class='alert alert-danger'>
                                        Insert Failed: " . $conn->error . "
                                    </div>";
                    }
                }
            }
        }
    }
}

/*
==================================================
EDIT DATA
==================================================
*/
$editData = null;

if (isset($_GET['edit'])) {

    $edit_id = (int)$_GET['edit'];

    if ($role == 'admin') {

        $edit = $conn->prepare("SELECT * FROM leaves WHERE id=?");
        $edit->bind_param("i", $edit_id);

    } else {

        $edit = $conn->prepare("
            SELECT *
            FROM leaves
            WHERE id=? AND employee_id=?
        ");

        $edit->bind_param("is", $edit_id, $employee_id);
    }

    $edit->execute();
    $editData = $edit->get_result()->fetch_assoc();
}

/*
==================================================
LIST
==================================================
*/
if ($role == 'admin') {

    $list = $conn->prepare("
        SELECT l.*, e.employee_name
        FROM leaves l
        LEFT JOIN employees e
            ON l.employee_id = e.employee_id
        ORDER BY l.id DESC
    ");

} else {

    $list = $conn->prepare("
        SELECT l.*, e.employee_name
        FROM leaves l
        LEFT JOIN employees e
            ON l.employee_id = e.employee_id
        WHERE l.employee_id=?
        ORDER BY l.id DESC
    ");

    $list->bind_param("s", $employee_id);
}

$list->execute();
$result = $list->get_result();


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

include(BASE_PATH . '/includes/header.php');
?>

<div class="container mt-4 mb-5">


<!-- LEAVE BALANCE CARD -->
<div class="card shadow mt-4">

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

    <!-- FORM -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><?php echo !empty($editData) ? 'Update Leave' : 'Leave Application'; ?></h4>
        </div>

        <div class="card-body">

            <?php echo $message; ?>

            <form method="POST">

                <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Employee ID</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($employee_id); ?>" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Employee Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($employee_name); ?>" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Leave Type</label>
                        <select name="leave_type" class="form-control" required>
                            <?php
                            $types = [
                                'CL',
                                'ML',
                                'BL',
                                'WITH PAY LEAVE',
                                'WITHOUT PAY LEAVE',
                                'OTHERS'
                            ];

                            foreach ($types as $type) {
                                $selected = (($editData['leave_type'] ?? '') == $type) ? 'selected' : '';
                                echo "<option value=\"{$type}\" {$selected}>{$type}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <?php if ($role == 'admin') { ?>
                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="leave_status" class="form-control">
                            <?php
                            $statuses = ['pending', 'approved', 'rejected'];
                            foreach ($statuses as $st) {
                                $selected = (($editData['leave_status'] ?? 'pending') == $st) ? 'selected' : '';
                                echo "<option value=\"{$st}\" {$selected}>" . ucfirst($st) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <?php } ?>

                    <div class="col-md-6 mb-3">
                        <label>Start Date</label>
                        <input type="date" id="start_date" name="start_date"
                               class="form-control"
                               required
                               value="<?php echo $editData['start_date'] ?? ''; ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>End Date</label>
                        <input type="date" id="end_date" name="end_date"
                               class="form-control"
                               required
                               value="<?php echo $editData['end_date'] ?? ''; ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Total Leave Days</label>
                        <input type="number" id="total_days" name="total_days"
                               class="form-control"
                               readonly
                               value="<?php echo $editData['total_days'] ?? 0; ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Address During Leave</label>
                        <textarea name="address_during_leave" class="form-control" rows="2"><?php
                            echo htmlspecialchars($editData['address_during_leave'] ?? '');
                        ?></textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Reason</label>
                        <textarea name="reason" class="form-control" rows="3"><?php
                            echo htmlspecialchars($editData['reason'] ?? '');
                        ?></textarea>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success">
                            <?php echo !empty($editData) ? 'Update Leave' : 'Submit Leave'; ?>
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>

    <!-- LIST -->
    <div class="card shadow mt-4">
        <div class="card-header bg-dark text-white">
            <h4>Leave List</h4>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Date Range</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>

                            <td>
                                <?php echo htmlspecialchars($row['employee_id']); ?><br>
                                <?php echo htmlspecialchars($row['employee_name']); ?>
                            </td>

                            <td><?php echo htmlspecialchars($row['leave_type']); ?></td>

                            <td>
                                <?php echo htmlspecialchars($row['start_date']); ?>
                                <br>to<br>
                                <?php echo htmlspecialchars($row['end_date']); ?>
                            </td>

                            <td><?php echo $row['total_days']; ?></td>

                            <td>
                                <?php
                                if ($row['leave_status'] == 'approved') {
                                    echo "<span class='badge bg-success'>Approved</span>";
                                } elseif ($row['leave_status'] == 'rejected') {
                                    echo "<span class='badge bg-danger'>Rejected</span>";
                                } else {
                                    echo "<span class='badge bg-warning text-dark'>Pending</span>";
                                }
                                ?>
                            </td>

                            <td>
                                <a href="?edit=<?php echo $row['id']; ?>"
                                   class="btn btn-sm btn-primary">
                                    Edit
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

<script>
function calcDays() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;

    if (start && end) {
        const d1 = new Date(start);
        const d2 = new Date(end);

        if (d2 >= d1) {
            const diff = Math.floor((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById('total_days').value = diff;
        } else {
            document.getElementById('total_days').value = 0;
        }
    }
}

document.getElementById('start_date').addEventListener('change', calcDays);
document.getElementById('end_date').addEventListener('change', calcDays);
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>
