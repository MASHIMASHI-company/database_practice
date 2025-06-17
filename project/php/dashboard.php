<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>dashboard</title>
    <link rel="stylesheet" href="../css/main.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="status-bar">
            <div class="level">0/300</div>
        </div>
        <div class="buttons">
            <div class="button">
                <a href="question.php?tag=HTML">HTML</a>
            </div>
            <div class="button">
                <a href="question.php?tag=CSS">CSS</a>
            </div>
            <div class="button">
                <a href="question.php?tag=JavaScript">JavaScript</a>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="../js/sidebar.js"></script>
    <script src="../js/mordol.js"></script>
</body>

</html>