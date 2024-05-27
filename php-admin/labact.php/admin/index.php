<?php
// Start session
session_start();

// Include database connection
include('includes/db-conn.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: login.php");
    exit();
}

// Connect to the database
$conn = mysqli_connect($sname, $uname, $password, $db_name);

// Check if connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the user information from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT username, profile_pic FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the query was successful
if (!$result) {
    die("Error executing query: " . $conn->error);
}

// Fetch user details
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // Redirect to login if user not found
    header("Location: login.php");
    exit();
}

// Check if 'username' key exists in $user array before accessing it
if (isset($user['username'])) {
    $username = htmlspecialchars($user['username']);
} else {
    // Handle the case where 'username' key is not present in $user array
    $username = "Unknown";
}

// Include header, topbar, and sidebar files
include('includes/header.php');
include('includes/topbar.php');
include('includes/sidebar.php');
?>

<div class="wrapper">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12 text-center">
                        <h1>Welcome <?php echo htmlspecialchars($user['username']); ?></h1>
                        <div class="image-box">
    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" class="img-thumbnail" alt="User Image" style="width:500px; height:500px;">
</div>
                        <div class="info">
                            <button type="button" class="btn btn-primary" onclick="location.href='user-profile.php?user_id=<?php echo $user_id; ?>';">User Profile</button>
                        </div>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Your content goes here -->
                    </div>
                    <!-- ./col -->
                </div>
                <!-- /.row -->
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <?php
    include('includes/footer.php');
    ?>
</div>
