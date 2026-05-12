<?php
session_start();

require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');

include(BASE_PATH . '/includes/header.php');

$message = "";

/*
=================================================
INSERT / UPDATE
=================================================
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $_POST['id'] ?? '';

    $employee_id = $_POST['employee_id'];
    $year        = $_POST['year'];

    $cl      = $_POST['cl_balance'];
    $ml      = $_POST['ml_balance'];
    $bl      = $_POST['bl_balance'];

    $wpl     = $_POST['with_pay_balance'];
    $wopl    = $_POST['without_pay_balance'];

    $others  = $_POST['others_balance'];

    $status  = $_POST['status'];

    /*
    ============================================
    UPDATE
    ============================================
    */
    if (!empty($id)) {

        $stmt = $conn->prepare("
            UPDATE leave_balance
            SET
                employee_id=?,
                year=?,
                cl_balance=?,
                ml_balance=?,
                bl_balance=?,
                with_pay_balance=?,
                without_pay_balance=?,
                others_balance=?,
                status=?,
                updated_at=NOW()
            WHERE id=?
        ");

        if (!$stmt) {
            die($conn->error);
        }

        $stmt->bind_param(
            "siiiiiiisi",
            $employee_id,
            $year,
            $cl,
            $ml,
            $bl,
            $wpl,
            $wopl,
            $others,
            $status,
            $id
        );

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>
                            Leave Balance Updated Successfully
                        </div>";
        }
    }

    /*
    ============================================
    INSERT
    ============================================
    */
    else {

        $stmt = $conn->prepare("
            INSERT INTO leave_balance
            (
                employee_id,
                year,
                cl_balance,
                ml_balance,
                bl_balance,
                with_pay_balance,
                without_pay_balance,
                others_balance,
                status,
                created_at
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");

        if (!$stmt) {
            die($conn->error);
        }

        $stmt->bind_param(
            "siiiiiiis",
            $employee_id,
            $year,
            $cl,
            $ml,
            $bl,
            $wpl,
            $wopl,
            $others,
            $status
        );

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>
                            Leave Balance Assigned Successfully
                        </div>";
        }
    }
}

/*
=================================================
EDIT DATA
=================================================
*/
$editData = null;

