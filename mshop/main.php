<?php
// โหลดข้อมูลสินค้าใหม่ทุกครั้งที่รีเฟรช
$products = require __DIR__ . '../data/products.php';

// helper เล็กๆ สำหรับฟอร์แมตราคา (บาท.ทศนิยม 2 ตำแหน่ง)
function thb($n) {
  // ถ้าข้อมูลจริงเป็น "สตางค์" (เช่น 25006 = 250.06 บาท) ให้เปลี่ยนเป็น: $n = $n / 100;
  return number_format((float)$n, 2);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>AuanCheng</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#0f172a; --panel:#1a1a1d; --text:#e7e7ea; --muted:#a1a1aa;
      --accent:#22c55e; --ring:rgba(255,255,255,.10); --radius:16px;
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
    .page-card{margin-top:24px;padding:28px;border-radius:20px;border:1px solid var(--ring);background: #111827;}
    .grid{display:grid;gap:16px;grid-template-columns:repeat(2,minmax(0,1fr))}
    @media(min-width:768px){.grid{grid-template-columns:repeat(3,1fr)}}
    @media(min-width:1024px){.grid{grid-template-columns:repeat(4,1fr)}}
    .card{display:flex;flex-direction:column;gap:10px;padding:12px;border-radius:14px;background:rgba(40,40,40,.30);border:1px solid rgba(255,255,255,.10)}
    .thumb{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:10px;background:#111}
    .card h3{margin:2px 6px 0;font-size:18px;color:#f4f4f5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .meta{display:flex;gap:12px;align-items:center;padding:0 4px}
    .price{color:var(--accent);font-weight:800;font-size:20px}
    .stock{color:#9ca3af;font-size:12px}
    .btn{display:inline-flex;justify-content:center;align-items:center;gap:8px;padding:10px 14px;font-size:14px;font-weight:600;color:#fff;text-decoration:none;border-radius:10px;border:1px solid #16a34a;background:#22c55e}
    .btn:hover{background:#114f28;transition: background-color 0.3s ease;transform: scale(1.09);}
    .btn[aria-disabled="true"]{background:#374151;border-color:#374151;cursor:not-allowed;filter:grayscale(.1)}
    footer{margin-top:56px;padding:56px 0;border-top:1px solid #17171b;color:#9da3b0}
  </style>
</head>
<body>
  <div class="container" style="padding-top:24px">
    <nav>
      <div class="nav-wrap">
        <a class="brand" href="#"><span class="logo"></span><span>AuanCheng</span></a>
        <div class="nav-links">
          <a href="Support.php">ติดต่อ</a><a href="Credit.php">เครดิต</a><a href="/mshop/login.php">Logout</a>
        </div>
      </div>
    </nav>

    <main class="page-card" >
      <h1 style="margin:0 0 16px">สินค้า</h1>
      <div class="grid" role="list" >
        <?php foreach ($products as $id => $p): 
          // ป้องกัน XSS จากข้อมูลภายนอก
          $name  = htmlspecialchars($p['name'] ?? '', ENT_QUOTES, 'UTF-8');
          $img   = htmlspecialchars($p['image'] ?? '', ENT_QUOTES, 'UTF-8');
          $price = isset($p['base_price']) ? (float)$p['base_price'] : 0.0;
          $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
          $soldOut = $stock <= 0;
        ?>
        <article class="card" role="listitem">
          <img class="thumb" src="<?= $img ?>" alt="<?= $name ?>">
          <h3><?= $name ?></h3>
          <div class="meta">
            <div class="price"><?= thb($price) ?> ฿</div>
            <div class="stock"<?= $soldOut ? '' : ' style="color:#86efac"' ?>>คงเหลือ <?= number_format($stock) ?></div>
          </div>
          <?php if ($soldOut): ?>
            <a class="btn" aria-disabled="true" href="#" onclick="return false;">สินค้าหมด</a>
          <?php else: ?>
            <a class="btn" href="products/buy.php?id=<?= urlencode((string)$id) ?>">ซื้อเลย</a>
          <?php endif; ?>
        </article>
        <?php endforeach; ?>
      </div>
    </main>

    <footer>
      <div style="text-align:center;">Copyright © 2025 AuanCheng — All rights reserved</div>
    </footer>
  </div>
</body>
</html>
