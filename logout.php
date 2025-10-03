<?php
// logout.php
require_once 'config.php';

session_destroy();
redirect('/login');
?>
<!-- END logout.php -->