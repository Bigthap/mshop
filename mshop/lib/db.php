<?php
function soups()    { return require __DIR__ . '/../data/products.php'; }
function toppings() { return require __DIR__ . '/../data/toppings.php'; }
function optionsDb(){ return require __DIR__ . '/../data/options.php'; }

function findSoup($id){
  $s = soups();
  return $s[$id] ?? null;
}
function findTopping($id){
  $t = toppings();
  return $t[$id] ?? null;
}

/**
 * คำนวณราคารวมของ 1 ออเดอร์ราเมง
 * @param int $soupId
 * @param int[] $toppingIds
 * @param array $opts ['size'=>'large','noodle'=>'normal','spicy'=>'2']
 * @return array ['base'=>..., 'toppings'=>..., 'options'=>..., 'total'=>... , 'breakdown'=>...]
 */
function calcRamenPrice($soupId, array $toppingIds, array $opts){
  $soup   = findSoup($soupId);
  if(!$soup) throw new RuntimeException('Soup not found');
  if($soup['stock'] <= 0) throw new RuntimeException('Out of stock');

  $topsDb = toppings();
  $optDb  = optionsDb();

  // ตรวจ allowed toppings + stock + per-bowl limit
  $allowed = array_flip($soup['allowed_topping_ids']);
  $validTops = [];
  foreach($toppingIds as $tid){
    if(!isset($allowed[$tid])) continue;              // ข้ามตัวที่ไม่อนุญาต
    if(!isset($topsDb[$tid])) continue;
    if($topsDb[$tid]['stock'] <= 0) continue;
    $validTops[] = $tid;
  }

  // enforce max_total_toppings
  $maxT = $optDb['limits']['max_total_toppings'] ?? PHP_INT_MAX;
  if(count($validTops) > $maxT){
    $validTops = array_slice($validTops, 0, $maxT);
  }

  $base = (float)$soup['base_price'];

  // ราคารวมท็อปปิ้ง
  $topsPrice = 0.0;
  $topsBreak = [];
  foreach($validTops as $tid){
    $p = (float)$topsDb[$tid]['price'];
    $topsPrice += $p;
    $topsBreak[] = ['id'=>$tid,'name'=>$topsDb[$tid]['name'],'price'=>$p];
  }

  // ราคา options
  $optAdd = 0.0;
  $optBreak = [];
  foreach(['size','noodle','spicy'] as $k){
    if(isset($opts[$k]) && isset($optDb[$k][$opts[$k]])){
      $add = (float)$optDb[$k][$opts[$k]]['add'];
      $optAdd += $add;
      $optBreak[] = ['key'=>$k,'label'=>$optDb[$k][$opts[$k]]['label'],'add'=>$add];
    }
  }

  $total = $base + $topsPrice + $optAdd;

  return [
    'base' => $base,
    'toppings' => $topsPrice,
    'options' => $optAdd,
    'total' => $total,
    'breakdown' => [
      'soup' => ['id'=>$soup['id'],'name'=>$soup['name'],'price'=>$base],
      'toppings' => $topsBreak,
      'options' => $optBreak,
    ]
  ];
}
