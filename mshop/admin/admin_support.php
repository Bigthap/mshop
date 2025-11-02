<?php
declare(strict_types=1);
$dataFile = __DIR__ . '/../data/support.php';
$SUPPORT = file_exists($dataFile) ? require $dataFile : [];
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Admin - ข้อความจากผู้ใช้</title>
  <style>
    body {
      font-family: sans-serif;
      background-color: #0e1626;
      color: #fff;
      padding: 30px;
    }
    .container {
      max-width: 800px;
      margin: auto;
    }
    h1 {
      text-align: center;
      color: #29b6f6;
    }
    .card {
      background: #1f2a44;
      border-radius: 10px;
      padding: 15px 20px;
      margin: 15px 0;
      box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    .email {
      font-weight: bold;
      color: #4fc3f7;
    }
    .message {
      margin-top: 8px;
      color: #e3f2fd;
    }
    .container{width:min(1100px,95%);margin:24px auto}
    .nav{
      position:sticky; top:16px; z-index:10;
      display:flex; justify-content:space-between; align-items:center; gap:12px;
      padding:12px 16px; border-radius:14px;
      background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.02));
      border:1px solid var(--ring); backdrop-filter:saturate(140%) blur(6px);
    }
    .brand{display:flex; gap:10px; align-items:center; text-decoration:none; color:#fff}
    .logo{width:28px;height:28px;border-radius:8px;background:conic-gradient(from 220deg,#2563eb,#7c3aed)}
    .links{display:flex; gap:8px; flex-wrap:wrap}
    .links a{color:#fff; text-decoration:none; font-size:14px; padding:8px 12px; border-radius:10px; border:1px solid transparent}
    .links a:hover{background:#202025; border-color:#2c2c30}
  </style>
</head>
<body>
    <div class="container">
    <div class="nav">
        <a class="brand" href="#"><span class="logo"></span><span>Admin • Support</span></a>
        <div class="links">
        <a href="../admin/admin_main.php">แอดมิน</a>
        <a href="../admin/edit_toppings.php">ท็อปปิ้ง</a>
        <a href="../admin/admin_users.php" aria-current="page">ผู้ใช้</a>
        <a href="admin_support.php">ซัพพอร์ต</a>
        <a href="../main.php">ร้านค้าหลัก</a>
        <a href="../login.php">Logout</a>
        </div>
    </div>

  <div class="container">
    <h1>รายการ Feedback ทั้งหมด</h1>
    <?php if (empty($SUPPORT)) : ?>
      <p style="text-align:center;">ยังไม่มีข้อความใด ๆ</p>
    <?php else: ?>
      <?php foreach (array_reverse($SUPPORT, true) as $id => $item): ?>
        <div class="card">
          <div class="email">#<?= $id ?> — <?= htmlspecialchars($item['email'], ENT_QUOTES, 'UTF-8') ?></div>
          <div class="message"><?= nl2br(htmlspecialchars($item['message'], ENT_QUOTES, 'UTF-8')) ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
