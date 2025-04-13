<?php
session_start();
session_unset();
session_destroy();

// Weiterleitung zur Startseite nach dem Logout
header("Location: index.php");
exit();
