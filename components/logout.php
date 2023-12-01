<?php

setcookie('user_name', '', time() - 3600, '/');
setcookie('token', '', time() - 3600, '/');

$redirect_url = "http://localhost:63342/php-blog/components/SignPage.php";
header('Location: ' . $redirect_url);
exit();
?>