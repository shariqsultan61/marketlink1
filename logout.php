<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php?msge=Logged out successfully&type=success");
exit();
?>