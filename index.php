<?php
include 'connection.php';
session_start();
?>

<!doctype html>
<html lang="en">

<head>
<?php include("head.php"); ?>
</head>

<body id="top">
  <main>
    <?php include("Nav.php"); ?>
    <?php include("header.php"); ?>
    <?php include("about.php"); ?>
    <?php include("services.php"); ?>
  </main>
  <?php include("footer.php"); ?>

  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/jquery.sticky.js"></script>
  <script src="js/custom.js"></script>

</body>

</html>