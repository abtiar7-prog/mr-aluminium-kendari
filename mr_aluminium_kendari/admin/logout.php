<?php
require_once '../includes/config.php';

session_destroy();
redirect(ADMIN_URL . '/login.php');
