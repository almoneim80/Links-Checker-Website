<?php
include '../connection.php';
session_start();


// Fetch users from the database
$query = "SELECT * FROM users";
$statement = $connection->prepare($query);
$statement->execute();
$users = $statement->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['p']) && !empty($_GET['p'])) {
    $userId = $_GET['p'];

    // Check if the user exists in the database
    $query = "SELECT * FROM users WHERE user_id = :user_id";
    $statement = $connection->prepare($query);
    $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Toggle the is_admin value
        $newAdminStatus = ($user['is_admin'] == 0) ? 1 : 0;

        // Update the user's is_admin status in the database
        $updateQuery = "UPDATE users SET is_admin = :is_admin WHERE user_id = :user_id";
        $updateStatement = $connection->prepare($updateQuery);
        $updateStatement->bindParam(':is_admin', $newAdminStatus, PDO::PARAM_INT);
        $updateStatement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $updateStatement->execute();

        // Redirect back to the index page or any other page as needed
        header("Location: index.php");
        exit();
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <?php include("head.php"); ?>
</head>

<body>
    <?php include("Nav.php"); ?>

    <div class="app-container">
        <div class="main-content">
            <div class="row gutters">
                <?php include('cards.php'); ?>
            </div>

            <div class="row gutters">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <p>Users</p>
                            <a href="../register.php" class="btn btn-primary">Add New User</a>
                        </div>
                        <div class="card-body">
                            <table id="scrollVertical" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Username</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $index => $user) : ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo $user['username']; ?></td>
                                            <?php
                                            if ($user['is_admin'] == 0) {
                                                echo '<td><a href="index.php?p=' . $user['user_id'] . '" class="btn btn-light">Promote to admin</a></td>';
                                            } else {
                                                echo '<td> <a href="index.php?p=' . $user['user_id'] . '" class="btn btn-light">Demotion from Admin</a></td>';
                                            }
                                            ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.sticky.js"></script>
    <script src="../js/custom.js"></script>

</body>

</html>