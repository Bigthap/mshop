<?php
declare(strict_types=1);

// path เก็บข้อมูล
$dataFile = __DIR__ . '/data/support.php';

// โหลดข้อมูลเดิม (ถ้ายังไม่มีไฟล์ ให้เริ่มด้วย array ว่าง)
$SUPPORT = [];
if (file_exists($dataFile)) {
  $SUPPORT = require $dataFile;
}

// ถ้ามีการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if ($email !== '' && $message !== '') {
    // สร้าง id ใหม่
    $nextId = empty($SUPPORT) ? 1 : max(array_keys($SUPPORT)) + 1;

    $SUPPORT[$nextId] = [
      'email' => $email,
      'message' => $message,
    ];

    // เขียนไฟล์กลับ
    $export = var_export($SUPPORT, true);
    $code = "<?php\n// Auto-generated support data\n\$SUPPORT = $export;\nreturn \$SUPPORT;\n";
    file_put_contents($dataFile, $code, LOCK_EX);

    echo "<p style='color:lightgreen'>ส่งข้อความสำเร็จ!<br></p>";
  } else {
    echo "<p style='color:red'>กรุณากรอกข้อมูลให้ครบ</p>";
  }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ส่ง Feedback</title>
  <style>
    body {
      font-family: sans-serif;
      max-width: 500px;
      margin: 50px auto;
      background-color: #1a2238;  
      color: white;               
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,0,0,0.3);
    }
    input, textarea {
      width: 100%;
      padding: 7px;
      margin: 10px 0;
      border: none;
      border-radius: 5px;
      background: #f4f4f4;
    }
    button {
      padding: 10px 20px;
      background: #16b916ff;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 5px;
      transition: 0.3s;
    }
    button:hover {
      background: #d8324c;
    }
  </style>
</head>
<body>
  <h2>ส่ง Feedback ถึงเรา</h2>
  <form method="post">
    <label>อีเมล:</label>
    <input type="email" name="email" required>

    <label>ข้อความ:</label>
    <textarea name="message" rows="5" required></textarea>

    <button type="submit">ส่ง Feedback</button>
  </form>
</body>
</html>
