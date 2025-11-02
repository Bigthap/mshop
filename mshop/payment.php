<?php
// cart_add.php  (Payment Mockup)
declare(strict_types=1);

// ---- โหลด "ฐานข้อมูล" ----
$products = require __DIR__ . '/data/products.php';
$toppings = require __DIR__ . '/data/toppings.php';
$optionsDb = require __DIR__ . '/data/options.php';

// ---- helper ----
function format_price(float $v): string { return '฿' . number_format($v, 2); }
function to_int($v, int $default = 0): int {
  if ($v === null) return $default;
  if (is_array($v) || is_object($v)) return $default;
  $v = filter_var($v, FILTER_SANITIZE_NUMBER_INT);
  return is_numeric($v) ? (int)$v : $default;
}
function clamp(int $v, int $min, int $max): int {
  return max($min, min($max, $v));
}

// ---- โหมด 1: แสดงสรุปคำสั่งซื้อ + แบบฟอร์มชำระเงิน (รับ POST จาก product.php) ----
// ---- โหมด 2: หลังยืนยันชำระเงิน (POST อีกครั้งพร้อม confirm=1) ----
$isConfirm = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === '1');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(400);
  echo 'วิธีใช้: ส่งคำสั่งซื้อด้วย POST มาจาก product.php';
  exit;
}

// ---- รับค่าโพสต์ครั้งแรก ----
$productId = to_int($_POST['product_id'] ?? null, 0);
$qty       = clamp(to_int($_POST['qty'] ?? 1, 1), 1, 999);

// ตั้งค่าตัวเลือกเริ่มต้น (เผื่อกรณีไม่มีส่งมา)
$sizeKey   = $_POST['opt_size']   ?? 'default';
$noodleKey = $_POST['opt_noodle'] ?? 'normal';

// ป้องกันคีย์แปลก ๆ
if (!isset($optionsDb['size'][$sizeKey]))   $sizeKey = 'default';
if (!isset($optionsDb['noodle'][$noodleKey])) $noodleKey = 'normal';

// สินค้า
$product = $products[$productId] ?? null;
if (!$product) {
  http_response_code(404);
  echo 'ไม่พบสินค้า';
  exit;
}

// สต็อกชาม
$stockBowls = (int)$product['stock'];
if ($stockBowls <= 0) {
  http_response_code(409);
  echo 'สินค้าหมดสต็อก';
  exit;
}
$qty = clamp($qty, 1, $stockBowls);

// ท็อปปิ้งที่ส่งมาเป็น array qty
$postedTop = $_POST['topping_qty'] ?? [];
if (!is_array($postedTop)) $postedTop = [];

// คัดเฉพาะท็อปปิ้งที่ “อนุญาต” สำหรับชามนี้
$allowedToppingIds = $product['allowed_topping_ids'] ?? [];
$toppingLines = []; // เก็บสรุปต่อชาม: [id,name,price,qty_per_bowl,max_per_bowl,stock_total]
foreach ($allowedToppingIds as $tid) {
  if (!isset($toppings[$tid])) continue;
  $meta = $toppings[$tid];
  $perBowlMax = (int)$meta['max_per_bowl'];
  $stockTotal = (int)$meta['stock'];
  $price      = (float)$meta['price'];

  // ตัวเลขจำนวนต่อชามที่เลือก
  $postedQtyPerBowl = to_int($postedTop[$tid] ?? 0, 0);
  // จำกัด 0..max_per_bowl
  $postedQtyPerBowl = clamp($postedQtyPerBowl, 0, $perBowlMax);

  // ตรวจสต็อกรวม: จำนวนรวม = qty_bowls * qty_per_bowl
  $needTotal = $qty * $postedQtyPerBowl;
  if ($needTotal > $stockTotal) {
    // ลดลงให้ไม่เกินสต็อก
    $postedQtyPerBowl = (int) floor($stockTotal / max(1,$qty));
  }

  $toppingLines[] = [
    'id' => $tid,
    'name' => $meta['name'],
    'price' => $price,
    'qty_per_bowl' => $postedQtyPerBowl,
    'max_per_bowl' => $perBowlMax,
    'stock_total' => $stockTotal
  ];
}

// ---- คำนวณราคา ----
$basePrice = (float)$product['base_price'];
$sizeAdd   = (float)($optionsDb['size'][$sizeKey]['add'] ?? 0.0);

