<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../mshop/data/users.php'; // ป้องกัน path ผิดเวลา include ข้ามโฟลเดอร์? (ปรับให้เหลือ __DIR__ . '/data/users.php' ถ้าไฟล์นี้อยู่ root)
// ✱ หมายเหตุ: ถ้า register.php อยู่ใน root เดียวกับ data → ใช้ require_once __DIR__ . '/data/users.php';
// จากที่คุณบอก: require_once __DIR__ . '/../data/users.php';  ← ตรงนี้มักลืมใส่ '/' หลัง __DIR__  จึงต้องเป็น '/../'
start_session_once();

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = trim($_POST['password'] ?? '');

  if (!validate_required([$name,$email,$pass])) {
    $err = 'Please fill in all fields.';
  } else {
    // โหลด users ปัจจุบันใหม่ให้ชัวร์ (รองรับกรณีมีการแก้ไขไฟล์ภายนอก)
    $USERS = (function(){
      $tmp = [];
      @require users_file_path();
      return isset($USERS) && is_array($USERS) ? $USERS : $tmp;
    })();

    // ป้องกัน email ซ้ำ (ไม่ใช้ foreach)
    $ids = array_keys($USERS);
    $exists = false;
    for ($k=0; $k<count($ids); $k++) {
      $id = $ids[$k];
      if (strcasecmp($USERS[$id]['email'], $email) === 0) { $exists = true; break; }
    }
    if ($exists) {
      $err = 'Email already registered.';
    } else {
      // หา id ถัดไป
      $nextId = empty($ids) ? 1 : (max($ids) + 1);

      // เพิ่มผู้ใช้ใหม่ลง array
      $USERS[$nextId] = [
        'name' => $name,
        'email' => $email,
        'password' => $pass, // เดโมเท่านั้น (plaintext)
        'role' => 'customer',
      ];

      // เขียนกลับไฟล์ /data/users.php แบบถาวร โดยไม่สร้างไฟล์ใหม่ผิดที่
      if (!users_save_php($USERS)) {
        $err = 'Cannot write to /data/users.php (check file permissions & path).';
      } else {
        $_SESSION['user'] = [ 'name'=>$name, 'email'=>$email, 'role'=>'customer' ];
        header('Location: /mshop/main.php'); exit;
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Create account • MShop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --acc:#22d3ee; }
    *{box-sizing:border-box} body{margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto; background:linear-gradient(135deg,#0b1023,#0f172a);} 
    .wrap{min-height:100dvh; display:grid; place-items:center; padding:24px}
    .card{width:100%; max-width:460px; background:var(--card); color:var(--text); border:1px solid #1f2937; border-radius:16px; padding:28px; box-shadow:0 10px 30px rgba(0,0,0,.35)}
    h1{margin:0 0 4px; font-size:22px}
    p.sub{margin:0 0 20px; color:var(--muted); font-size:14px}
    label{display:block; font-size:13px; margin:12px 0 6px; color:#cbd5e1}
    input{ width:100%; padding:12px 14px; border-radius:12px; border:1px solid #334155; background:#0b1220; color:#e5e7eb; outline:none; }
    input:focus{border-color:#475569}
    .btn{width:100%; margin-top:16px; padding:12px 14px; border:0; border-radius:12px; background:var(--acc); color:#0b1020; font-weight:700; cursor:pointer}
    .btn:hover{filter:brightness(1.05)}
    .error{margin-top:12px; color:#fecaca; font-size:13px; background:#450a0a; border:1px solid #7f1d1d; padding:8px 10px; border-radius:10px}
    a{color:#67e8f9}
  </style>
</head>
<body>
  <div class="wrap">
    <form class="card" method="post" action="">
      <h1>Create account</h1>
      <p class="sub">ข้อมูลใหม่จะถูกเพิ่มลง users.php จริง</p>

      <label for="name">Name</label>
      <input id="name" name="name" type="text" placeholder="Your name" required>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" placeholder="you@example.com" required>

      <label for="password">Password</label>
      <input id="password" name="password" type="password" placeholder="••••••••" minlength="4" required>

      <button class="btn" type="submit">Create</button>

      <?php if($err): ?>
        <div class="error"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <p style="text-align:center; margin-top:12px"><a href="logout.php">Back to sign in</a></p>
    </form>
  </div>
</body>
</html>