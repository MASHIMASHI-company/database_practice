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
            <div class="level">0/20</div>
        </div>
        <div class="question">
            <div class="result" onclick="toggleAccordion()">
                Q1 <span id="accordionArrow" class="arrow">▼</span>
            </div>
            <div class="problem-statement" id="problemContent">
                あいうえおかきくけこさしすせそたちつてとなにぬねのあいうえおかきくけこさしすせそたちつてとなにぬねの
            </div>
        </div>
        <div class="home-button-container">
        <a class="fixed-button home-button" href="dashboard.php">Dash Board</a>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="../js/sidebar.js"></script>
    <script src="../js/mordol.js"></script>
    <script src="../js/button.js"></script>
    <script src="../js/acoordion.js"></script>
</body>

</html>