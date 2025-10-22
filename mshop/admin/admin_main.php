<?php
// admin/index.php
declare(strict_types=1);

// โหลดข้อมูลสินค้าใหม่ทุกครั้งที่รีเฟรช
$products = require __DIR__ . '/../data/products.php';

// helper ราคาบาท (ทศนิยม 2 ตำแหน่ง)
function thb($n): string {
  return number_format((float)$n, 2);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#0f172a; --panel:#111827; --panel2:#0b1220; --text:#e7e7ea; --muted:#a1a1aa;
      --accent:#22c55e; --ring:rgba(255,255,255,.10); --radius:16px;
      --cyan:#22d3ee; --red:#f87171;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter,system-ui,Segoe UI,Roboto,Arial;background:var(--bg);color:var(--text)}
    .container{width:min(1200px,95%);margin-inline:auto}
    nav{position:sticky;top:16px;z-index:10}
    .nav-wrap{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:12px;background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.02));border:1px solid var(--ring);backdrop-filter:saturate(140%) blur(6px)}
    .brand{display:flex;gap:10px;align-items:center;text-decoration:none;color:#fff}
    .brand .logo{width:28px;height:28px;border-radius:8px;background:conic-gradient(from 220deg,#2563eb,#7c3aed)}
    .nav-links{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .nav-links a{color:#fff;text-decoration:none;font-size:14px;padding:8px 12px;border-radius:10px;border:1px solid transparent}
    .nav-links a:hover{background:#202025;border-color:#2c2c30}
    .page{margin-top:24px;display:grid;gap:16px;grid-template-columns:1fr}
    .panel{padding:18px;border-radius:20px;border:1px solid var(--ring);background:var(--panel)}
    .panel h1{margin:0 0 10px}
    .toolbar{display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:space-between}
    .left, .right{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .input, select{background:var(--panel2);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:10px;padding:10px 12px}
    .btn{display:inline-flex;justify-content:center;align-items:center;gap:8px;padding:10px 14px;font-size:14px;font-weight:600;color:#fff;text-decoration:none;border-radius:10px;border:1px solid #16a34a;background:#22c55e;cursor:pointer}
    .btn:hover{background:#114f28;transition: background-color .2s ease;transform: translateY(-1px)}
    .btn.ghost{background:transparent;border:1px dashed rgba(255,255,255,.25)}
    .btn.cyan{border-color:#0ea5b7;background:#22d3ee;color:#05202a}
    .btn.red{border-color:#b91c1c;background:#ef4444}
    .grid{display:grid;gap:16px;grid-template-columns:repeat(2,minmax(0,1fr))}
    @media(min-width:768px){.grid{grid-template-columns:repeat(3,1fr)}}
    @media(min-width:1024px){.grid{grid-template-columns:repeat(4,1fr)}}
    .card{display:flex;flex-direction:column;gap:10px;padding:12px;border-radius:14px;background:rgba(40,40,40,.30);border:1px solid rgba(255,255,255,.10)}
    .thumb{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:10px;background:#111}
    .name{margin:2px 6px 0;font-size:16px;color:#f4f4f5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .meta{display:flex;gap:12px;align-items:center;padding:0 4px;justify-content:space-between}
    .price{color:var(--cyan);font-weight:800}
    .stock{font-size:12px;color:#9ca3af}
    .stock.ok{color:#86efac}
    .rowbtn{display:flex;gap:8px;flex-wrap:wrap}
    .muted{color:var(--muted)}
    footer{margin:32px 0 56px;text-align:center;color:#9da3b0}
    .caps{font-size:12px;color:var(--muted)}
  </style>
</head>
<body>
  <div class="container" style="padding-top:24px">
    <nav>
      <div class="nav-wrap">
        <a class="brand" href="#"><span class="logo"></span><span>Admin Dashboard</span></a>
        <div class="nav-links">
            <a href="../admin/admin_main.php">แอดมิน</a>
            <a href="edit_toppings.php">ท็อปปิ้ง</a>
            <a href="admin_users.php">ผู้ใช้</a>
            <a href="../main.php">ร้านค้าหลัก</a>
            <a href="../mshop/logout.php">Logout</a>
        </div>
      </div>
    </nav>

    <section class="page">
      <!-- แผงควบคุมบนสุด -->
      <div class="panel">
        <h1>หน้าหลักผู้ดูแลระบบ</h1>
        <div class="toolbar">


          <!-- Quick jump ไปหน้า edit.php?id= -->
          <form class="right" method="get" action="edit.php" onsubmit="return goEditBySelect()">
            <select id="jumpId" name="id">
              <option value="">เลือกสินค้าเพื่อแก้ไข…</option>
              <?php foreach ($products as $id => $p): 
                $name = htmlspecialchars($p['name'] ?? '', ENT_QUOTES, 'UTF-8');
              ?>
                <option value="<?php echo (int)$id; ?>">
                  #<?php echo (int)$id; ?> — <?php echo $name; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">แก้ไข</button>
            <a class="btn ghost" href="../main.php">ไปยังหน้าหลัก</a>
          </form>
        </div>
      </div>

      <!-- รายการสินค้า -->
      <div class="panel">
        <h2 style="margin:0 0 12px">สินค้า</h2>
        <?php if (empty($products)): ?>
          <p class="muted">ยังไม่มีสินค้า</p>
        <?php else: ?>
          <div class="grid" id="gridProducts" role="list">
            <?php foreach ($products as $id => $p):
              $name  = htmlspecialchars($p['name'] ?? '', ENT_QUOTES, 'UTF-8');
              $img   = htmlspecialchars($p['image'] ?? '', ENT_QUOTES, 'UTF-8');
              $price = isset($p['base_price']) ? (float)$p['base_price'] : 0.0;
              $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
              $soldOut = $stock <= 0;
            ?>
              <article class="card" role="listitem" data-name="<?php echo mb_strtolower($name, 'UTF-8'); ?>">
                <img class="thumb" src="<?php echo $img; ?>" alt="<?php echo $name; ?>"
                     onerror="this.onerror=null;this.src='https://via.placeholder.com/600x600?text=No+Image';">
                <div class="name" title="<?php echo $name; ?>"><?php echo $name; ?></div>
                <div class="meta">
                  <div class="price"><?php echo thb($price); ?> ฿</div>
                  <div class="stock <?php echo $soldOut ? '' : 'ok'; ?>">
                    คงเหลือ <?php echo number_format($stock); ?>
                  </div>
                </div>
                <div class="rowbtn">
                  <a class="btn cyan" href="edit.php?id=<?php echo (int)$id; ?>">แก้ไขสินค้า</a>
                  <a class="btn ghost" href="../products/buy.php?id=<?php echo (int)$id; ?>" target="_blank" rel="noopener">ดูหน้า Product</a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <footer>
      Copyright © <?php echo date('Y'); ?> — Admin
    </footer>
  </div>

</body>
</html>
