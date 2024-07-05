<?php
// Database connection
include 'connection.php';
session_start();
$username = $_SESSION['username'];

$query = $query = "SELECT user_id FROM users WHERE username =:username ";
$statement = $connection->prepare($query);
// Bind the parameter
$statement->bindParam(':username', $username, PDO::PARAM_STR);
// Execute the statement
$statement->execute();
// Fetch the result
$result = $statement->fetch(PDO::FETCH_ASSOC);
// Check if a row is found
if ($result)
    // Retrieve the user_id
    $user_id_From_Session = $result['user_id'];

if (isset($_POST['post'])) {
    // Retrieve comment content from the form
    $content = $_POST['comment'];

    // Prepare and execute the SQL query to insert the comment into the database
    $query = "INSERT INTO comments (parent_comment_id, user_id, content, created_at, updated_at, likes) 
              VALUES (:parent_comment_id, :user_id, :content, NOW(), NOW(), 0)";
    $statement = $connection->prepare($query);
    $statement->execute([
        'parent_comment_id' => 0, // Assuming it's a root comment
        'user_id' => $user_id_From_Session,
        'content' => $content,
    ]);

    // Redirect back to the page after inserting the comment
    header("Location: comments.php");
    exit;
}

if (isset($_POST['reply'])) {
    // Retrieve comment content from the form
    $content = $_POST['replycontent'];
    $username = $_POST['username'];
    $parent_comment_id = $_POST['comment_id'];

    // Prepare and execute the SQL query to insert the comment into the database
    $query = "INSERT INTO comments (parent_comment_id, user_id, content, created_at, updated_at, likes) 
              VALUES (:parent_comment_id, :user_id, :content, NOW(), NOW(), 0)";
    $statement = $connection->prepare($query);
    $statement->execute([
        'parent_comment_id' =>  $parent_comment_id,
        'user_id' => $user_id_From_Session,
        'content' => $content,
    ]);

    // Redirect back to the page after inserting the comment
    header("Location: comments.php");
    exit;
}

if (isset($_GET['like'])) {
    $query = "UPDATE comments SET likes = likes + 1 WHERE comment_id = :comment_id";
    $statement = $connection->prepare($query);
    $statement->bindParam(':comment_id', $_GET['like'] , PDO::PARAM_INT);
    $statement->execute();
}

?>

<!doctype html>
<html lang="en">