if (isset($_GET['edit'])) {

    $edit_id = $_GET['edit'];

    $edit = $conn->prepare("
        SELECT *
        FROM leave_balance
        WHERE id=?
    ");

    $edit->bind_param("i", $edit_id);
    $edit->execute();

    $editData = $edit->get_result()->fetch_assoc();
}

/*
=================================================
EMPLOYEE LIST
=================================================
*/
$employees = $conn->query("
    SELECT employee_id, employee_name
    FROM employees
    ORDER BY employee_name ASC
");

/*
=================================================
BALANCE LIST
=================================================
*/
$list = $conn->query("
    SELECT 
        elb.*,
        e.employee_name
    FROM leave_balance elb
    LEFT JOIN employees e
    ON elb.employee_id = e.employee_id
    ORDER BY elb.id DESC
");
?>

<div class="container mt-4 mb-5">

    <!-- FORM -->
    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4>
                <?php
                echo !empty($editData)
                    ? 'Update Leave Balance'
                    : 'Assign Leave Balance';
                ?>
            </h4>
        </div>

        <div class="card-body">

            <?php echo $message; ?>

            <form method="POST">

                <input type="hidden"
                       name="id"
                       value="<?php echo $editData['id'] ?? ''; ?>">

                <div class="row">

                    <!-- EMPLOYEE -->
                    <div class="col-md-6 mb-3">

                        <label>Employee</label>

                        <select name="employee_id"
                                class="form-control"
                                required>

                            <option value="">
                                Select Employee
                            </option>

                            <?php while($emp = $employees->fetch_assoc()) { ?>

                                <option
                                    value="<?php echo $emp['employee_id']; ?>"

                                    <?php
                                    if (($editData['employee_id'] ?? '') == $emp['employee_id']) {
                                        echo 'selected';
                                    }
                                    ?>
                                >
                                    <?php echo $emp['employee_id']; ?>
                                    -
                                    <?php echo $emp['employee_name']; ?>
                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <!-- YEAR -->
                    <div class="col-md-6 mb-3">

                        <label>Year</label>

                        <input type="number"
                               name="year"
                               class="form-control"
                               required
                               value="<?php echo $editData['year'] ?? date('Y'); ?>">

                    </div>

                    <!-- CL -->
                    <div class="col-md-4 mb-3">

                        <label>CL Balance</label>

                        <input type="number"
                               name="cl_balance"
                               class="form-control"
                               value="<?php echo $editData['cl_balance'] ?? 0; ?>">

                    </div>

                    <!-- ML -->
                    <div class="col-md-4 mb-3">

                        <label>ML Balance</label>

                        <input type="number"
                               name="ml_balance"
                               class="form-control"
                               value="<?php echo $editData['ml_balance'] ?? 0; ?>">

                    </div>

                    <!-- BL -->
                    <div class="col-md-4 mb-3">

                        <label>BL Balance</label>

                        <input type="number"
                               name="bl_balance"
                               class="form-control"
                               value="<?php echo $editData['bl_balance'] ?? 0; ?>">

                    </div>

                    <!-- WITH PAY -->
                    <div class="col-md-4 mb-3">

                        <label>With Pay Leave</label>

                        <input type="number"
                               name="with_pay_balance"
                               class="form-control"
                               value="<?php echo $editData['with_pay_balance'] ?? 0; ?>">

                    </div>

                    <!-- WITHOUT PAY -->
                    <div class="col-md-4 mb-3">

                        <label>Without Pay Leave</label>

                        <input type="number"
                               name="without_pay_balance"
                               class="form-control"
                               value="<?php echo $editData['without_pay_balance'] ?? 0; ?>">

                    </div>

                    <!-- OTHERS -->
                    <div class="col-md-4 mb-3">

                        <label>Others Balance</label>

                        <input type="number"
                               name="others_balance"
                               class="form-control"
                               value="<?php echo $editData['others_balance'] ?? 0; ?>">

                    </div>

                    <!-- STATUS -->
                    <div class="col-md-6 mb-3">

                        <label>Status</label>

                        <select name="status"
                                class="form-control">

                            <option value="active"
                                <?php
                                if (($editData['status'] ?? '') == 'active') {
                                    echo 'selected';
                                }
                                ?>>
                                Active
                            </option>

                            <option value="inactive"
                                <?php
                                if (($editData['status'] ?? '') == 'inactive') {
                                    echo 'selected';
                                }
                                ?>>
                                Inactive
                            </option>

                        </select>

                    </div>

                    <!-- BUTTON -->
                    <div class="col-md-12">

                        <button type="submit"
                                class="btn btn-success">

                            <?php
                            echo !empty($editData)
                                ? 'Update Leave Balance'
                                : 'Save Leave Balance';
                            ?>

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- LIST -->
    <div class="card shadow mt-4">

        <div class="card-header bg-dark text-white">
            <h4>Leave Balance List</h4>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>
                            <th>Employee</th>
                            <th>Year</th>

                            <th>CL</th>
                            <th>ML</th>
                            <th>BL</th>

                            <th>With Pay</th>
                            <th>Without Pay</th>

                            <th>Others</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while($row = $list->fetch_assoc()) { ?>

                        <tr>

                            <td><?php echo $row['id']; ?></td>

                            <td>
                                <?php echo $row['employee_id']; ?>
                                <br>
                                <?php echo $row['employee_name']; ?>
                            </td>

                            <td><?php echo $row['year']; ?></td>

                            <td><?php echo $row['cl_balance']; ?></td>

                            <td><?php echo $row['ml_balance']; ?></td>

                            <td><?php echo $row['bl_balance']; ?></td>

                            <td><?php echo $row['with_pay_balance']; ?></td>

                            <td><?php echo $row['without_pay_balance']; ?></td>

                            <td><?php echo $row['others_balance']; ?></td>

                            <td>

                                <?php
                                if ($row['status'] == 'active') {
                                    echo "<span class='badge bg-success'>Active</span>";
                                } else {
                                    echo "<span class='badge bg-danger'>Inactive</span>";
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

<?php include(BASE_PATH . '/includes/footer.php'); ?>