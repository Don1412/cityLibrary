<?php
require 'vendor/autoload.php';

use Classes\LibraryLogsChecker;

$logs = new LibraryLogsChecker();
$outputFile = 'output/out.txt';
$report = $logs->getReport(LibraryLogsChecker::TEXT_FORMAT);
file_put_contents($outputFile, $report);
