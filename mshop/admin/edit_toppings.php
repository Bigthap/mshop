<?php
// admin/edit_toppings.php (no session, no search/filter)
declare(strict_types=1);

// ------ โหลด DB ------
$toppingsFile = __DIR__ . '/../data/toppings.php';
/** @var array<int, array> $TOPPINGS */
$TOPPINGS = require $toppingsFile;

// ------ Helper ------
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function to_int($v, int $def=0): int {
  if (is_array($v) || is_object($v)) return $def;
  $v = filter_var($v, FILTER_SANITIZE_NUMBER_INT);
  return is_numeric($v) ? (int)$v : $def;
}
function to_float($v, float $def=0.0): float {
  if (is_array($v) || is_object($v)) return $def;
  $v = str_replace([',',' '], '', (string)$v);
  return is_numeric($v) ? (float)$v : $def;
}
function save_php_array(string $path, array $data): bool {
  $code = "<?php\nreturn " . var_export($data, true) . ";\n";
  return @file_put_contents($path, $code, LOCK_EX) !== false;
}

// ------ Handle POST actions (no CSRF/session) ------
$flash = null; $flashType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $act = $_POST['_act'] ?? '';

  if ($act === 'update_one') {
    $id = to_int($_POST['id'] ?? null, 0);
    if ($id && isset($TOPPINGS[$id])) {
      $name  = trim((string)($_POST['name'] ?? ''));
      $price = max(0.0, to_float($_POST['price'] ?? 0));
      $stock = max(0, to_int($_POST['stock'] ?? 0));
      $type  = in_array($_POST['type'] ?? '', ['protein','basic','special'], true) ? $_POST['type'] : ($TOPPINGS[$id]['type'] ?? 'basic');
      $maxpb = max(0, to_int($_POST['max_per_bowl'] ?? 0));

      if ($name !== '') $TOPPINGS[$id]['name'] = $name;
      $TOPPINGS[$id]['price'] = $price;
      $TOPPINGS[$id]['stock'] = $stock;
      $TOPPINGS[$id]['type']  = $type;
      $TOPPINGS[$id]['max_per_bowl'] = $maxpb;

      if (save_php_array($toppingsFile, $TOPPINGS)) {
        $flash = "บันทึกท็อปปิ้ง #{$id} เรียบร้อย";
      } else {
        $flash = "ไม่สามารถเขียนไฟล์ได้: " . basename($toppingsFile); $flashType='error';
      }
    } else {
      $flash = "ไม่พบไอเท็มที่แก้ไข"; $flashType='error';
    }
  }

  if ($act === 'bulk_restock') {
    $scope = $_POST['scope'] ?? 'all'; // all|protein|basic|special
    $to    = max(0, to_int($_POST['stock_to'] ?? 0));
    $count = 0;
    foreach ($TOPPINGS as $id => &$t) {
      if ($scope === 'all' || ($t['type'] ?? '') === $scope) {
        $t['stock'] = $to;
        $count++;
      }
    }
    unset($t);
    if (save_php_array($toppingsFile, $TOPPINGS)) {
      $flash = "ปรับสต็อก $count รายการ เป็น $to สำเร็จ";
    } else {
      $flash = "เขียนไฟล์ไม่สำเร็จ: " . basename($toppingsFile); $flashType='error';
    }
  }

  // โหลดใหม่หลังบันทึก
  $TOPPINGS = require $toppingsFile;
}

