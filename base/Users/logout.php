<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: ../src/login.php");
exit();
