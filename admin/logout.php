<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/session.php';
destroyCurrentSession();
header('Location: ../index.php');
exit();
