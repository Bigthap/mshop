<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/data/users.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = trim($_POST['password'] ?? '');

  if (!validate_required([$email, $pass])) {
    $error = 'Please fill in both email and password.';
  } else {
    $found = null;
    $admin = null;

    // --- ใช้ for loop แทน foreach ---
    $keys = array_keys($USERS);
    for ($i = 0; $i < count($keys); $i++) {
      $u = $USERS[$keys[$i]];
      if (strcasecmp($u['email'], $email) === 0 && $u['password'] === $pass) {
        if ($u['role'] === 'admin') {
          $admin = $u;
        } else {
          $found = $u;
        }
        break; // เจอแล้วไม่ต้องวนต่อ
      }
    }

    if ($found || $admin) {
      if ($found) {
        header('Location: /mshop/main.php');
        exit;
      } else {
        header('Location: /mshop/admin/admin_main.php');
        exit;
      }
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
      <h1>Sign in</h1>
      <p class="sub">Use your demo account to continue.</p>

      <label>Email</label>
      <input name="email" type="email" placeholder="you@example.com" required>

      <label>Password</label>
      <input name="password" type="password" placeholder="••••••••" required>

      <button class="btn" type="submit">Sign in</button>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <p style="text-align:center; margin-top:12px"><a href="/mshop/register.php">Create account</a></p>
    </form>
  </div>
</body>
</html>
