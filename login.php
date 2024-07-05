<?php
include 'connection.php'; 
// Start session to store user information
session_start();

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute SQL query to fetch user data
    $query = "SELECT * FROM users WHERE username = :username";
    $statement = $connection->prepare($query);
    $statement->execute(['username' => $username]);
    $user = $statement->fetch();

    // Check if a user was found with the provided username
    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, store user information in session
            $_SESSION['username'] = $user['username'];
            $_SESSION['isAdmin'] = $user['is_admin'];

            // Redirect user to a logged-in page
            header("Location: index.php");
            exit;
        } else {
            // Password incorrect
            $error = "❌ Error: Invalid username or password";
        }
    } else {
        // Username not found
        $error = "❌ Error: Invalid username or password";
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
                            <form class="custom-form subscribe-form" action="login.php" method="post" role="form">
                                <h4 class="mb-4 pb-2"><i class="bi-back"></i> Login</h4>

                                <?php if(isset($error)) { ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?php echo $error; ?>
                                    </div>
                                <?php } ?>

                                <input type="text" name="username" id="subscribe-email" class="form-control" placeholder="username" required="">
                                <input type="password" name="password" id="subscribe-email" class="form-control" placeholder="password" required="">

                                <div class="col-lg-12 col-12">
                                    <button type="submit" class="form-control">Login</button>
                                </div>
                            </form>
                            <nav class="new-account">
                                    <a href="register.php">Don't have Account ?</a>
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
