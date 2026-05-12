<?php 
require_once(__DIR__ . '/config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');
include("includes/header.php"); 
$employee_id = $_SESSION['employee_id'];
$role = $_SESSION['employee_role'] ?? 'employee';

/*
====================================
SHIFT FUNCTION (SIMPLE)
====================================
*/
function getShift($conn, $shift_name)
{
    $stmt = $conn->prepare("SELECT * FROM shifts WHERE shift_name=? LIMIT 1");
    $stmt->bind_param("s", $shift_name);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}


/*
====================================
MAIN QUERY
====================================
*/
$sql = "
SELECT 
    e.employee_id,
    e.employee_name,
    e.shift_id,
    s.shift_name,
    s.start_time,
    s.end_time,
    s.late_after,
    s.early_leave_before,
    a.in_time,
    a.out_time
FROM employees e
LEFT JOIN shifts s 
    ON e.shift_id = s.id
LEFT JOIN attendance a 
    ON e.employee_id = a.employee_id 
    AND a.attendance_date = CURDATE()
";

if ($role != 'admin') {
    $sql .= " WHERE e.employee_id = ?";
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL ERROR: " . $conn->error . "<br>" . $sql);
}

if ($role != 'admin') {
    $stmt->bind_param("s", $employee_id);
}

$stmt->execute();
$result = $stmt->get_result();


// Last 30 days attendance summary query

$sql1 = "
SELECT 
    e.employee_id,
    e.employee_name,
    e.shift_id,
    s.shift_name,
    s.start_time,
    s.end_time,
    s.late_after,
    s.early_leave_before,
    a.attendance_date,
    a.in_time,
    a.out_time
FROM employees e
LEFT JOIN shifts s 
    ON e.shift_id = s.id
LEFT JOIN attendance a 
    ON e.employee_id = a.employee_id
WHERE e.employee_id = ?
AND a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY a.attendance_date DESC
";
$stmt1 = $conn->prepare($sql1);

if (!$stmt1) {
    die("SQL ERROR: " . $conn->error);
}

$stmt1->bind_param("s", $employee_id);
$stmt1->execute();
$result1 = $stmt1->get_result();


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


    <h3 class="mb-4">Dashboard</h3>

    <div class="row">

        <?php
        $total_emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM employees"));
        ?>

        <!-- Card 1 -->
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Total Employees</h6>
                        <h3><?php echo $total_emp['total']; ?></h3>
                    </div>
                    <i class="fa fa-users fa-2x text-primary"></i>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Today Attendance</h6>
                        <h3>
                            <?php
                            // $today = date('Y-m-d');
                            // $att = mysqli_fetch_assoc(mysqli_query($conn,
                            //     "SELECT COUNT(*) as total FROM attendance WHERE date='$today'"
                            // ));
                            // echo $att['total'];
                            ?>
                        </h3>
                    </div>
                    <i class="fa fa-clock fa-2x text-success"></i>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Absent</h6>
                        <h3>
                            <?php
                            // $absent = $total_emp['total'] - $att['total'];
                            // echo $absent;
                            ?>
                        </h3>
                    </div>
                    <i class="fa fa-user-times fa-2x text-danger"></i>
                </div>
            </div>
        </div>

    </div>


