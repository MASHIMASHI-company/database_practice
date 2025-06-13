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
            <div class="button">
                <div class="q">
                    Q1
                </div>
            </div>
            <div class="problem">
                あいうえおかきくけこさしすせそたちつてとなにぬねのあいうえおかきくけこさしすせそたちつてとなにぬねの
            </div>
        </div>

        <div class="options">
            <label><input type="radio" name="q1" value="1"> 選択肢1</label>
            <label><input type="radio" name="q1" value="2"> 選択肢2</label>
            <label><input type="radio" name="q1" value="3"> 選択肢3</label>
            <label><input type="radio" name="q1" value="4"> 選択肢4</label>
        </div>

        <div class="next-button-container">
            <a class="fixed-button next-button" onclick="checkAndGo()">NEXT</a>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="../js/sidebar.js"></script>
    <script src="../js/mordol.js"></script>
    <script src="../js/button.js"></script>
</body>

</html>