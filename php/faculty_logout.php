<?php
session_start();

if (isset($_SESSION['faculty_id'])) {
    session_destroy();
}

header("Location: ../faculty/faculty_auth.php");
exit();
?>