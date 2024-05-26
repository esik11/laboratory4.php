<?php
session_start(); // Start the session to manage user data

include "includes/db-conn.php"; // Include the database connection file

use PHPMailer\PHPMailer\PHPMailer; // Import PHPMailer class
use PHPMailer\PHPMailer\SMTP; // Import PHPMailer SMTP class
use PHPMailer\PHPMailer\Exception; // Import PHPMailer Exception class

require 'vendor/autoload.php'; // Require autoload file to load PHPMailer library

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Function to validate and sanitize input data
    function validate($data)
    {
        $data = trim($data); // Remove whitespace from the beginning and end of the string
        $data = stripslashes($data); // Remove backslashes (\)
        $data = htmlspecialchars($data); // Convert special characters to HTML entities
        return $data; // Return the sanitized data
    }

    // Validate and sanitize input fields
    $username = validate($_POST['username']);
    $password = validate($_POST['password']);
    $confirm_password = validate($_POST['confirm_password']);
    $emailaddress = validate($_POST['emailaddress']);
    $firstname = validate($_POST['firstname']);
    $middlename = validate($_POST['middlename']);
    $lastname = validate($_POST['lastname']);
    $gmail_password = validate($_POST['gmail_password']);

    // Handle file upload
    $profile_pic = '';
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['profile_pic']['tmp_name'];
        $file_name = $_FILES['profile_pic']['name'];
        $file_size = $_FILES['profile_pic']['size'];
        $file_type = $_FILES['profile_pic']['type'];

        // Validate file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 1048576; // 1MB

        if (!in_array($file_type, $allowed_types) || $file_size > $max_size) {
            $_SESSION['status'] = "Invalid file type or size. Please upload a JPEG, PNG, or GIF image with a maximum size of 1MB.";
            header("Location: register.php");
            exit();
        }

        // Move the uploaded file to a permanent location
        $upload_dir = 'uploads/';
        $upload_file = $upload_dir . basename($file_name);
        if (!move_uploaded_file($file_tmp_name, $upload_file)) {
            $_SESSION['status'] = "Error uploading file.";
            header("Location: register.php");
            exit();
        }

        // Store the file path in the database
        $profile_pic = $upload_file;
    }
    // Checking if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['status'] = "Passwords do not match."; // Set session status message
        $_SESSION['username'] = $username;
        $_SESSION['emailaddress'] = $emailaddress;
        $_SESSION['firstname'] = $firstname;
        $_SESSION['middlename'] = $middlename;
        $_SESSION['lastname'] = $lastname;
        header("Location: signup.php"); // Redirect back to signup page
        exit(); // Terminate script execution
    }

    // check if any field is empty
    if (empty($username) || empty($password) || empty($confirm_password) || empty($emailaddress) || empty($firstname) || empty($middlename) || empty($lastname) || empty($gmail_password)) {
        $_SESSION['status'] = "All fields are required."; // Set session status message
        $_SESSION['username'] = $username;
        $_SESSION['emailaddress'] = $emailaddress;
        $_SESSION['firstname'] = $firstname;
        $_SESSION['middlename'] = $middlename;
        $_SESSION['lastname'] = $lastname;
        header("Location: register.php"); //redirect back to register page
        exit(); //terminate script execution
    } elseif ($password !== $confirm_password) { //check again for passwords match
        $_SESSION['status'] = "Passwords do not match."; //set session status message
        $_SESSION['username'] = $username;
        $_SESSION['emailaddress'] = $emailaddress;
        $_SESSION['firstname'] = $firstname;
        $_SESSION['middlename'] = $middlename;
        $_SESSION['lastname'] = $lastname;
        header("Location: register.php"); //redirect back to register page
        exit(); //terminate script execution
    } elseif ($firstname === $middlename || $middlename === $lastname || $firstname === $lastname) {
        $_SESSION['status'] = "First name, middle name, and last name cannot be the same."; // Set session status message
        $_SESSION['username'] = $username;
        $_SESSION['emailaddress'] = $emailaddress;
        $_SESSION['firstname'] = $firstname;
        $_SESSION['middlename'] = $middlename;
        $_SESSION['lastname'] = $lastname;
        header("Location: register.php"); // Redirect back to register page
        exit(); // Terminate script execution
    } else {
        // database operations
        $verify_token = md5(rand()); // Generate a verification token

        // Storing email address or username in the 'email' field of the database
        $email_to_store = $emailaddress;

        // check if the email address already exists in the database
        $check_email_query = "SELECT email FROM users WHERE LOWER(email) = LOWER('$email_to_store') LIMIT 1";
        $check_email_query_run = mysqli_query($conn, $check_email_query);

        if (mysqli_num_rows($check_email_query_run) > 0) {
            $_SESSION['status'] = "Email ID already exists. Please use another email address."; // Set session status message
            header("Location: register.php"); // Redirect back to register page
            exit(); // Terminate script execution
        }

        // Insert user data into the database
        $sql = "INSERT INTO users (username, password, profile_pic, first_name, middle_name, last_name, email, verify_token) 
                VALUES ('$username', '$password', '$profile_pic', '$firstname', '$middlename', '$lastname', '$email_to_store', '$verify_token')";

        if (mysqli_query($conn, $sql)) {
            // Registration successful message
            $_SESSION['status'] = "Registration successful. Please verify your email."; // Set session status message
            
            // Perform email sending here
            // Configure PHPMailer instance and send verification email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $emailaddress; // Your Gmail address
                $mail->Password = $gmail_password; // Your Gmail password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipient
                $mail->setFrom($emailaddress, 'Your Name');
                $mail->addAddress($emailaddress); // Add recipient

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Email Verification';
                $mail->Body    = "Click the following link to verify your email address:  <a href='http://localhost/laboratory4.php/php-admin/labact.php/admin/verify-email.php?token=$verify_token'>Verify Email</a>";
                $mail->AltBody = 'Please verify your email address.';

                $mail->send();
            } catch (Exception $e) {
                $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"; // Set session status message
                header("Location: register.php"); // Redirect back to register page
                exit(); // Terminate script execution
            }
            header("Location: register.php"); // Redirect back to register page
            exit(); // Terminate script execution
        } else {
            $_SESSION['status'] = "Error occurred while registering user."; // Set session status message
            header("Location: register.php"); // Redirect back to register page
            exit(); // Terminate script execution
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>

<body class="hold-transition register-page">
    <div class="register-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="../../index2.html" class="h1"><b>Admin</b>LTE</a>
            </div>
            <div class="card-body">
                <h2>Register</h2>
                <?php if (isset($_SESSION['status'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $_SESSION['status']; ?>
                    </div>
                    <?php unset($_SESSION['status']); ?>
                <?php } ?>
                <form action="register.php" method="post" enctype="multipart/form-data">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First Name" value="<?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] : ''; ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="middlename" name="middlename" placeholder="Middle Name" value="<?php echo isset($_SESSION['middlename']) ? $_SESSION['middlename'] : ''; ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last Name" value="<?php echo isset($_SESSION['lastname']) ? $_SESSION['lastname'] : ''; ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="User Name" value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>

                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>

                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>

                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" id="emailaddress" name="emailaddress" placeholder="Email Address" value="<?php echo isset($_SESSION['emailaddress']) ? $_SESSION['emailaddress'] : ''; ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="gmail_password" name="gmail_password" placeholder="Email Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>

                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="profile_pic" name="profile_pic">
                            <label class="custom-file-label" for="profile_pic">Choose profile picture</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="agreeTerms" name="terms" value="agree">
                                <label for="agreeTerms">
                                    I agree to the <a href="#">terms</a>
                                </label>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>

                <div class="social-auth-links text-center">
                    <a href="#" class="btn btn-block btn-primary">
                        <i class="fab fa-facebook mr-2"></i>
                        Sign up using Facebook
                    </a>
                    <a href="#" class="btn btn-block btn-danger">
                        <i class="fab fa-google-plus mr-2"></i>
                        Sign up using Google+
                    </a>
                </div>

                <a href="login.php" class="text-center">I already have a membership</a>
            </div>
            <!-- /.form-box -->
        </div><!-- /.card -->
    </div>
    <!-- /.register-box -->

    <!-- jQuery -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="assets/dist/js/adminlte.min.js"></script>
</body>

</html>