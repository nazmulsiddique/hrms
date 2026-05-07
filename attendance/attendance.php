<?php
date_default_timezone_set('Asia/Dhaka');

require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');

$employee_role = $_SESSION['employee_role'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $employee_role === 'admin') {

    $attendance_id = intval($_POST['attendance_id']);

    // HTML time input returns HH:MM only
    $in_time_raw = $_POST['in_time'] ?? '';
    $out_time_raw = $_POST['out_time'] ?? '';

    // Fetch attendance date
    $getDate = $conn->prepare("
        SELECT attendance_date 
        FROM attendance 
        WHERE id=?
    ");

    $getDate->bind_param("i", $attendance_id);
    $getDate->execute();
    $dateResult = $getDate->get_result();
    $dateRow = $dateResult->fetch_assoc();

    if ($dateRow) {

        $attendance_date = $dateRow['attendance_date'];

        /*
        ==========================================
        FULL DATETIME FORMAT
        ==========================================
        */
        $final_in_time = null;
        $final_out_time = null;

        if (!empty($in_time_raw)) {
            $final_in_time = date(
                'Y-m-d H:i:s',
                strtotime($attendance_date . ' ' . $in_time_raw)
            );
        }

        if (!empty($out_time_raw)) {
            $final_out_time = date(
                'Y-m-d H:i:s',
                strtotime($attendance_date . ' ' . $out_time_raw)
            );
        }

        /*
        ==========================================
        UPDATE QUERY
        ==========================================
        */
        $stmt = $conn->prepare("
            UPDATE attendance
            SET 
                in_time = ?,
                out_time = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssi",
            $final_in_time,
            $final_out_time,
            $attendance_id
        );

        if ($stmt->execute()) {
            $message = "
                <div class='alert alert-success'>
                    Attendance time updated successfully!
                </div>
            ";
        } else {
            $message = "
                <div class='alert alert-danger'>
                    Update failed: " . $stmt->error . "
                </div>
            ";
        }
    }
}

include(BASE_PATH . '/includes/header.php');

/*
==================================================
FILTERS
==================================================
*/
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date   = $_GET['to_date'] ?? date('Y-m-d');
$employee_filter = $_GET['employee_id'] ?? '';

/*
==================================================
QUERY
==================================================
*/
$sql = "
    SELECT 
        a.*,
        e.employee_name,
        e.employee_role
    FROM attendance a
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    WHERE a.attendance_date BETWEEN ? AND ?
";

$params = [$from_date, $to_date];
$types = "ss";

if (!empty($employee_filter)) {
    $sql .= " AND a.employee_id = ?";
    $params[] = $employee_filter;
    $types .= "s";
}

$sql .= " ORDER BY a.attendance_date DESC, a.in_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid mt-4 mb-5">

    <div class="card shadow-lg">

        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Employee Attendance List</h3>
        </div>

        <div class="card-body">

            <?php echo $message; ?>

            <!-- FILTER FORM -->
            <form method="GET" class="row g-3 mb-4">

                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" 
                           name="from_date" 
                           class="form-control"
                           value="<?php echo $from_date; ?>">
                </div>

                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" 
                           name="to_date" 
                           class="form-control"
                           value="<?php echo $to_date; ?>">
                </div>

                <div class="col-md-3">
                    <label>Employee ID</label>
                    <input type="text" 
                           name="employee_id" 
                           class="form-control"
                           placeholder="Optional"
                           value="<?php echo htmlspecialchars($employee_filter); ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-success w-100">
                        Filter
                    </button>

                    <a href="attendance.php" class="btn btn-secondary w-100">
                        Reset
                    </a>
                </div>

            </form>

            <!-- TABLE -->
            <div class="table-responsive">

                <table class="table table-bordered table-striped align-middle text-center">

                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>IN Image</th>
                            <th>OUT Image</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Date</th>
                            <th>IN Time</th>
                            <th>OUT Time</th>
                            <th>IN Location</th>
                            <th>OUT Location</th>
                            <th>Status</th>

                            <?php if ($employee_role === 'admin') { ?>
                                <th>Admin Action</th>
                            <?php } ?>
                        </tr>
                    </thead>

                    <tbody>

                    <?php
                    $sl = 1;

                    if ($result->num_rows > 0) {

                        while ($row = $result->fetch_assoc()) {

                            $status = 'Absent';

                            if (!empty($row['in_time']) && empty($row['out_time'])) {
                                $status = 'IN Only';
                            }

                            if (!empty($row['in_time']) && !empty($row['out_time'])) {
                                $status = 'Completed';
                            }
                    ?>

                        <tr>

                            <form method="POST">

                                <td><?php echo $sl++; ?></td>

                                <td><?php echo htmlspecialchars($row['employee_id']); ?></td>

                               <td>
                                    <a href="<?php echo BASE_URL . 'attendance/' . (!empty($row['in_image']) ? $row['in_image'] : 'default-image.jpg'); ?>" target="_blank">
                                        <img 
                                            src="<?php echo BASE_URL . 'attendance/' . (!empty($row['in_image']) ? $row['in_image'] : 'default-image.jpg'); ?>" 
                                            alt="Employee IN Image"
                                            class="img-thumbnail"
                                            style="width:50px;height:50px;object-fit:cover;"
                                        >
                                    </a>
                                </td>

                                <td>
                                    <a href="<?php echo BASE_URL . 'attendance/' . (!empty($row['out_image']) ? $row['out_image'] : 'default-image.jpg'); ?>" target="_blank">
                                        <img 
                                            src="<?php echo BASE_URL . 'attendance/' . (!empty($row['out_image']) ? $row['out_image'] : 'default-image.jpg'); ?>" 
                                            alt="Employee OUT Image"
                                            class="img-thumbnail"
                                            style="width:50px;height:50px;object-fit:cover;"
                                        >
                                    </a>
                                </td>

                                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>

                                <td><?php echo htmlspecialchars($row['employee_role']); ?></td>

                                <td>
                                    <?php echo date('d-m-Y', strtotime($row['attendance_date'])); ?>
                                </td>

                                <!-- IN TIME -->
                                <td>
                                    <?php if ($employee_role === 'admin') { ?>
                                        <input type="time"
                                               name="in_time"
                                               value="<?php echo !empty($row['in_time']) ? date('H:i:s', strtotime($row['in_time'])) : ''; ?>"
                                               class="form-control">
                                    <?php } else { ?>
                                        <?php echo !empty($row['in_time']) ? date('h:i:s A', strtotime($row['in_time'])) : '-'; ?>
                                    <?php } ?>
                                </td>

                                <!-- OUT TIME -->
                                <td>
                                    <?php if ($employee_role === 'admin') { ?>
                                        <input type="time"
                                               name="out_time"
                                               value="<?php echo !empty($row['out_time']) ? date('H:i:s', strtotime($row['out_time'])) : ''; ?>"
                                               class="form-control">
                                    <?php } else { ?>
                                        <?php echo !empty($row['out_time']) ? date('h:i:s A', strtotime($row['out_time'])) : '-'; ?>
                                    <?php } ?>
                                </td>

                                <td>
                                    <?php if (!empty($row['in_latitude']) && !empty($row['in_longitude'])) { ?>

                                        <a 
                                            href="https://www.google.com/maps?q=<?php echo $row['in_latitude']; ?>,<?php echo $row['in_longitude']; ?>" 
                                            target="_blank"
                                            class="text-decoration-none"
                                        >
                                            <?php echo $row['in_latitude']; ?>
                                            <br>
                                            <?php echo $row['in_longitude']; ?>
                                        </a>

                                    <?php } else { ?>

                                        -

                                    <?php } ?>
                                </td>

                                <td>
                                    <?php if (!empty($row['out_latitude']) && !empty($row['out_longitude'])) { ?>

                                        <a 
                                            href="https://www.google.com/maps?q=<?php echo $row['out_latitude']; ?>,<?php echo $row['out_longitude']; ?>" 
                                            target="_blank"
                                            class="text-decoration-none"
                                        >
                                            <?php echo $row['out_latitude']; ?>
                                            <br>
                                            <?php echo $row['out_longitude']; ?>
                                        </a>

                                    <?php } else { ?>

                                        -

                                    <?php } ?>
                                </td>

                                <td>
                                    <?php
                                    if ($status == 'Completed') {
                                        echo "<span class='badge bg-success'>$status</span>";
                                    } elseif ($status == 'IN Only') {
                                        echo "<span class='badge bg-warning text-dark'>$status</span>";
                                    } else {
                                        echo "<span class='badge bg-danger'>$status</span>";
                                    }
                                    ?>
                                </td>

                                <?php if ($employee_role === 'admin') { ?>
                                    <td>
                                        <input type="hidden"
                                               name="attendance_id"
                                               value="<?php echo $row['id']; ?>">

                                        <button type="submit"
                                                class="btn btn-primary btn-sm">
                                            Update
                                        </button>
                                    </td>
                                <?php } ?>

                            </form>

                        </tr>

                    <?php
                        }

                    } else {
                    ?>

                        <tr>
                            <td colspan="11">
                                No attendance records found.
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