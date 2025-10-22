<?php
// product.php
declare(strict_types=1);

// ---- โหลด "ฐานข้อมูล" ----
$products = require __DIR__ . '/../data/products.php';
$toppings = require __DIR__ . '/../data/toppings.php';
$optionsDb = require __DIR__ . '/../data/options.php';

// ---- รับ id จาก query string (fallback = 1) ----
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
// ---- หา product ----
$product = $products[$id] ?? null;

// ---- helper แปลงราคา ----
function format_price(float $v): string { return '฿' . number_format($v, 2); }

// ---- prev/next id สำหรับปุ่มนำทาง ----
$ids = array_keys($products);
sort($ids);
$currentIndex = array_search($id, $ids, true);
$prevId = ($product && $currentIndex !== false && $currentIndex > 0) ? $ids[$currentIndex - 1] : null;
$nextId = ($product && $currentIndex !== false && $currentIndex < count($ids) - 1) ? $ids[$currentIndex + 1] : null;

// ---- ค่าตั้งต้นของ options ในฟอร์ม ----
$defaultSizeKey   = 'default'; // ตาม options.php
$defaultNoodleKey = 'normal';

// ---- คำนวณราคา "เริ่มต้น" ที่โชว์ (base + size default + topping default ที่คิดเงินเพิ่มเท่านั้น) ----
$base = $product ? (float)$product['base_price'] : 0.0;
$sizeAdd = isset($optionsDb['size'][$defaultSizeKey]['add']) ? (float)$optionsDb['size'][$defaultSizeKey]['add'] : 0.0;