// ------ เตรียมข้อมูลโชว์ ------
ksort($TOPPINGS, SORT_NUMERIC);
$byType = ['protein'=>0,'basic'=>0,'special'=>0];
$zeroStock = 0;
foreach ($TOPPINGS as $tp) {
  $tt = $tp['type'] ?? 'basic';
  if (isset($byType[$tt])) $byType[$tt]++;
  if ((int)($tp['stock'] ?? 0) <= 0) $zeroStock++;
}
$total = count($TOPPINGS);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>MShop • Edit Toppings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --bg:#0f172a; --panel:#111827; --panel2:#0b1220; --text:#e5e7eb; --muted:#94a3b8;
      --ring:rgba(255,255,255,.1); --radius:16px; --accent:#22d3ee; --ok:#34d399; --warn:#f59e0b; --danger:#ef4444;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;color:var(--text);background:linear-gradient(135deg,#0b1020,#131a2e 60%,#0b1020)}
    .container{width:min(1100px,95%);margin:24px auto}
    .nav{position:sticky;top:16px;z-index:10;display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 16px;border-radius:14px;background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid var(--ring);backdrop-filter:saturate(140%) blur(6px)}
    .brand{display:flex;gap:10px;align-items:center;text-decoration:none;color:#fff}
    .logo{width:28px;height:28px;border-radius:8px;background:conic-gradient(from 220deg,#2563eb,#7c3aed)}
    .links{display:flex;gap:8px;flex-wrap:wrap}
    .links a{color:#fff;text-decoration:none;font-size:14px;padding:8px 12px;border-radius:10px;border:1px solid transparent}
    .links a:hover{background:#202025;border-color:#2c2c30}

    .panel{margin-top:18px;padding:18px;border-radius:20px;border:1px solid var(--ring);background:var(--panel)}
    h1,h2{margin:0 0 10px}
    .muted{color:var(--muted)}

    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:10px}
    .stat{padding:12px 14px;border-radius:12px;background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.02));border:1px solid var(--ring)}
    .stat .n{font-size:20px;font-weight:800}
    .stat .k{font-size:12px;color:var(--muted)}

    .toolbar{display:flex;gap:12px;align-items:center;justify-content:flex-end;margin-top:12px;flex-wrap:wrap}
    .row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .input, select{background:var(--panel2);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:10px;padding:10px 12px}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:10px;color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.18);background:linear-gradient(180deg,#0ea5b7,#0891b2);cursor:pointer}
    .btn:hover{filter:brightness(1.05)}
    .btn.warn{background:linear-gradient(180deg,#f59e0b,#b45309)}
    .flash{margin-top:12px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.16)}
    .flash.ok{background:linear-gradient(180deg,#86efac,#22c55e);color:#041a0f}
    .flash.error{background:linear-gradient(180deg,#fecaca,#f87171);color:#2b0a0a}

    .table-wrap{margin-top:12px;border:1px solid var(--ring);border-radius:14px;overflow:auto;background:linear-gradient(180deg,rgba(255,255,255,.02),rgba(255,255,255,.01))}
    table{width:100%;border-collapse:separate;border-spacing:0}
    thead th{position:sticky;top:0;background:#0f172a;color:#cfe8ff;text-align:left;font-weight:600;font-size:14px;border-bottom:1px solid var(--ring);padding:10px 12px}
    tbody td{padding:8px 10px;border-bottom:1px solid rgba(255,255,255,.06);font-size:14px;vertical-align:middle}
    tbody tr:hover{background:rgba(255,255,255,.04)}
    .id{font-variant-numeric:tabular-nums}
    .badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06)}
    .badge.zero{background:linear-gradient(180deg,#fca5a5,#ef4444);color:#290606}
    .cap{font-size:12px;color:var(--muted)}
    @media (max-width:860px){ .stats{grid-template-columns:repeat(2,1fr)} thead th:nth-child(6),tbody td:nth-child(6){display:none} }
    @media (max-width:640px){ thead th:nth-child(3),tbody td:nth-child(3){display:none} }
  </style>
</head>
<body>
  <div class="container">
    <div class="nav">
      <a class="brand" href="#"><span class="logo"></span><span>Admin • Edit Toppings</span></a>
      <div class="links">
        <a href="../admin/admin_main.php">แอดมิน</a>
        <a href="../admin/edit_toppings.php" aria-current="page">ท็อปปิ้ง</a>
        <a href="../admin/admin_users.php">ผู้ใช้</a>
        <a href="../main.php">ร้านค้าหลัก</a>
        <a href="../logout.php">Logout</a>
      </div>
    </div>

    <div class="panel">
      <h1>จัดการท็อปปิ้ง</h1>
      <div class="muted">แก้ไขชื่อ ราคา สต็อก ประเภท และจำนวนสูงสุดต่อชาม</div>

      <div class="stats">
        <div class="stat"><div class="n"><?= number_format($total) ?></div><div class="k">ทั้งหมด</div></div>
        <div class="stat"><div class="n"><?= number_format($byType['protein']) ?></div><div class="k">โปรตีน</div></div>
        <div class="stat"><div class="n"><?= number_format($byType['basic']) ?></div><div class="k">พื้นฐาน</div></div>
        <div class="stat"><div class="n"><?= number_format($byType['special']) ?> <span class="k">(หมดสต็อก <?= number_format($zeroStock) ?>)</span></div><div class="k">พิเศษ</div></div>
      </div>

      <?php if ($flash): ?>
        <div class="flash <?= $flashType === 'error' ? 'error' : 'ok' ?>"><?= h($flash) ?></div>
      <?php endif; ?>

      <!-- Bulk Restock -->
      <div class="toolbar">
        <form class="row" method="post" action="./edit_toppings.php" onsubmit="return confirm('ตั้งค่าสต็อกจำนวนนี้ให้ช่วงที่เลือก ใช่หรือไม่?')">
          <input type="hidden" name="_act" value="bulk_restock">
          <select name="scope" class="input">
            <option value="all">ทั้งหมด</option>
            <option value="protein">เฉพาะโปรตีน</option>
            <option value="basic">เฉพาะพื้นฐาน</option>
            <option value="special">เฉพาะพิเศษ</option>
          </select>
          <input type="number" class="input" name="stock_to" min="0" step="1" value="100" style="width:120px">
          <button class="btn warn" type="submit">ตั้งค่าสต็อกจำนวนนี้</button>
        </form>
      </div>

      <div class="table-wrap" style="margin-top:12px">
        <table>
          <thead>
            <tr>
              <th style="min-width:80px">ID</th>
              <th style="min-width:220px">ชื่อ</th>
              <th style="min-width:120px">ราคา (฿)</th>
              <th style="min-width:110px">สต็อก</th>
              <th style="min-width:140px">ประเภท</th>
              <th style="min-width:150px">สูงสุด/ชาม</th>
              <th style="min-width:160px">บันทึก</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($TOPPINGS as $id => $t):
              $name  = $t['name'] ?? '';
              $price = (float)($t['price'] ?? 0);
              $stock = (int)($t['stock'] ?? 0);
              $type  = (string)($t['type'] ?? 'basic');
              $maxpb = (int)($t['max_per_bowl'] ?? 0);
              $isZero = $stock <= 0;
            ?>
            <tr>
              <td class="id">#<?= (int)$id ?></td>
              <td>
                <form method="post" action="./edit_toppings.php" class="row" style="gap:6px;flex-wrap:nowrap">
                  <input type="hidden" name="_act" value="update_one">
                  <input type="hidden" name="id" value="<?= (int)$id ?>">
                  <input class="input" type="text" name="name" value="<?= h($name) ?>" style="width:100%">
              </td>
              <td><input class="input" type="number" step="0.01" min="0" name="price" value="<?= number_format($price,2,'.','') ?>" style="width:120px"></td>
              <td>
                <div class="row" style="gap:6px">
                  <input class="input" type="number" step="1" min="0" name="stock" value="<?= (int)$stock ?>" style="width:110px">
                  <?php if ($isZero): ?><span class="badge zero">หมด</span><?php endif; ?>
                </div>
              </td>
              <td>
                <select class="input" name="type" style="min-width:130px">
                  <option value="protein" <?= $type==='protein'?'selected':'' ?>>โปรตีน</option>
                  <option value="basic"   <?= $type==='basic'  ?'selected':'' ?>>พื้นฐาน</option>
                  <option value="special" <?= $type==='special'?'selected':'' ?>>พิเศษ</option>
                </select>
              </td>
              <td><input class="input" type="number" step="1" min="0" name="max_per_bowl" value="<?= (int)$maxpb ?>" style="width:120px"></td>
              <td>
                  <button class="btn" type="submit">บันทึก</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div class="cap" style="padding:10px">* การบันทึกจะเขียนทับไฟล์ <code><?= h(basename($toppingsFile)) ?></code>. หากไม่สำเร็จให้ตรวจสิทธิ์การเขียนไฟล์/โฟลเดอร์</div>
      </div>
    </div>
  </div>
</body>
</html>
