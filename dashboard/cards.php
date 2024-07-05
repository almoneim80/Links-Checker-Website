<?php
// Include the database connection file
include_once("../connection.php");

// Execute SQL query to count the number of users
$query = "SELECT COUNT(*) AS user_count FROM users";
$statement = $connection->prepare($query);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);
$userCount = $result['user_count'];


// Execute SQL query to count the number of links
$query = "SELECT COUNT(*) AS links_count FROM links";
$statement = $connection->prepare($query);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);
$linksCount = $result['links_count'];
?>

<div class="col-md-4 col-sm-3">
    <div class="card">
        <div class="card-body">
            <div class="stats-widget">
                <div class="stats-widget-header">
                    <i class="icon-person"></i>
                </div>
                <div class="stats-widget-body">
                    <ul class="row no-gutters">
                        <li class="col-sm-6 col">
                            <h6 class="title">Users</h6>
                        </li>
                        <li class="col-sm-6 col">
                            <h4 class="total"><?php echo $userCount; ?></h4>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-md-4 col-sm-3">
    <div class="card">
        <div class="card-body">
            <div class="stats-widget">
                <div class="stats-widget-header">
                    <i class="icon-link"></i>
                </div>
                <div class="stats-widget-body">
                    <ul class="row no-gutters">
                        <li class="col-sm-6 col">
                            <h6 class="title">Scanned Links</h6>
                        </li>
                        <li class="col-sm-6 col">
                            <h4 class="total"><?php echo $linksCount; ?></h4>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
