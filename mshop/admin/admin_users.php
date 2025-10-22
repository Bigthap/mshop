<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../data/users.php';

// สถิติง่าย ๆ
$total = count($USERS);
$roles = array_count_values(array_map(fn($u)=>$u['role'] ?? 'unknown', $USERS));
$admins = $roles['admin'] ?? 0;
$customers = $roles['customer'] ?? 0;
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>MShop • Admin Users</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --bg:#0f172a; --panel:#111827; --panel2:#0b1220; --text:#e5e7eb; --muted:#94a3b8;
      --ring:rgba(255,255,255,.1); --radius:16px; --accent:#22d3ee; --ok:#34d399; --warn:#f59e0b; --danger:#ef4444;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;
      color:var(--text);
      background:linear-gradient(135deg,#0b1020,#131a2e 60%,#0b1020);
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

    .panel{margin-top:18px; padding:18px; border-radius:20px; border:1px solid var(--ring); background:var(--panel)}
    h1{margin:0 0 8px; font-size:clamp(20px,3.2vw,28px)}
    .muted{color:var(--muted)}

    .stats{display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:10px}
    .stat{padding:12px 14px; border-radius:12px; background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.02)); border:1px solid var(--ring)}
    .stat .n{font-size:22px; font-weight:800}
    .stat .k{font-size:12px; color:var(--muted)}

    .toolbar{display:flex; gap:12px; align-items:center; justify-content:space-between; margin-top:16px; flex-wrap:wrap}
    .search{display:flex; gap:8px; align-items:center}
    .input{
      background:var(--panel2); border:1px solid rgba(255,255,255,.12); color:#fff;
      border-radius:10px; padding:10px 12px; min-width:240px;
    }

    .table-wrap{margin-top:12px; border:1px solid var(--ring); border-radius:14px; overflow:auto; background:linear-gradient(180deg,rgba(255,255,255,.02),rgba(255,255,255,.01))}
    table{width:100%; border-collapse:separate; border-spacing:0}
    thead th{
      position:sticky; top:0; z-index:1;
      background:#0f172a; color:#cfe8ff; text-align:left; font-weight:600; font-size:14px;
      border-bottom:1px solid var(--ring); padding:10px 12px;
    }
    tbody td{padding:10px 12px; border-bottom:1px solid rgba(255,255,255,.06); font-size:14px}
    tbody tr:hover{background:rgba(255,255,255,.04)}
    .id{font-variant-numeric:tabular-nums}
    .email a{color:var(--accent); text-decoration:none}
    .badge{
      display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600;
      border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.06)
    }
    .badge.admin{background:linear-gradient(180deg,#fca5a5,#ef4444); color:#290606}
    .badge.customer{background:linear-gradient(180deg,#86efac,#22c55e); color:#052012}

    .empty{padding:16px; text-align:center; color:var(--muted)}
    .btn{display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:10px; color:#fff; text-decoration:none; border:1px solid rgba(255,255,255,.18); background:linear-gradient(180deg,#0ea5b7,#0891b2)}
    .btn:hover{filter:brightness(1.05)}
    @media (max-width:700px){
      .stats{grid-template-columns:1fr}
      .input{min-width:180px}
      thead th:nth-child(3), tbody td:nth-child(3){display:none} /* ซ่อน Email บนจอแคบ */
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="nav">
      <a class="brand" href="#"><span class="logo"></span><span>Admin • Users</span></a>
      <div class="links">
        <a href="../admin/admin_main.php">แอดมิน</a>
        <a href="../admin/edit_toppings.php">ท็อปปิ้ง</a>
        <a href="../admin/admin_users.php" aria-current="page">ผู้ใช้</a>
        <a href="../main.php">ร้านค้าหลัก</a>
        <a href="../logout.php">Logout</a>
      </div>
    </div>

    <div class="panel">
      <h1>ผู้ใช้ทั้งหมด</h1>
      <div class="muted">จัดการรายชื่อผู้ใช้ของระบบ (ม็อกอัพจากไฟล์ <code>data/users.php</code>)</div>

      <div class="stats">
        <div class="stat"><div class="n"><?= number_format($total) ?></div><div class="k">ทั้งหมด</div></div>
        <div class="stat"><div class="n"><?= number_format($admins) ?></div><div class="k">ผู้ดูแล (admin)</div></div>
        <div class="stat"><div class="n"><?= number_format($customers) ?></div><div class="k">ลูกค้า (customer)</div></div>
      </div>

      <div class="table-wrap">
        <?php if (empty($USERS)): ?>
          <div class="empty">ยังไม่มีผู้ใช้</div>
        <?php else: ?>
          <table id="tbl">
            <thead>
              <tr>
                <th style="min-width:70px">ID</th>
                <th style="min-width:180px">Name</th>
                <th style="min-width:220px">Email</th>
                <th style="min-width:120px">Role</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $ids = array_keys($USERS);
                sort($ids, SORT_NUMERIC);
                foreach ($ids as $id):
                  $u = $USERS[$id];
                  $name  = htmlspecialchars($u['name']  ?? '', ENT_QUOTES, 'UTF-8');
                  $email = htmlspecialchars($u['email'] ?? '', ENT_QUOTES, 'UTF-8');
                  $role  = htmlspecialchars($u['role']  ?? 'unknown', ENT_QUOTES, 'UTF-8');
                  $roleClass = $role === 'admin' ? 'admin' : ($role === 'customer' ? 'customer' : '');
              ?>
                <tr data-name="<?= mb_strtolower($name,'UTF-8'); ?>" data-email="<?= mb_strtolower($email,'UTF-8'); ?>">
                  <td class="id"><?= (int)$id ?></td>
                  <td><?= $name ?></td>
                  <td class="email"><a href="mailto:<?= $email ?>"><?= $email ?></a></td>
                  <td><span class="badge <?= $roleClass ?>"><?= $role ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    // ค้นหาแบบ real-time: ชื่อหรืออีเมล
    const q = document.getElementById('q');
    const rows = Array.from(document.querySelectorAll('#tbl tbody tr'));
    if (q && rows.length){
      q.addEventListener('input', () => {
        const term = (q.value || '').trim().toLowerCase();
        rows.forEach(tr => {
          const name = (tr.getAttribute('data-name') || '');
          const email = (tr.getAttribute('data-email') || '');
          tr.style.display = (!term || name.includes(term) || email.includes(term)) ? '' : 'none';
        });
      });
    }
  </script>
</body>
</html>