// ยอดต่อ “ชามเดียว” (base + size + toppings-ตาม qty_per_bowl)
$topSumPerBowl = 0.0;
foreach ($toppingLines as $line) {
  $topSumPerBowl += $line['price'] * $line['qty_per_bowl'];
}
$unitSubtotal = $basePrice + $sizeAdd + $topSumPerBowl;

// รวมทั้งออเดอร์ (จำนวนชาม)
$orderSubtotal = $unitSubtotal * $qty;

// mockup: ยังไม่คิดค่าจัดส่ง/ภาษี
$grandTotal = $orderSubtotal;

// ---- UI ----
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo $isConfirm ? 'ชำระเงินสำเร็จ' : 'ชำระเงิน (Payment)'; ?></title>
  <style>
    :root{
      --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --accent:#22d3ee;
      --ok:#34d399; --danger:#f87171; --shadow:0 12px 30px rgba(0,0,0,.35); --radius:16px
    }
    *{box-sizing:border-box}
    body{margin:0;background:linear-gradient(135deg,#0b1020,#131a2e 60%,#0b1020);font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;color:var(--text);min-height:100dvh;display:flex;align-items:center;justify-content:center;padding:24px}
    .page{width:min(1000px,100%);display:grid;grid-template-columns:1.2fr 1fr;gap:24px}
    .card{background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.08);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px}
    h1{margin:6px 0 10px;font-size:clamp(22px,3vw,32px)}
    h2{margin:8px 0 10px;font-size:clamp(18px,2.2vw,22px);color:#cfe8ff}
    .muted{color:var(--muted)}
    .row{display:flex;justify-content:space-between;align-items:center;margin:6px 0}
    .line{border-top:1px dashed rgba(255,255,255,.15);margin:10px 0}
    .price{font-weight:700}
    .tag{display:inline-block;padding:4px 8px;border-radius:999px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);font-size:12px;margin-right:6px}
    .btn{display:inline-block;padding:12px 16px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:linear-gradient(180deg,#0ea5b7,#0891b2);color:#fff;text-decoration:none;font-weight:600}
    .btn:hover{transform:translateY(-2px);filter:brightness(1.05);background:linear-gradient(180deg,#10b2c8,#0e93b7)}
    .ghost{background:transparent;border:1px dashed rgba(255,255,255,.2);color:var(--text)}
    .btnrow{display:flex;gap:10px;flex-wrap:wrap}
    .list{margin:6px 0 0;padding:0;list-style:none}
    .list li{display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)}
    .success{color:#02220f;background:linear-gradient(180deg,#34d399,#10b981);padding:10px 12px;border-radius:10px;display:inline-block}
    .payopt{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px}
    .payopt label{display:flex;gap:8px;align-items:center;padding:10px;border:1px solid rgba(255,255,255,.16);border-radius:12px;background:rgba(255,255,255,.03);cursor:pointer}
    .cap{font-size:12px;color:var(--muted)}
    @media (max-width:900px){ .page{grid-template-columns:1fr} }
  </style>
</head>
<body>
<?php if (!$isConfirm): ?>
  <!-- ขั้นตอนเลือกชำระเงิน -->
  <div class="page">
    <!-- ซ้าย: สรุปออเดอร์ -->
    <div class="card">
      <h1>ชำระเงิน (Payment)</h1>
      <div class="muted">ตรวจสอบรายละเอียดคำสั่งซื้อก่อนชำระเงิน</div>

      <div class="line"></div>

      <h2>สินค้า</h2>
      <div class="row"><div><?php echo $product['name']; ?></div><div class="price"><?php echo format_price($basePrice); ?> / ชาม</div></div>
      <div class="row"><div>ขนาดชาม</div><div><?php echo $optionsDb['size'][$sizeKey]['label']; ?> <?php echo $sizeAdd>0?'(+'.format_price($sizeAdd).')':''; ?></div></div>
      <div class="row"><div>เส้น</div><div><?php echo $optionsDb['noodle'][$noodleKey]['label']; ?></div></div>

      <div class="line"></div>

      <h2>ท็อปปิ้งต่อชาม</h2>
      <ul class="list">
        <?php if (empty($toppingLines)): ?>
          <li><div>—</div><div>ไม่มี</div></li>
        <?php else: ?>
          <?php foreach ($toppingLines as $line): ?>
            <li>
              <div>
                <?php echo $line['name']; ?>
                <span class="tag">สูงสุด/ชาม: <?php echo (int)$line['max_per_bowl']; ?></span>
                <span class="tag">คลังรวม: <?php echo (int)$line['stock_total']; ?></span>
                <div class="cap">เลือก: <?php echo (int)$line['qty_per_bowl']; ?> ชิ้น/ชาม</div>
              </div>
              <div>
                <?php echo ($line['price']>0)?format_price((float)$line['price']).' × '.$line['qty_per_bowl']: 'ฟรี'; ?>
              </div>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>

      <div class="line"></div>

      <div class="row"><div>จำนวนชาม</div><div><?php echo (int)$qty; ?></div></div>
      <div class="row"><div>ราคารวมต่อชาม</div><div><?php echo format_price($unitSubtotal); ?></div></div>
      <div class="row" style="font-size:18px"><div><strong>ยอดรวม</strong></div><div class="price"><?php echo format_price($grandTotal); ?></div></div>
    </div>

    <!-- ขวา: ช่องทางชำระ (Mockup) -->
    <div class="card">
      <h2>เลือกช่องทางการชำระเงิน</h2>
      <form method="post" action="payment.php" autocomplete="off" novalidate>
        <div class="payopt">
          <label>
            <input type="radio" name="pay_method" value="qr" checked>
            <span>พร้อมเพย์ / QR</span>
          </label>
          <label>
            <input type="radio" name="pay_method" value="card">
            <span>บัตรเครดิต/เดบิต</span>
          </label>
          <label>
            <input type="radio" name="pay_method" value="cod">
            <span>ชำระปลายทาง (COD)</span>
          </label>
          <label>
            <input type="radio" name="pay_method" value="transfer">
            <span>โอนผ่านธนาคาร</span>
          </label>
        </div>

        <div class="line"></div>
        <div class="cap">เดโมเท่านั้น: ปุ่ม “ชำระเงิน” เพื่อชำระเงิน</div>

        <!-- ส่งข้อมูลออเดอร์กลับมาอีกครั้งเพื่อแสดงผลลัพธ์สำเร็จ -->
        <input type="hidden" name="confirm" value="1">
        <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
        <input type="hidden" name="qty" value="<?php echo (int)$qty; ?>">
        <input type="hidden" name="opt_size" value="<?php echo htmlspecialchars($sizeKey, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="opt_noodle" value="<?php echo htmlspecialchars($noodleKey, ENT_QUOTES, 'UTF-8'); ?>">
        <?php foreach ($toppingLines as $line): ?>
          <input type="hidden" name="topping_qty[<?php echo (int)$line['id']; ?>]" value="<?php echo (int)$line['qty_per_bowl']; ?>">
        <?php endforeach; ?>

        <div class="row" style="margin-top:14px;justify-content:flex-end">
          <a class="btn ghost" href="../mshop/main.php">← กลับหน้าแรก</a>
          <button class="btn" type="submit">ชำระเงิน</button>
        </div>
      </form>
    </div>
  </div>

<?php else: ?>
  <!-- หน้าสำเร็จหลังกดชำระเงิน (Mock) -->
  <div class="card" style="width:min(680px,100%)">
    <h1>ชำระเงินสำเร็จ</h1>
    <p><span class="success">การชำระเงินของคุณได้รับการยืนยัน</span></p>

    <div class="line"></div>

    <h2>สรุปออเดอร์</h2>
    <div class="row"><div>สินค้า</div><div><?php echo $products[to_int($_POST['product_id'])]['name'] ?? '—'; ?></div></div>
    <div class="row"><div>จำนวนชาม</div><div><?php echo to_int($_POST['qty'], 1); ?></div></div>
    <div class="row"><div>ช่องทางที่เลือก</div><div>
      <?php
        $pm = $_POST['pay_method'] ?? 'qr';
        $pmName = ['qr'=>'พร้อมเพย์/QR','card'=>'บัตรเครดิต/เดบิต','cod'=>'ปลายทาง (COD)','transfer'=>'โอนธนาคาร'][$pm] ?? 'พร้อมเพย์/QR';
        echo $pmName;
      ?>
    </div></div>

    <div class="line"></div>

    <div class="btnrow">
      <a class="btn" href="../mshop/main.php">← กลับหน้าแรก</a>
      <a class="btn ghost" href="../mshop/login.php">ออกจากระบบ</a>
    </div>

  </div>
<?php endif; ?>
</body>
</html>
