<?php

include_once("../searchVE.php");

$digit = isset($_REQUEST['digito']) ? $_REQUEST['digito'] : "";;
$type = isset($_REQUEST['documentType']) ? $_REQUEST['documentType'] : "V";
$document = isset($_REQUEST['documentNumber']) ? $_REQUEST['documentNumber'] : "";

$allData = SearchVE::search($document, $type, $digit);

header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
echo json_encode($allData);
