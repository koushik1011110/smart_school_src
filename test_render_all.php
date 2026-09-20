<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once('application/config/database.php');

// Let's check what each print output looks like
// 1. printFeesByName for 7703 / 1
// 2. printFeesByGroup for 1951
// 3. printFeesByGroupArray for 1951
// 4. schoolerPrintByGroupArray for 1951

echo "Checking templates...\n";
