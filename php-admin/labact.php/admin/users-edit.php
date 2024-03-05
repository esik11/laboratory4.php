<?php 
    
    include('includes/header.php');

    include('includes/db_conn.php');

    if(isset($_GET['id'])) {
        $user_id = $_GET['id'];

        $query = "SELECT * FROM user_profile WHERE user_id = $user_id";
        $result = mysqli_query($conn, $query);

        if(mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
        } else {
            echo "User not found.";
            exit();
        }
    } else {
        echo "User ID not provided.";
        exit();
    }

    // Check if form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate and sanitize user input
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $phone_number = $_POST['phone_number'];
        $address = $_POST['address'];

        // Update user information in the database
        $update_query = "UPDATE user_profile SET full_name = '$full_name', email = '$email', password = '$password', phone_number = '$phone_number', address = '$address' WHERE user_id = $user_id";
        
        if(mysqli_query($conn, $update_query)) {
            header("Location: users.php?success=User information updated successfully.");
            exit();
        } else {
            echo "Error updating user information: " . mysqli_error($conn);
        }
    }
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Edit User</h4>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="firstname" class="form-label">Firstname</label>
                        <input type="firstname" id="firstname" name="firstname" class="form-control" value="<?php echo $user['firstname']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Middlename</label>
                        <input type="middlename" id="middlename" name="middlename" class="form-control" value="<?php echo $user['middlename']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="lastname" class="form-label">Lastname</label>
                        <input type="lastname" id="lastname" name="lastname" class="form-control" value="<?php echo $user['lastname']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" value="<?php echo $user['password']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?php echo $user['phone_number']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?php echo $user['address']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">profile_pic</label>
                        <input type="file" id="profilepic" name="profilepic" class="form-control" value="<?php echo $user['profile_pic']; ?>">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update User</button>
                
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
