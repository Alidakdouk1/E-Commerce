<?php
session_start();
$_SESSION['test'] = 'working';
echo "Session variable set. <a href='test2.php'>Check it here</a>";
?>
