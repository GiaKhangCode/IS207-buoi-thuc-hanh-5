<?php
declare(strict_types=1);

session_start();
$_SESSION['giohang'] ??= [];
$_SESSION['giohang_token'] ??= bin2hex(random_bytes(32));
