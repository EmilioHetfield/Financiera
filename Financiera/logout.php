<?php
session_start();
session_destroy();
header("Location: /efloresrTest/Financiera/pages-login.html");
exit();
?>
