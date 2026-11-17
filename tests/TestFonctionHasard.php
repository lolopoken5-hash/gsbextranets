<?php
require_once __DIR__ . '/../include/fct.inc.php';

$code = genererCode2FA();
if (!preg_match('/^\d{6}$/', $code)) {
    throw new RuntimeException("Le code doit contenir six chiffres.");
}

echo $code . PHP_EOL;



