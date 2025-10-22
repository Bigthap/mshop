<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '../data/users.php';
start_session_once();

// ถ้าเข้าสู่ระบบแล้ว พากลับหน้าที่เหมาะสม
if (current_user()) {
  if (is_admin()) { header('Location: ../mshop/admin/admin_users.php'); exit; }
  header('Location: /mshop/main.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $pass  = $_POST['password'] ?? '';

  if (!validate_required([$email, $pass])) {
    $error = 'Please fill in both email and password.';
  } else {
    // หา user แบบไม่ใช้ foreach (ใช้ for + selection)
    $ids = array_keys($USERS);
    $found = null;
    for ($k = 0; $k < count($ids); $k++) {
      $id = $ids[$k];
      if (strcasecmp($USERS[$id]['email'], $email) === 0) { $found = $USERS[$id]; break; }
    }

    if ($found && $found['password'] === $pass) {
      $_SESSION['user'] = [
        'name'  => $found['name'],
        'email' => $found['email'],
        'role'  => $found['role'],
      ];
      if ($found['role'] === 'admin') { header('Location: ../mshop/admin/admin_users.php'); exit; }
      header('Location: main.php'); exit;
    } else {
      $error = 'Invalid email or password.';
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login • MShop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --acc:#22d3ee; }
    *{box-sizing:border-box} body{margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto; background:linear-gradient(135deg,#0b1023,#0f172a);} 
    .wrap{min-height:100dvh; display:grid; place-items:center; padding:24px}
    .card{width:100%; max-width:420px; background:var(--card); color:var(--text); border:1px solid #1f2937; border-radius:16px; padding:28px; box-shadow:0 10px 30px rgba(0,0,0,.35)}
    h1{margin:0 0 4px; font-size:22px}
    p.sub{margin:0 0 20px; color:var(--muted); font-size:14px}
    label{display:block; font-size:13px; margin:12px 0 6px; color:#cbd5e1}
    input[type="email"],input[type="password"]{ width:100%; padding:12px 14px; border-radius:12px; border:1px solid #334155; background:#0b1220; color:#e5e7eb; outline:none; }
    input:focus{border-color:#475569}
    .row{display:flex; align-items:center; justify-content:space-between; margin-top:10px}
    .hint{font-size:12px; color:#94a3b8}
    .btn{width:100%; margin-top:16px; padding:12px 14px; border:0; border-radius:12px; background:var(--acc); color:#0b1020; font-weight:700; cursor:pointer}
    .btn:hover{filter:brightness(1.05)}
    .error{margin-top:12px; color:#fecaca; font-size:13px; background:#450a0a; border:1px solid #7f1d1d; padding:8px 10px; border-radius:10px}
    .footer{margin-top:18px; font-size:13px; color:#9ca3af; text-align:center}
    a{color:#67e8f9; text-decoration:none}
    a:hover{text-decoration:underline}
  </style>
</head>
<body>
  <div class="wrap">
    <form class="card" method="post" action="">
      <h1>Sign in</h1>
      <p class="sub">Use your mock account to continue.</p>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" placeholder="you@example.com" required>

      <label for="password">Password</label>
      <input id="password" name="password" type="password" placeholder="••••••••" minlength="4" required>

      <div class="row">
        <span class="hint">Try: <code>big@t.com / 123456</code> or <code>admin@t.com / admin123</code></span>
        <a href="/mshop/register.php">Create account</a>
      </div>

      <button class="btn" type="submit">Sign in</button>

      <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <div class="footer">
        <small>For demo only • PHP array DB • Session-based</small>
      </div>
    </form>
  </div>
</body>
</html>
