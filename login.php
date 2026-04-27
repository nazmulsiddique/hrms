<?php
session_start();
require_once(__DIR__ . '/config/config.php');
require_once(BASE_PATH . '/config/db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT * FROM employees 
        WHERE employee_id=? 
        AND employee_status='active'
    ");

    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['employee_password'])) {

            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['employee_name'] = $user['employee_name'];
            $_SESSION['employee_role'] = $user['employee_role'];

            // header("Location: " . BASE_URL . "dashboard.php");
             header("Location: " . BASE_URL);
            exit;

        } else {
            $error = "Wrong Password!";
        }

    } else {
        $error = "Invalid Employee ID!";
    }
}
/* Hide sidebar/navbar on login page */
$hideLayout = true;
include(BASE_PATH . '/includes/header.php');
?>


<div class="container mt-5 mb-5">

    <div class="row justify-content-center">

        <div class="col-md-4">

            <div class="card shadow">

                <div class="card-header bg-primary text-white text-center">
                    <h4>Employee Login</h4>
                </div>

                <div class="card-body">

                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php } ?>

                    <form method="POST">

                        <!-- EMPLOYEE ID -->
                        <div class="mb-3">
                            <label>Employee ID</label>
                            <input type="text"
                                   name="employee_id"
                                   class="form-control"
                                   required>
                        </div>

                        <!-- PASSWORD -->
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>

                        <!-- LOGIN BUTTON -->
                        <button type="submit" class="btn btn-success w-100">
                            Login
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>