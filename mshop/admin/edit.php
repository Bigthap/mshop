<?php

$file = __DIR__ . '/../data/products.php';
$products = require $file;

// รับ id ของสินค้าที่จะปรับ
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!isset($products[$id])) {
  die("❌ Product not found");
}

$product = $products[$id];

// เมื่อกดบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newName = $_POST['name'];
  $newPrice = (float)$_POST['price'];
  $newStock = (int)$_POST['stock'];
  $newImage = $_POST['image'];

  // ปรับข้อมูลในอาร์เรย์
  $products[$id]['name'] = $newName;
  $products[$id]['base_price'] = $newPrice;
  $products[$id]['stock'] = $newStock;
  $products[$id]['image'] = $newImage;

  // เขียนกลับไฟล์ products.php
  $export = "<?php\nreturn " . var_export($products, true) . ";\n";
  file_put_contents($file, $export);

  echo "<p style='color:lime;'>✅ Updated successfully!</p>";
  // โหลดใหม่เพื่อให้เห็นผลล่าสุด
  $product = $products[$id];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <style>
    body { font-family: system-ui; background: #0f172a; color: #e2e8f0; display: grid; place-items: center; min-height: 100vh; }
    form { background: #1e293b; padding: 20px 30px; border-radius: 12px; width: 300px; }
    label { display: block; margin-top: 12px; color: #94a3b8; }
    input { width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: white; }
    button { margin-top: 18px; width: 100%; padding: 10px; border: 0; border-radius: 10px; background: #22d3ee; color: #0f172a; font-weight: 600; cursor: pointer; }
    button:hover{background:#114f28;transition: background-color .2s ease;transform: translateY(-1px)}
    .btn{display:inline-flex;justify-content:center;align-items:center;gap:8px;width: 90%;padding:10px 14px;font-size:14px;font-weight:600;color:#fff;text-decoration:none;border-radius:10px;border:1px solid #16a34a;background:#22c55e;cursor:pointer}
    .btn:hover{background:#e2e8f0;transition: background-color .2s ease;transform: translateY(-1px)}
    .btn.ghost{background:transparent;border:1px dashed rgba(255,255,255,.25)}
  </style>
</head>
<body>
  <form method="post">
    <h2>Edit Product ID#<?= $id ?></h2>
    <p><b><?= htmlspecialchars($product['name']) ?></b></p>
    <label>Name</label>
    <input type="text" name="name" value="<?= $product['name'] ?>">

    <label>Price</label>
    <input type="number" step="0.01" name="price" value="<?= $product['base_price'] ?>">

    <label>Stock</label>
    <input type="number" name="stock" value="<?= $product['stock'] ?>">

    <label>Image</label>
    <input type="text" name="image" value="<?= $product['image'] ?>">

    <button type="submit">💾 Save</button>
    <a class="btn ghost" href="../admin/admin_main.php">ไปยังหน้าหลัก</a>
  </form>
</body>
</html>