<div class="in-out-summary">
    <div class="container mt-4">
    <div class="card shadow">

    <div class="card-header bg-primary text-white">
        <h4>Employee IN / OUT Dashboard</h4>
    </div>

    <div class="card-body">

    <table class="table table-bordered text-center">

    <thead class="table-dark">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Shift</th>
        <th>IN Time</th>
        <th>OUT Time</th>
        <th>Status</th>
    </tr>
    </thead>

    <tbody>

    <?php while ($row = $result->fetch_assoc()) { ?>

    <?php
    /*
    ====================================
    TIME SAFE CHECK
    ====================================
    */
    $shift_in = strtotime($row['start_time'] ?? '09:00:00');
    $shift_out = strtotime($row['end_time'] ?? '18:00:00');
    $late_after = strtotime($row['late_after'] ?? '09:15:00');
    $early_leave = strtotime($row['early_leave_before'] ?? '17:45:00');

    $in_time = !empty($row['in_time']) ? strtotime($row['in_time']) : 0;
    $out_time = !empty($row['out_time']) ? strtotime($row['out_time']) : 0;

    /*
    ====================================
    STATUS LOGIC
    ====================================
    */
    $status = "Absent";
    $badge = "danger";

    if ($in_time && $out_time) {

        if ($in_time <= $shift_in && $out_time >= $shift_out) {
            $status = "Present";
            $badge = "success";
        }
        elseif ($in_time > $late_after) {
            $status = "Late";
            $badge = "warning";
        }
        elseif ($out_time < $early_leave) {
            $status = "Early Leave";
            $badge = "info";
        }
        else {
            $status = "Normal";
            $badge = "primary";
        }

    }
    elseif ($in_time && !$out_time) {
        $status = "IN Only";
        $badge = "warning";
    }
    elseif (!$in_time && $out_time) {
        $status = "OUT Only";
        $badge = "secondary";
    }
    ?>

    <tr>
        <td><?= $row['employee_id'] ?></td>
        <td><?= $row['employee_name'] ?></td>
        <td>
            <?= $row['shift_name'] ?>
            (
            <?= date('H:i', strtotime($row['start_time'])) ?>
            -
            <?= date('H:i', strtotime($row['end_time'])) ?>
            )
        </td>

        <td>
            <?= $row['in_time'] ? date('h:i A', strtotime($row['in_time'])) : '-' ?>
        </td>

        <td>
            <?= $row['out_time'] ? date('h:i A', strtotime($row['out_time'])) : '-' ?>
        </td>

        <td>
            <span class="badge bg-<?= $badge ?>">
                <?= $status ?>
            </span>
        </td>
    </tr>

    <?php } ?>

    </tbody>

    </table>

    </div>
    </div>
    </div>
</div>

<div class="30-days-attendance">
   <div class="container mt-4">

    <div class="card shadow">

    <div class="card-header bg-dark text-white">
        <h4>My Last 30 Days Attendance (Shift Wise)</h4>
    </div>

    <div class="card-body">

    <table class="table table-bordered text-center">

    <thead class="table-dark">
    <tr>
        <th>Date</th>
        <th>Shift</th>
        <th>IN</th>
        <th>OUT</th>
        <th>Status</th>
    </tr>
    </thead>

    <tbody>

    <?php while ($row = $result1->fetch_assoc()) { ?>

    <?php
    /*
    ========================================
    SHIFT TIMES
    ========================================
    */
    $shift_in = strtotime($row['start_time'] ?? '09:00:00');
    $shift_out = strtotime($row['end_time'] ?? '18:00:00');
    $late_after = strtotime($row['late_after'] ?? '09:15:00');
    $early_leave = strtotime($row['early_leave_before'] ?? '17:45:00');

    /*
    ========================================
    ATTENDANCE TIME
    ========================================
    */
    $in_time = !empty($row['in_time']) ? strtotime($row['in_time']) : 0;
    $out_time = !empty($row['out_time']) ? strtotime($row['out_time']) : 0;

    /*
    ========================================
    STATUS LOGIC
    ========================================
    */
    $status = "Absent";
    $badge = "danger";

    if ($in_time && $out_time) {

        if ($in_time <= $shift_in && $out_time >= $shift_out) {
            $status = "Present";
            $badge = "success";
        }
        elseif ($in_time > $late_after) {
            $status = "Late";
            $badge = "warning";
        }
        elseif ($out_time < $early_leave) {
            $status = "Early Leave";
            $badge = "info";
        }
        else {
            $status = "Normal";
            $badge = "primary";
        }

    }
    elseif ($in_time && !$out_time) {
        $status = "IN Only";
        $badge = "warning";
    }
    elseif (!$in_time && $out_time) {
        $status = "OUT Only";
        $badge = "secondary";
    }
    ?>

    <tr>

        <td>
            <?= !empty($row['attendance_date']) 
                ? date('d-m-Y', strtotime($row['attendance_date'])) 
                : '-' ?>
        </td>

        <td>
            <?= $row['shift_name'] ?>
            (
            <?= date('H:i', strtotime($row['start_time'])) ?>
            -
            <?= date('H:i', strtotime($row['end_time'])) ?>
            )
        </td>

        <td>
            <?= !empty($row['in_time']) 
                ? date('h:i A', strtotime($row['in_time'])) 
                : '-' ?>
        </td>

        <td>
            <?= !empty($row['out_time']) 
                ? date('h:i A', strtotime($row['out_time'])) 
                : '-' ?>
        </td>

        <td>
            <span class="badge bg-<?= $badge ?>">
                <?= $status ?>
            </span>
        </td>

    </tr>

    <?php } ?>

    </tbody>

    </table>

    </div>

    </div>

    </div>
</div>



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

<?php include("includes/footer.php"); ?>