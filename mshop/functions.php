<?php
function validate_required($fields){
  $i=0; while($i<count($fields)){ if(trim($fields[$i])==='') return false; $i++; } return true;
}

function users_file_path(): string { return __DIR__ . '/data/users.php'; }

function users_save_php(array $users): bool {
  $export = var_export($users, true);
  $code = "<?php\n// Auto-generated demo users.\n\$USERS = $export;\n?>\n";
  $target = users_file_path();
  $dir = dirname($target);
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  $tmp = tempnam($dir, 'users_');
  if ($tmp === false) return false;
  if (file_put_contents($tmp, $code, LOCK_EX) === false) { @unlink($tmp); return false; }
  return rename($tmp, $target);
}