<head>
    <?php include("head.php"); ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body id="top">
    <main>
        <?php include("Nav.php"); ?>


        <section class="hero-section d-flex justify-content-center align-items-center" id="section_1" class="gradient-custom">
            <div class="container my-2 py-2">
                <h4 class="text-center mb-4 pb-2"><i class="bi-back"></i> Comments</h4>
                <div class="row d-flex justify-content-center">
                    <div class="col-md-12 col-lg-10 col-xl-8">
                        <div class="card">
                            <div class="card-body p-4">
                                <div class="row mt-3">
                                    <div class="col">
                                        <?php
                                        $query = "SELECT c.*, u.username 
                                       FROM comments c
                                       JOIN users u ON c.user_id = u.user_id
                                       WHERE c.parent_comment_id = 0";
                                        $statement = $connection->prepare($query);
                                        $statement->execute();
                                        $comments = $statement->fetchAll();

                                        foreach ($comments as $row) {

                                            echo '
                                            <div class="d-flex flex-start mt-4">
                                            <div id="comment-person"><a href="#" class="navbar-icon bi-person smoothscroll"></a></div>
                                            <div class="flex-grow-1 flex-shrink-1">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 style="font-size: 15px;">  ' . $row['username'] . ' <span class="small">- ' . $row['created_at'] . '</span></h6>
                                                        <div id="action-icon">
                                                        <a href="comments.php?u=' . $row['username'] . ' &cid=' . $row['comment_id'] . ' " class="navbar-icon bi-reply bi-sm"></a>
                                                        <a href="comments.php?like=' . $row['comment_id'].' " class="bi bi-heart bi-sm like-btn"></a>
                                                        <span class="like-count">' . $row['likes'] . '</span>
                                                        </div>
                                                    </div>
                                                    <p class="small mb-0">
                                                        ' . $row['content'] . '
                                                    </p>
                                                </div>';

                                            $query = "SELECT c.*, u.username 
                                            FROM comments c
                                            JOIN users u ON c.user_id = u.user_id
                                            WHERE c.parent_comment_id = :comment_id";
                                            $statement_reply = $connection->prepare($query);
                                            $statement_reply->bindParam(':comment_id', $row['comment_id'], PDO::PARAM_INT);
                                            $statement_reply->execute();
                                            $replies = $statement_reply->fetchAll();

                                            if ($replies) {
                                                foreach ($replies as $reply) {
                                                    echo '
                                                <div class="d-flex flex-start mt-4">
                                                <div id="comment-person"><a href="#" class="navbar-icon bi-person smoothscroll"></a></div>
                                                    <div class="flex-grow-1 flex-shrink-1">
                                                        <div>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                        <h6 style="font-size: 15px;">  ' . $reply['username'] . ' <span class="small">-' . $row['created_at'] . '</span></h6>
                                                                <div id="action-icon">
                                                                <a href="comments.php?like=' . $reply['comment_id'].' " class="bi bi-heart bi-sm like-btn"></a>
                                                                <span class="like-count">' . $reply['likes'] . '</span>
                                                                </div>
                                                        </div>
                                                        <p class="small mb-0">
                                                        ' . $reply['content'] . '
                                                    </p>
                                                        </div>
                                                    </div>
                                                    </div>
                                        ';
                                                }
                                            }

                                            echo '
                                                </div>
                                            </div>';
                                        }

                                        ?>

                                        <div class="card-footer py-3 border-0 mt-5" style="background-color: #f8f9fa;">
                                            <?php
                                            if (isset($_GET['u']) && isset($_GET['cid'])) {
                                                $username = $_GET['u'];
                                                $commentId = $_GET['cid'];
                                                echo '
                                    <form action="comments.php" method="POST">
                                    <div class="d-flex flex-start w-100">
                                        <div id="comment-person"><a href="#" class="navbar-icon bi-person smoothscroll"></a></div>
                                        <div class="form-outline w-100">
                                            <textarea class="form-control" id="textAreaExample" name="replycontent"  placeholder=" reply on ' . $username . ' " rows="4" style="background: #fff;"></textarea>
                                        </div>
                                         <input type="text" name="username" value=' . $username . ' hidden />
                                         <input type="number" name="comment_id" value=' . $commentId . ' hidden />
                                    </div>
                                    <div class="float-end mt-2 pt-1">
                                        <button type="submit" name="reply" class="btn btn-primary btn-sm">Post reply</button>
                                        <a href="comments.php"  class="btn btn-outline-primary btn-sm">Cancel</a>
                                    </div>
                                </form>
                                    ';
                                            } else {
                                                echo '
                                    <form action="comments.php" method="POST">
                                        <div class="d-flex flex-start w-100">
                                            <div id="comment-person"><a href="#" class="navbar-icon bi-person smoothscroll"></a></div>
                                            <div class="form-outline w-100">
                                                <textarea class="form-control" id="textAreaExample" name="comment" rows="4" style="background: #fff;"></textarea>
                                            </div>
                                        </div>
                                        <div class="float-end mt-2 pt-1">
                                            <button type="submit" name="post" class="btn btn-primary btn-sm">Post comment</button>
                                            <button type="reset" class="btn btn-outline-primary btn-sm">Cancel</button>
                                        </div>
                                    </form>
                                    ';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        </section>
    </main>


    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.sticky.js"></script>
    <script src="js/custom.js"></script>

</body>

</html>