<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $message = htmlspecialchars($_POST["message"]);

    echo "<h2 style='color: white;'>ขอบคุณสำหรับ Feedback!</h2>";
    echo "<p style='color: white;'><b>ชื่อ:</b> $name</p>";
    echo "<p style='color: white;'><b>ข้อความ:</b> $message</p>";

    $data = "ชื่อ: $name\nข้อความ: $message\n--------------------\n";
    file_put_contents("feedback.txt", $data, FILE_APPEND);
} else {
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
    }
    input, textarea {
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
  <form method="post" action="">
    <label>ชื่อ:</label>
    <input type="text" name="name" required>

    <label>ข้อความ:</label>
    <textarea name="message" rows="5" required></textarea>

    <button type="submit">ส่ง Feedback</button>
  </form>
</body>
</html>
<?php } ?>