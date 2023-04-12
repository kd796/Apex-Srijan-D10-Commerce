<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// ini_set("register_globals", 0);

define('ADE_TRANSLATE', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

require_once(__DIR__ . '/convert_variables.php');
include_once(__DIR__ . '/Database.php');
require_once(__DIR__ . '/cookie.php');
$userid = ade_get_cookie();

if ($userid === false) :
    header('Location: /' . DatabaseSel::translate('tools/advanced-drilling'));

    exit;
endif;

$filePath = \Drupal::service('file_url_generator')->generateAbsoluteString('public://');
$fileName = DatabaseSel::export_pdf($userid, $conversion_array);
$pdfFile  = $filePath . 'advanced_selector/' . $fileName;

header('Location: ' . $pdfFile);

exit;
