<?php
$username = $_SESSION["username"];
?>
<!DOCTYPE html>
    <html>
    <head> 
        <meta charset="utf-8"> 
        <title>dashboard</title>
        <link rel="stylesheet" href="../css/footer.css">
    </head>
    <body>  
        <footer>
            <div><?=$username?></div>
        </footer>
    </body>
</html>