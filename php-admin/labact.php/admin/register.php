<?php
session_start();
include('includes/db-conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Function to validate and sanitize input
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Validate and sanitize form inputs
    $full_name = validate($_POST['full_name']);
    $email = validate($_POST['email']);
    $password = validate($_POST['password']);
    $address = validate($_POST['address']);
    $phone_number = validate($_POST['phone_number']);
    $firstname = validate($_POST['firstname']);
    $middlename = validate($_POST['middlename']);
    $lastname = validate($_POST['lastname']);

    // File upload handling
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $_SESSION['error'] = "File is not an image.";
            $uploadOk = 0;
        }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        $_SESSION['error'] = "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["profile_pic"]["size"] > 500000) {
        $_SESSION['error'] = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $_SESSION['error'] = "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $_SESSION['success'] = "The file ". basename( $_FILES["profile_pic"]["name"]). " has been uploaded.";
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
        }
    }

    // Check if email already exists
    $email_check_query = "SELECT * FROM user_profile WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $email_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) { // If email exists
        if ($user['email'] === $email) {
            $_SESSION['error'] = "Email already exists";
        }
    }

    // Insert user data into the database if no errors
    if (!isset($_SESSION['error'])) {
        $sql = "INSERT INTO user_profile (full_name, email, password, address, phone_number, profile_pic, firstname, middlename, lastname)
            VALUES ('$full_name', '$email', '$password', '$address', '$phone_number', '$target_file', '$firstname', '$middlename', '$lastname')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "New record created successfully";
        } else {
            $_SESSION['error'] = "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center mb-4">User Registration</h2>
                        <?php
                        if(isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
                            unset($_SESSION['error']);
                        }
                        if(isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
                            unset($_SESSION['success']);
                        }
                        ?>
                        <form action="register.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="full_name">Full Name/Username</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" id="phone_number" name="phone_number" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="profile_pic">Profile Picture</label>
                                <input type="file" id="profile_pic" name="profile_pic" class="form-control-file">
                            </div>
                            <div class="form-group">
                                <label for="firstname">First Name</label>
                                <input type="text" id="firstname" name="firstname" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="middlename">Middle Name</label>
                                <input type="text" id="middlename" name="middlename" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="lastname">Last Name</label>
                                <input type="text" id="lastname" name="lastname" class="form-control">
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary btn-block">Register</button>
                            <p class="mb-0">
                            <a href="login.php" class="text-center">Have account already? GO LOG IN</a>
                        </form>
                    </div>
                </div