// default toppings ที่คิดเงินเพิ่ม (price > 0) เท่านั้น
$defaultTopSum = 0.0;
if ($product && !empty($product['default_topping_ids'])) {
  foreach ($product['default_topping_ids'] as $tid) {
    if (isset($toppings[$tid]) && (float)$toppings[$tid]['price'] > 0) {
      $defaultTopSum += (float)$toppings[$tid]['price'];
    }
  }
}
$displayPrice = $base + $sizeAdd + $defaultTopSum;
?>
<!doctype html>
<html lang="th" itemscope itemtype="https://schema.org/Product">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo $product ? $product['name'] . ' | รายละเอียดสินค้า' : 'ไม่พบสินค้า'; ?></title>
  <style>
    :root{
      --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --accent:#22d3ee;
      --danger:#f87171; --ok:#34d399; --btn:#0891b2; --btnh:#0e7490; --shadow:0 12px 30px rgba(0,0,0,.35); --radius:16px
    }
    *{box-sizing:border-box}
    body{margin:0;background:linear-gradient(135deg,#0b1020,#131a2e 60%,#0b1020);font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;color:var(--text);min-height:100dvh;display:flex;align-items:center;justify-content:center;padding:24px}
    .container{width:min(1100px,100%);display:grid;grid-template-columns:1.2fr 1fr;gap:28px}
    .card{background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.08);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
    .media{position:relative;aspect-ratio:1/1;background:#0b1220}
    .media img{width:100%;height:100%;object-fit:cover;display:block}
    .badge{position:absolute;top:14px;left:14px;padding:6px 10px;border-radius:999px;font-size:12px;letter-spacing:.3px;background:rgba(0,0,0,.55);border:1px solid rgba(255,255,255,.12);backdrop-filter:blur(6px)}
    .stock-ok{color:#081a0f;background:linear-gradient(180deg,#34d399,#10b981)}
    .stock-zero{color:#2b0a0a;background:linear-gradient(180deg,#fca5a5,#f87171)}
    .content{padding:22px}
    h1{margin:0 0 8px;font-size:clamp(22px,3vw,34px);line-height:1.2}
    .price{font-size:clamp(20px,2.5vw,28px);color:var(--accent);font-weight:700}
    .muted{color:var(--muted);font-size:14px}
    .row{display:flex;align-items:center;gap:12px;margin:12px 0;flex-wrap:wrap}
    .qty{display:flex;align-items:center;gap:8px}
    input[type=number]{width:90px;padding:10px 12px;border-radius:10px;background:#0b1220;border:1px solid rgba(255,255,255,.12);color:var(--text)}
    .actions{display:flex;gap:10px;margin-top:14px;flex-wrap:wrap}
    .btn{padding:12px 16px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:linear-gradient(180deg,#0ea5b7,#0891b2);color:#fff;cursor:pointer;font-weight:600;transition:.2s transform ease,.2s filter ease,.2s background ease}
    .btn:hover{transform:translateY(-2px);filter:brightness(1.05);background:linear-gradient(180deg,#10b2c8,#0e93b7)}
    .btn:disabled{opacity:.55;cursor:not-allowed;transform:none}
    .ghost{background:transparent;border:1px dashed rgba(255,255,255,.2);color:var(--text)}
    .crumbs{margin-bottom:10px;font-size:14px}
    .nav{display:flex;gap:8px;margin-top:10px}
    .nav a{font-size:13px;color:var(--accent);text-decoration:none;padding:6px 10px;border-radius:999px;background:rgba(34,211,238,.08);border:1px solid rgba(34,211,238,.15)}
    .notfound{max-width:680px;text-align:center}
    fieldset{border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:12px 14px;margin:12px 0}
    legend{padding:0 6px;font-size:13px;color:var(--muted)}
    .opt-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px}
    .pill{display:flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid rgba(255,255,255,.15);border-radius:999px;background:rgba(255,255,255,.03)}
    .pill input{accent-color:#22d3ee}
    .top-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px}
    .top-item{padding:10px;border:1px solid rgba(255,255,255,.15);border-radius:12px;background:rgba(255,255,255,.03)}
    .top-item small{display:block;color:var(--muted)}
    .cap{font-size:12px;color:var(--muted)}
    @media (max-width:900px){ .container{grid-template-columns:1fr} }
  </style>
</head>
<body>
<?php if (!$product): ?>
  <div class="card notfound" role="alert">
    <div class="content">
      <h1>ไม่พบสินค้า</h1>
      <p class="muted">ตรวจสอบพารามิเตอร์ <code>?id=</code> ใน URL อีกครั้ง หรือกลับไปยังหน้ารายการสินค้า</p>
      <div class="actions">
        <a class="btn ghost" href="index.php">← กลับหน้าแรก</a>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="container" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
    <!-- ซ้าย: รูป -->
    <div class="card">
      <div class="media">
        <img
          src="<?php echo $product['image']; ?>"
          alt="<?php echo $product['name']; ?>"
          itemprop="image"
          onerror="this.onerror=null;this.src='https://previews.123rf.com/images/bananyakosensei/bananyakosensei1806/bananyakosensei180600017/103730897-vector-illustration-error-404-page-not-found-binary-code-green-background-with-message.jpg';"
        >
        <?php if ((int)$product['stock'] > 0): ?>
          <div class="badge stock-ok">มีสต็อก: <?php echo (int)$product['stock']; ?></div>
        <?php else: ?>
          <div class="badge stock-zero">หมดสต็อก</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ขวา: รายละเอียด + ตัวเลือก -->
    <div class="card">
      <div class="content">
        <div class="crumbs"><a href="index.php" class="muted" style="text-decoration:none;">หน้าสินค้า</a> › รายละเอียด</div>

        <h1 itemprop="name"><?php echo $product['name']; ?></h1>

        <div class="row">
          <div class="price" id="priceLabel" itemprop="price" content="<?php echo number_format($displayPrice, 2, '.', ''); ?>">
            <?php echo format_price($displayPrice); ?>
          </div>
          <meta itemprop="priceCurrency" content="THB">
          <link itemprop="availability" href="https://schema.org/<?php echo ((int)$product['stock']>0)?'InStock':'OutOfStock'; ?>">
        </div>

        <p class="muted" itemprop="description">เลือกขนาด, ความนุ่มเส้น และท็อปปิ้งตามใจ แล้วกดสั่งซื้อได้เลย (เดโม)</p>

        <form class="row" method="post" action="../payment.php" autocomplete="off" novalidate style="display:block;width:100%">

          <!-- Options: size -->
          <fieldset>
            <legend>ขนาดชาม</legend>
            <div class="opt-row">
              <?php foreach ($optionsDb['size'] as $key => $opt): ?>
                <label class="pill">
                  <input type="radio" name="opt_size" value="<?php echo $key; ?>" <?php echo ($key===$defaultSizeKey?'checked':''); ?> data-add="<?php echo (float)$opt['add']; ?>">
                  <span><?php echo $opt['label']; ?> <?php echo ((float)$opt['add']>0)?'(+'.format_price((float)$opt['add']).')':''; ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </fieldset>

          <!-- Options: noodle hardness -->
          <fieldset>
            <legend>ความนุ่มของเส้น</legend>
            <div class="opt-row">
              <?php foreach ($optionsDb['noodle'] as $key => $opt): ?>
                <label class="pill">
                  <input type="radio" name="opt_noodle" value="<?php echo $key; ?>" <?php echo ($key===$defaultNoodleKey?'checked':''); ?> data-add="<?php echo (float)$opt['add']; ?>">
                  <span><?php echo $opt['label']; ?> <?php echo ((float)$opt['add']>0)?'(+'.format_price((float)$opt['add']).')':''; ?></span>
                </label>
              <?php endforeach; ?>
            </div>
            <div class="cap">หมายเหตุ: ตัวเลือกเส้นไม่มีผลต่อราคาในเดโมนี้</div>
          </fieldset>

          <!-- Toppings -->
          <fieldset>
            <legend>ท็อปปิ้ง</legend>
            <div class="top-grid">
              <?php
              $allowed = $product['allowed_topping_ids'] ?? [];
              foreach ($allowed as $tid):
                if (!isset($toppings[$tid])) continue;
                $t = $toppings[$tid];
                $disabled = ((int)$t['stock'] <= 0);
                $isDefault = in_array($tid, $product['default_topping_ids'] ?? [], true);
              ?>
              <label class="top-item">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                  <div>
                    <strong><?php echo $t['name']; ?></strong>
                    <small>
                      <?php
                        echo ((float)$t['price']>0) ? '+'.format_price((float)$t['price']) : 'ฟรี';
                        echo ' • จำกัดชามละ '.$t['max_per_bowl'].' ชิ้น';
                      ?>
                    </small>
                    <small>คลัง: <?php echo (int)$t['stock']; ?> ชิ้น</small>
                  </div>
                  <input
                    type="number"
                    name="topping_qty[<?php echo (int)$tid; ?>]"
                    min="0"
                    max="<?php echo (int)$t['max_per_bowl']; ?>"
                    value="<?php echo $isDefault ? 1 : 0; ?>"
                    data-price="<?php echo (float)$t['price']; ?>"
                    <?php echo $disabled ? 'disabled' : ''; ?>
                    style="width:80px;padding:8px;border-radius:8px;background:#0b1220;border:1px solid rgba(255,255,255,.12);color:var(--text)"
                  >
                </div>
              </label>
              <?php endforeach; ?>
            </div>
            <div class="cap">* ใส่ตัวเลข 0–จำนวนสูงสุดต่อชาม; ค่าเริ่มต้นจะเลือกท็อปปิ้งฟรี/ที่ติดชามไว้ให้ 1 ชิ้น</div>
          </fieldset>

          <!-- จำนวนสินค้า -->
          <div class="row" style="justify-content:space-between;align-items:center">
            <div class="qty">
              <label for="qty">จำนวนชาม</label>
              <input
                type="number" id="qty" name="qty"
                min="1"
                max="<?php echo max(0, (int)$product['stock']); ?>"
                value="<?php echo (int)min(1, max(0, (int)$product['stock'])); ?>"
                <?php echo ((int)$product['stock'] <= 0) ? 'disabled' : ''; ?>
                inputmode="numeric" pattern="[0-9]*"
              >
            </div>

            <!-- ราคาสรุปแบบไลฟ์ -->
            <div class="price" id="liveTotal"><?php echo format_price($displayPrice); ?></div>
          </div>

          <!-- hidden สำหรับระบุสินค้า -->
          <input type="hidden" name="product_id" value="<?php echo (int)$id; ?>">

          <div class="actions">
            <button class="btn" type="submit" <?php echo ((int)$product['stock'] <= 0) ? 'disabled aria-disabled="true"' : ''; ?>>
              <?php echo ((int)$product['stock'] > 0) ? 'สั่งซื้อทันที' : 'Out of Stock'; ?>
            </button>
            <a class="btn ghost" href="../main.php">← กลับหน้าแรก</a>

            <div class="nav" style="margin-left:auto">
              <?php if ($prevId !== null): ?>
                <a href="buy.php?id=<?php echo (int)$prevId; ?>">← ก่อนหน้า</a>
              <?php endif; ?>
              <?php if ($nextId !== null): ?>
                <a href="buy.php?id=<?php echo (int)$nextId; ?>">ถัดไป →</a>
              <?php endif; ?>
            </div>
          </div>
        </form>

        <!-- schema.org (Offer) -->
        <meta itemprop="price" content="<?php echo number_format($displayPrice, 2, '.', ''); ?>">
        <meta itemprop="priceCurrency" content="THB">
      </div>
    </div>
  </div>

  <!-- structured data (Product) เพิ่มเติมสำหรับ SEO -->
  <meta itemprop="sku" content="SKU-<?php echo (int)$id; ?>">

  <!-- JS เล็กน้อย อัปเดตราคาแบบเรียลไทม์: base + size + toppings(คิดตามจำนวนที่กรอก) -->
  <script>
    (function(){
      var base = <?php echo json_encode((float)$base); ?>;
      var priceLabel = document.getElementById('priceLabel');
      var liveTotal = document.getElementById('liveTotal');

      function getSizeAdd(){
        // แก้ไข: ต้องเพิ่ม :checked เพื่อเลือกขนาดที่ถูกเลือกอยู่เท่านั้น
        var el = document.querySelector('input[name="opt_size"]:checked'); 
        return el ? parseFloat(el.getAttribute('data-add')||'0') : 0;
      }

      function sumToppings(){
        var total = 0;
        document.querySelectorAll('input[name^="topping_qty["]').forEach(function(inp){
          var qty = parseInt(inp.value || '0', 10);
          var p = parseFloat(inp.getAttribute('data-price')||'0');
          if (!isNaN(qty) && !isNaN(p)) total += qty * p;
        });
        return total;
      }
      
      function getQuantity() {
          var qtyInput = document.getElementById('qty');
          if (qtyInput) {
              var quantity = qtyInput.value;
              var parsedQty = parseInt(quantity, 10);
              if (!isNaN(parsedQty) && parsedQty > 0) {
                  return parsedQty;
              }
          }
          return 1;
      }
      
      function updatePrice(){
        // คำนวณราคาต่อชาม: ราคาพื้นฐาน + ราคาขนาด + ราคารวมท็อปปิ้ง
        var pricePerUnit = base + getSizeAdd() + sumToppings();
        // คำนวณราคารวม: (ราคาต่อชาม) * จำนวนชาม
        var subtotal = pricePerUnit * getQuantity(); 

        // อัปเดตทั้ง content (schema.org) และข้อความโชว์
        var v = subtotal.toFixed(2);
        priceLabel.setAttribute('content', v);
        liveTotal.textContent = '฿' + Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
      }

      // ตรวจสอบการเปลี่ยนค่าแบบสมบูรณ์
      document.addEventListener('change', function(e){
        // ตรวจสอบการเปลี่ยนขนาด (opt_size) หรือการเปลี่ยนจำนวนชาม (qty)
        if (e.target.matches('input[name="opt_size"], input#qty')) updatePrice();
      });
      
      document.addEventListener('input', function(e){
        // ตรวจสอบการกรอกจำนวนชาม (qty)
        if (e.target.matches('input#qty')) {
            updatePrice();
        }
        
        // ตรวจสอบและจำกัดจำนวนท็อปปิ้ง (topping_qty)
        if (e.target.matches('input[name^="topping_qty["]')) {
          var max = parseInt(e.target.getAttribute('max')||'0',10);
          var v = parseInt(e.target.value||'0',10);
          if (v<0) e.target.value = 0;
          if (!isNaN(max) && v>max) e.target.value = max;
          updatePrice();
        }
      });

      // เริ่มต้น: คำนวณราคาครั้งแรกเมื่อโหลดหน้า
      updatePrice();
    })();
</script>
<?php endif; ?>
</body>
</html>
