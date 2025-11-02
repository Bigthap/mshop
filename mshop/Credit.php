<?php
$members = [
    ["นาย พีรดนย์",  "พันธ์อ่อน 682110065"],
    ["นาย ธีรเมธ",   "เอ้งฉ้วน 682110045"],
    ["นาย อรรถวิทย์", "แก้วบุญเรือง 682110099"],
    ["นาย ธนภัทร",  "ใจคำปัน 682110039"],
    ["นาย เจษฎากร",   "ขันธรรม 682110013"],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8" />
<title>Credit</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    :root{
        --bg: #1A2238;
        --text: #EAF2FF;
        --line: rgba(255,255,255,.7);
    }
    body{
        margin:0;
        background: var(--bg);
        color: var(--text);
        font-family: "Noto Sans Thai", sans-serif;
        display:flex;
        align-items:center;
        justify-content:center;
        height:100vh;
    }
    .frame{
        border: 2px solid var(--line);
        border-radius: 25px;
        padding: 60px 100px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        text-align: center;
    }
    h1{
        margin-bottom: 40px;
        font-size: 48px;
    }
    .grid{
        display:grid;
        grid-template-columns: auto auto;
        justify-content: center;
        column-gap: 60px;
        row-gap: 18px;
    }
    .name{
        font-size: 28px;
    }
</style>
</head>
<body>
    <div class="frame">
        <h1>Credit</h1>
        <div class="grid">
            <?php foreach ($members as $row): ?>
                <div class="name"><?= htmlspecialchars($row[0]) ?></div>
                <div class="name"><?= htmlspecialchars($row[1]) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
