<?php
    // $username = $_SESSION["username"];
    // $email = $_SESSION["email"];
?>
<!DOCTYPE html>
    <html>
    <head> 
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>dashboard</title>
        <link rel="stylesheet" href="../css/header.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Potta+One&display=swap" rel="stylesheet">
    </head>
    <body>  
        <header>
            <a href="dashboard.php"><img src="../image/1c5a6078-b57d-47e9-b234-2022e121fab6.png"></a>
            <a href="dashboard.php"><div>MASHIMASHI COMPANY</div></a>
            <div class="hamburger-menu">
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
            </div>
        </header>
        <div id="sidebar" class="sidebar">
            <ul>
                <li><a href="#">User Name</a></li>
                <li><a href="#">Email Address</a></li>
                <li><a href="#">Pass Word</a></li>
                <li><a href="logout.php">Log OUT</a></li>
                <li><a href="dashboard.php">Dash Board</a></li>
            </ul>
        </div>

        <div id="modal" class="modal">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2 id="modal-title">編集</h2>   
                <form id="modal-form">
                </form>
                <button id="save-button">保存</button>
            </div>
        </div>
        <script>
            let SESSION_USERNAME = <?= json_encode($username); ?>;
            let SESSION_EMAIL = <?= json_encode($email); ?>;
        </script>
    </body>
</html>