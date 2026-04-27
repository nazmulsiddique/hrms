<?php
date_default_timezone_set('Asia/Dhaka');

require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');
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
                            <th>Name</th>
                            <th>Role</th>
                            <th>Date</th>
                            <th>IN Time</th>
                            <th>IN Location</th>
                            <th>IN Image</th>
                            <th>OUT Time</th>
                            <th>OUT Location</th>
                            <th>OUT Image</th>
                            <th>Status</th>
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
                            <td><?php echo $sl++; ?></td>

                            <td><?php echo htmlspecialchars($row['employee_id']); ?></td>

                            <td><?php echo htmlspecialchars($row['employee_name']); ?></td>

                            <td><?php echo htmlspecialchars($row['employee_role']); ?></td>

                            <td>
                                <?php echo date('d-m-Y', strtotime($row['attendance_date'])); ?>
                            </td>

                            <td>
                                <?php
                                echo !empty($row['in_time'])
                                    ? date('h:i:s A', strtotime($row['in_time']))
                                    : '-';
                                ?>
                            </td>

                            <td>
                                <?php
                                if (!empty($row['in_latitude'])) {
                                    echo $row['in_latitude'] . "<br>" . $row['in_longitude'];
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                            <td>
                                <?php if (!empty($row['in_image'])) { ?>
                                    <a href="<?php echo $row['in_image']; ?>" target="_blank">
                                        <img src="<?php echo $row['in_image']; ?>"
                                             width="60"
                                             height="60"
                                             class="rounded border">
                                    </a>
                                <?php } else { echo '-'; } ?>
                            </td>

                            <td>
                                <?php
                                echo !empty($row['out_time'])
                                    ? date('h:i:s A', strtotime($row['out_time']))
                                    : '-';
                                ?>
                            </td>

                            <td>
                                <?php
                                if (!empty($row['out_latitude'])) {
                                    echo $row['out_latitude'] . "<br>" . $row['out_longitude'];
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                            <td>
                                <?php if (!empty($row['out_image'])) { ?>
                                    <a href="<?php echo $row['out_image']; ?>" target="_blank">
                                        <img src="<?php echo $row['out_image']; ?>"
                                             width="60"
                                             height="60"
                                             class="rounded border">
                                    </a>
                                <?php } else { echo '-'; } ?>
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

                        </tr>

                    <?php
                        }

                    } else {
                    ?>

                        <tr>
                            <td colspan="12">
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