<?php
session_start();

$_SESSION = [];
session_unset();
session_destroy();

// Xóa cookie
$cookieParams = ['expires' => time() - 3600, 'path' => '/']; // hết hạn và áp dụng toàn site

setcookie('user_id', '', $cookieParams);
setcookie('role', '', $cookieParams);
setcookie('username', '', $cookieParams);

header('Location: index.php');
exit;
?>
