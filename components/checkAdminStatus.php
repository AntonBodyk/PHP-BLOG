<?php

if (isset($_COOKIE['user_status']) && $_COOKIE['user_status'] == 'admin') {
    echo 'admin';
} else {
    echo 'not_admin';
}
