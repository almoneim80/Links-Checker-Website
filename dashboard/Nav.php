<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi-back"></i>
            <span>Link Doctor</span>
        </a>

        <div class="d-lg-none ms-auto me-4">
            <a href="#" class="navbar-icon bi-person smoothscroll"></a>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-lg-5 me-lg-auto">
                <li class="nav-item">
                    <a class="nav-link click-scroll" href="../index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link click-scroll" href="../examination.php">Examination</a>
                </li>
                <?php
                if (isset($_SESSION['username'])) {
                    echo '
                    <li class="nav-item">
                    <a class="nav-link click-scroll" href="../comments.php">Comments</a>
                </li>
                    ';
                } else {
                    echo ' 
                    <li class="nav-item">
                    <a class="nav-link click-scroll" href="../login.php">Comments</a>
                </li>
                    ';
                }


                if (isset($_SESSION['username']) && isset($_SESSION['isAdmin'])) {
                    if ($_SESSION['isAdmin'] == 1) {
                        echo '<li class="nav-item">
                        <a class="nav-link click-scroll" href="dashboard/index.php">Dashboard</a>
                    </li>';
                    }
                }
                ?>
            </ul>
            <div class="d-none d-lg-block">
                <?php if (isset($_SESSION['username'])) {
                    $username = $_SESSION['username'];
                    echo '<a href="../logout.php" class="navbar-icon bi-box-arrow-left" title="Logout from ' . $username . ' "></a>';
                } else {
                    echo '<a href="../login.php" class="navbar-icon bi-person smoothscroll" title="Account"></a>';
                }
                ?>
            </div>
        </div>
    </div>
</nav>