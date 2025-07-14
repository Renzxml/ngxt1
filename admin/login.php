<?php
require_once '../includes/db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$messageType = '';

// Load cookie if set
$email = isset($_COOKIE['remember_email']) ? $_COOKIE['remember_email'] : '';
$rememberChecked = isset($_COOKIE['remember_email']);

$db = new db_connect();
if (!$db->connect()) {
    die("Database connection failed: " . $db->error);
}
$conn = $db->conn;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // === ADMIN REGISTRATION ===
    if (isset($_POST['register'])) {
        $fname      = trim($_POST['fName']);
        $lname      = trim($_POST['lName']);
        $email      = trim($_POST['email']);
        $password   = $_POST['password'];
        $cPassword  = $_POST['cPpassword'];

        // 1. duplicate check
        $stmt = $conn->prepare("SELECT 1 FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $messageType = "error";
            $message     = "Email is already registered.";
        } elseif ($password !== $cPassword) {
            $messageType = "error";
            $message     = "Passwords do not match.";
        } else {
            // 2. insert
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO users (email, fname, lname, password, role_status)
                VALUES (?, ?, ?, ?, 'pending')"               // <-- pick 'pending' or 'active'
            );
            $stmt->bind_param("ssss", $email, $fname, $lname, $hashed);

            if ($stmt->execute()) {
                $newId = $stmt->insert_id;

                // 3. build user array for mail
                $userRow = [
                    'id'          => $newId,
                    'fname'       => $fname,
                    'lname'       => $lname,
                    'email'       => $email,          // <- use $email
                    'role_status' => 'pending'        // <- match the INSERT above
                ];

                // 4. send alert
                require_once '../includes/mail_helpers.php';
                notifyReviewers($userRow);

                $messageType = "success";
                $message     = "Account created. Awaiting approval.";
            } else {
                $messageType = "error";
                $message     = "Failed to register admin.";
            }
        }
    }


    // === ADMIN LOGIN ===
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if (!$admin) {
            $messageType = "error";
            $message = "Email is not registered.";
        } elseif ($admin['role_status'] !== 'partners') {
            $messageType = "error";
            $message = "Account is inactive. Contact administrator.";
        } elseif (!password_verify($password, $admin['password'])) {
            $messageType = "error";
            $message = "Incorrect password.";
        } else {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['fname'] . ' ' . $admin['lname'];
            $_SESSION['role_status'] = $admin['role_status'];

            // === REMEMBER ME COOKIE ===
            if ($remember) {
                setcookie("remember_email", $email, time() + (86400 * 30), "/"); // 30 days
            } else {
                setcookie("remember_email", "", time() - 3600, "/");
            }

            header("Location: index.php");
            exit;
        }
    }
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://kit.fontawesome.com/77ff7e1fdc.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../assets/login-style.css">
    <link rel="icon" type="image/png" href="../assets/resources/logo8.jpg">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container" id="container">
        <div class="form-container sign-up">
            <form action="" method="POST">
                <h1><img src="../assets/resources/logo1.png" height="45"></h1>

                <div class="input-group">
                    <div class="input">
                        <input id="fName" name="fName" placeholder="First Name" type="text" required class="validate" autocomplete="off">
                        <div class="input-addon">
                            <i class="fa-regular fa-user fa-sm"></i>
                        </div>
                    </div>

                    <div class="input">
                        <input id="lName" name="lName" placeholder="Last Name" type="text" required class="validate" autocomplete="off">
                        <div class="input-addon">
                            <i class="fa-regular fa-user fa-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="input">
                    <div class="input-addon">
                        <i class="fa-regular fa-envelope fa-sm"></i>
                    </div>
                    <input id="email" name="email" placeholder="Admin Email" type="email" required class="validate" autocomplete="off">
                </div>

                <div class="input">
                    <div class="input-addon">
                        <i class="fa-solid fa-key fa-sm"></i>
                    </div>
                    <input id="password" name="password" placeholder="Password" type="password" required class="validate" autocomplete="off">
                </div>

                <div class="input">
                    <div class="input-addon">
                        <i class="fa-solid fa-key fa-sm"></i>
                    </div>
                    <input id="cPassword" name="cPpassword" placeholder="Confirm Password" type="password" required class="validate" autocomplete="off">
                </div>

                <div class="terms">
                    <input type="checkbox" required>
                    <span>I accept Terms of Service</span>
                </div>

                <input type="submit" name="register" value="CREATE ADMACCOUNT" class="admin-btn" />

                <div class="privacy">
                    <a href="#">Privacy Policy</a>
                </div>
            </form>
        </div>

        <div class="form-container sign-in">
            <form action="" method="POST">
                <h1><img src="../assets/resources/logo1.png" height="45"></h1>
  
                <div class="input">
                    <div class="input-addon">
                        <i class="fa-regular fa-envelope fa-sm"></i>
                    </div>
                    <input id="email" name="email" placeholder="Admin Email" type="email" required class="validate" autocomplete="off">
                </div>

                <div class="input">
                    <div class="input-addon">
                        <i class="fa-solid fa-key fa-sm"></i>
                    </div>
                    <input id="password" name="password" placeholder="Password" type="password" required class="validate" autocomplete="off">
                </div>

                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" <?= $rememberChecked ? 'checked' : '' ?> />
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot_password">Forgot your password?</a>
                </div>

                <input type="submit" name="login" value="ADMIN LOGIN" class="admin-btn" />

                <div class="privacy">
                    <a href="#">Privacy Policy</a>
                </div>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1 class="header-left">Welcome Back!</h1>
                    <p>Access the admin dashboard with your credentials</p>
                    <button class="hidden" id="login">ADMIN LOGIN</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1 class="header-right">New Admin?</h1>
                    <p>Register a new admin account to access the system</p>
                    <button class="hidden" id="register">CREATE ACCOUNT</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $messageType; ?>',
                title: '<?php echo $messageType === "success" ? "Success" : "Error"; ?>',
                text: '<?php echo $message; ?>'
            });
        </script>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const container = document.getElementById("container");
            const registerBtn = document.getElementById("register");
            const loginBtn = document.getElementById("login");

            if (registerBtn) {
                registerBtn.addEventListener("click", () => {
                    container.classList.add("active");
                });
            }

            if (loginBtn) {
                loginBtn.addEventListener("click", () => {
                    container.classList.remove("active");
                });
            }
        });
    </script>
</body>
</html>