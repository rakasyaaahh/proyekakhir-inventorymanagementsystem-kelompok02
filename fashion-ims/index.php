<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /fashion-ims/pages/dasbor.php');
} else {
    header('Location: /fashion-ims/login.php');
}
exit;
