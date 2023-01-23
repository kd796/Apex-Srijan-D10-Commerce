<?php
	error_reporting(E_ALL);
    ini_set('display_errors', '1');
	ini_set("register_globals", 0);
    require_once($_SERVER['DOCUMENT_ROOT']."/themes/atg/advanced-selector/api/convert_variables.php");
    include_once($_SERVER['DOCUMENT_ROOT']."/themes/atg/advanced-selector/api/Database.php");
	if (!isset($_COOKIE["userid"])):
        //header("location: /tools/advanced-drilling");
    endif;
    $id = $_COOKIE["userid"];
    $filename = DatabaseSel::export_pdf($id, $conversion_array);
	header("location: /" . variable_get('file_public_path', conf_path() . '/files') . "/advanced_selector/".$filename);
?>

