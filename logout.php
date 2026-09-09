<?php
require_once __DIR__ . '/includes/session.php';
destroyCurrentSession();
header('Location: index.php');
exit;
