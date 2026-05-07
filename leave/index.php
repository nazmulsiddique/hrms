<?php
session_start();
date_default_timezone_set('Asia/Dhaka');

require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');

if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];
$role = $_SESSION['employee_role'] ?? 'user';

$message = "";

/*
========================================
INSERT / UPDATE
========================================
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $_POST['id'] ?? null;

    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $total_days = $_POST['total_days'];
    $address    = $_POST['address_during_leave'];
    $reason     = $_POST['reason'];
    $status     = $_POST['leave_status'] ?? 'pending';

    /*
    ========================================
    UPDATE
    ========================================
    */
    if (!empty($id)) {

        $stmt = $conn->prepare("
            UPDATE leaves
            SET 
                leave_type=?,
                start_date=?,
                end_date=?,
                total_days=?,
                address_during_leave=?,
                reason=?,
                leave_status=?
            WHERE id=? AND employee_id=?
        ");

        $stmt->bind_param(
            "sssssssis",
            $leave_type,
            $start_date,
            $end_date,
            $total_days,
            $address,
            $reason,
            $status,
            $id,
            $employee_id
        );

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Leave Updated Successfully</div>";
        }

    }

    /*
    ========================================
    INSERT
    ========================================
    */
    else {

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
                leave_status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
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
            $message = "<div class='alert alert-success'>Leave Applied (Pending)</div>";
        }
    }
}

/*
========================================
EDIT DATA
========================================
*/
$editData = null;

if (isset($_GET['edit'])) {

    $eid = $_GET['edit'];

    $q = $conn->prepare("
        SELECT * FROM leaves 
        WHERE id=? AND employee_id=?
    ");

    $q->bind_param("is", $eid, $employee_id);
    $q->execute();
    $editData = $q->get_result()->fetch_assoc();
}

/*
========================================
LIST
========================================
*/
$list = $conn->prepare("
    SELECT l.*, e.employee_name
    FROM leaves l
    LEFT JOIN employees e 
    ON l.employee_id = e.employee_id
    WHERE l.employee_id=?
    ORDER BY l.id DESC
");

$list->bind_param("s", $employee_id);
$list->execute();
$result = $list->get_result();

include(BASE_PATH . '/includes/header.php');
?>

<div class="container mt-4 mb-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4>Leave Application</h4>
        </div>

        <div class="card-body">

            <?php echo $message; ?>

            <!-- FORM -->
            <form method="POST">

                <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">

                <!-- EMPLOYEE INFO -->
                <div class="mb-2">
                    <label>Employee ID</label>
                    <input type="text" class="form-control"
                           value="<?php echo $employee_id; ?>" readonly>
                </div>

                <div class="mb-2">
                    <label>Employee Name</label>
                    <input type="text" class="form-control"
                           value="<?php echo $employee_name; ?>" readonly>
                </div>

                <!-- LEAVE TYPE -->
                <select name="leave_type" class="form-control mb-2" required>
                    <?php
                    $types = ['CL','ML','AL','BL','OTHERS'];
                    foreach ($types as $t) {
                        $sel = ($editData['leave_type'] ?? '') == $t ? 'selected' : '';
                        echo "<option $sel value='$t'>$t</option>";
                    }
                    ?>
                </select>

                <!-- DATES -->
                <input type="date" id="start_date" name="start_date"
                       value="<?php echo $editData['start_date'] ?? ''; ?>"
                       class="form-control mb-2">

                <input type="date" id="end_date" name="end_date"
                       value="<?php echo $editData['end_date'] ?? ''; ?>"
                       class="form-control mb-2">

                <!-- TOTAL DAYS -->
                <input type="text" id="total_days" name="total_days"
                       value="<?php echo $editData['total_days'] ?? ''; ?>"
                       class="form-control mb-2" readonly>

                <!-- ADDRESS -->
                <textarea name="address_during_leave" class="form-control mb-2">
                    <?php echo $editData['address_during_leave'] ?? ''; ?>
                </textarea>

                <!-- REASON -->
                <textarea name="reason" class="form-control mb-2">
                    <?php echo $editData['reason'] ?? ''; ?>
                </textarea>

                <!-- STATUS (ADMIN ONLY) -->
                <?php if ($role == 'admin') { ?>
                    <select name="leave_status" class="form-control mb-2">
                        <?php
                        $sts = ['pending','approved','rejected'];
                        foreach ($sts as $s) {
                            $sel = ($editData['leave_status'] ?? 'pending') == $s ? 'selected' : '';
                            echo "<option $sel value='$s'>$s</option>";
                        }
                        ?>
                    </select>
                <?php } ?>

                <button class="btn btn-success w-100">
                    <?php echo $editData ? "Update Leave" : "Submit Leave"; ?>
                </button>

            </form>

        </div>
    </div>

    <br>

    <!-- LIST -->
    <div class="card shadow">
        <div class="card-body">

            <table class="table table-bordered text-center">

                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()) { ?>

                <tr>

                    <td><?php echo $row['employee_id']; ?></td>
                    <td><?php echo $row['employee_name']; ?></td>
                    <td><?php echo $row['leave_type']; ?></td>
                    <td><?php echo $row['start_date'].' - '.$row['end_date']; ?></td>
                    <td><?php echo $row['total_days']; ?></td>

                    <td>
                        <?php
                        if ($row['leave_status'] == 'pending') {
                            echo "<span class='badge bg-warning'>Pending</span>";
                        } elseif ($row['leave_status'] == 'approved') {
                            echo "<span class='badge bg-success'>Approved</span>";
                        } else {
                            echo "<span class='badge bg-danger'>Rejected</span>";
                        }
                        ?>
                    </td>

                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                            Edit
                        </a>
                    </td>

                </tr>

                <?php } ?>

            </table>

        </div>
    </div>

</div>

<!-- LIVE DAYS -->
<script>
function calcDays() {
    let s = document.getElementById('start_date').value;
    let e = document.getElementById('end_date').value;

    if (s && e) {
        let d1 = new Date(s);
        let d2 = new Date(e);

        if (d2 >= d1) {
            let diff = (d2 - d1) / (1000*60*60*24) + 1;
            document.getElementById('total_days').value = diff;
        }
    }
}

document.getElementById('start_date').addEventListener('change', calcDays);
document.getElementById('end_date').addEventListener('change', calcDays);
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>