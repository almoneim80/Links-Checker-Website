<?php
// Include the database connection file
include_once("connection.php");

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $username = $_POST["username"];
    $password = $_POST["password"]; // Note: You should hash the password for security reasons

    // Check if fields are empty
    if (empty($username) || empty($password)) {
        $error = "❌ Error: Please fill in all fields.";
    } elseif (strpos($username, ' ') !== false) { // Check if username contains spaces
        $error = "❌ Error: Username cannot contain spaces.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) { // Check password complexity
        $error = "❌ Error: Password must be at least 8 characters long and contain upper and lower case letters, numbers, and symbols.";
    } else {
        // Check if username already exists
        $query = "SELECT COUNT(*) AS count FROM users WHERE username = :username";
        $statement = $connection->prepare($query);
        $statement->bindParam(":username", $username);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            // Username already exists
            $error = "❌ Error: Username already exists. Please choose a different username.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // SQL query to insert new user
            $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
            // Prepare the SQL statement
            $statement = $connection->prepare($sql);
            // Bind parameters
            $statement->bindParam(":username", $username);
            $statement->bindParam(":password", $hashed_password);

            // Execute the statement
            if ($statement->execute()) {
                header("Location: login.php");
                exit();
            } else {
                // Registration failed
                $error = "❌ Error: There was an error and you could not create your account. Try again.";
            }
        }
    }
}
?>



<!doctype html>
<html lang="en">

<head>
    <?php include("head.php"); ?>
</head>

<body id="top">
    <main>
        <?php include("Nav.php"); ?>

        <section class="site-header d-flex flex-column justify-content-center align-items-center">
            <div class="container">
                <div class="container">
                    <div class="row justify-content-center align-items-center">

                        <div class="col-lg-5 col-12 mb-5">
                            <form class="custom-form subscribe-form" action="#" method="post" role="form">
                                <h4 class="mb-4 pb-2"><i class="bi-back"></i> New Account</h4>


                                <div class="alert alert-danger 
                                <?php echo isset($error) ? '' : 'd-none'; ?>" role="alert">
                                    <?php echo isset($error) ? $error : ''; ?>
                                </div>

                                <input type="text" name="username" id="subscribe-email" class="form-control" placeholder="username">
                                <input type="password" name="password" id="subscribe-email" class="form-control" placeholder="password">

                                <div class="col-lg-12 col-12">
                                    <button type="submit" class="form-control">Register</button>
                                </div>
                            </form>
                            <nav class="new-account">
                                <a href="login.php">Already have Account ?</a>
                            </nav>
                        </div>

                        <div class="col-lg-5 col-12">
                            <div class="topics-detail-block bg-white shadow-lg">
                                <img src="images/topics/undraw_Remote_design_team_re_urdx.png" class="topics-detail-block-image img-fluid">
                            </div>
                        </div>

                    </div>
                </div>
        </section>

    </main>
    <?php include("footer.php"); ?>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.sticky.js"></script>
    <script src="js/custom.js"></script>

</body>

</html>