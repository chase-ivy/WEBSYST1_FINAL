<?php
include 'auth.php';

logout_user();
header('Location: index.php');
exit;
