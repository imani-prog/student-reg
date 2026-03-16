<?php
session_start();

unset($_SESSION['student_id'], $_SESSION['student_name']);
$_SESSION['login_success'] = 'You have been signed out successfully.';

header('Location: ../login.php');
exit;
