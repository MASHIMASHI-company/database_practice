<?php
?>
<!DOCTYPE html>
    <html>
    <head> 
        <meta charset="utf-8"> 
        <title>dashboard</title>
        <link rel="stylesheet" href="../css/main.css">
    </head>
    <body>  
        <header>
            <img src="../image/1c5a6078-b57d-47e9-b234-2022e121fab6.png">
            <div>MASHIMASHI COMPANY</div>
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
    </body>
</html>