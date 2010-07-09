<?php

declare(encoding='UTF-8');
namespace HobokenTest;

require_once 'PHPUnit/Framework.php';
require_once 'TestCase.php';

$hobokenTestPath = dirname(__FILE__);
$hobokenLibPath  = $hobokenTestPath . '/../';
set_include_path(get_include_path() . PATH_SEPARATOR . $hobokenLibPath . PATH_SEPARATOR . $hobokenTestPath);

define('DS', DIRECTORY_SEPARATOR, false);
define('DIRECTORY_TESTSUITE', $hobokenTestPath . DS, false);
define('DIRECTORY_DATA', DIRECTORY_TESTSUITE . '_Data' . DS, false);