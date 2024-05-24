<?php
// Start session
session_start();

// Include header file
include('includes/header.php');

// Include database connection
include('includes/db-conn.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch user information from the database based on the user ID
$query = "SELECT username, password, profile_pic, first_name, middle_name, last_name, email FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);

// Check if user exists
if(mysqli_num_rows($result) > 0) {
    // If user exists, fetch user details
    $user = mysqli_fetch_assoc($result);
} else {
    // If user does not exist, display error message
    echo "User not found.";
    exit();
}

// Check if form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize user input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $profile_pic = mysqli_real_escape_string($conn, $_POST['profile_pic']);

    // Handle profile picture upload
    if ($_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        // If profile picture is uploaded successfully
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        // Check if uploaded file is an image
        $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
        if($check !== false) {
            // File is an image
            $uploadOk = 1;
        } else {
            // File is not an image
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["profile_pic"]["size"] > 500000) {
            // If file size exceeds limit, display error message
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow only certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            // If file format is not allowed, display error message
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if upload was successful
        if ($uploadOk == 0) {
            // If upload failed, display error message
            echo "Sorry, your file was not uploaded.";
        } else {
            // If upload was successful, move the file to the target directory
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                // File uploaded successfully, update user information in the database
                $update_query = "UPDATE users SET username = '$username', password = '$password', profile_pic = '$profile_pic', first_name = '$first_name', middle_name = '$middle_name', last_name = '$last_name', email = '$email', profile_pic = '$target_file' WHERE user_id = $user_id";

                // Execute the update query
                if(mysqli_query($conn, $update_query)) {
                    // If update is successful, redirect to user profile page with success message
                    header("Location: user-profile.php?success=User information updated successfully.");
                    exit();
                } else {
                    // If update fails, display error message
                    echo "Error updating user information: " . mysqli_error($conn);
                }
            } else {
                // If file upload failed, display error message
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        // If no new profile picture is uploaded, update other user information
        $update_query = "UPDATE users SET username = '$username', password = '$password', first_name = '$first_name', middle_name = '$middle_name', last_name = '$last_name', email = '$email' WHERE user_id = $user_id";

        // Execute the update query
        if(mysqli_query($conn, $update_query)) {
            // If update is successful, redirect to user profile page with success message
            header("Location: user-profile.php?success=User information updated successfully.");
            exit();
        } else {
            // If update fails, display error message
            echo "Error updating user information: " . mysqli_error($conn);
        }
    }
}
?>

<!-- HTML content for the edit user form -->
<!-- The form allows users to update their profile information -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Edit User</h4>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <!-- Input fields for updating user information -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" value="<?php echo $user['password']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo $user['first_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control" value="<?php echo $user['middle_name']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo $user['last_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="profile_pic" class="form-label">Profile Picture</label>
                        <input type="file" id="profile_pic" name="profile_pic" class="form-control">
                    </div>
                    <!-- Submit button to update user information -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update User</button>
                        <button type="submit" class="btn btn-primary">Back</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer file
include('includes/footer.php');
?>
