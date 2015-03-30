<?php

require 'vendor/autoload.php';

require_once('../adm/conexion.php');

function remove_spaces($str) {
	return str_replace(' ', '', $str);
}

function pr($mixed) {
	echo '<pre>';
	var_dump($mixed);
	exit;
}

function dd($mixed) {
	echo '<pre>';
	print_r($mixed);
	exit;
}

$filepath = realpath(__DIR__ . '/../../private/import-data/segundo-basico-a/format.xls');

if (!@file_exists($filepath)) {
	echo 'File not found'; exit;
}

$objPHPexcel = PHPExcel_IOFactory::load($filepath);

// $rendererName = PHPExcel_Settings::PDF_RENDERER_DOMPDF;
// $rendererLibrary = 'domPDF0.6.0beta3';
// $rendererLibraryPath = dirname(__FILE__). 'libs/classes/dompdf' . $rendererLibrary;

// PHPExcel_Settings::setPdfRenderer($rendererName,$rendererLibraryPath);

// pr($objPHPexcel);

// header('Content-Type: application/pdf');
// header('Content-Disposition: attachment;filename="membership.pdf"');
// header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPexcel, 'PDF');
$objWriter->setPreCalculateFormulas(false);
$objWriter->save('php://output');

// 
// Read ODS file
// 

// $inputFileType = PHPExcel_IOFactory::identify($filepath);
// $objReader = PHPExcel_IOFactory::createReader($inputFileType);
// $objPHPExcel = $objReader->load($filepath);
// $worksheet = $objPHPExcel->getActiveSheet();

// foreach ($worksheet->getRowIterator() as $row) {
// 	$cellIterator = $row->getCellIterator();
// 	$cellIterator->setIterateOnlyExistingCells(false);

// 	foreach ($cellIterator as $cell) {
// 		$cell_value = $cell->getValue();
// 		$cell_value = htmlentities($cell_value);

// 		$list[$cell->getRow()][$cell->getColumn()] = $cell_value;
// 	}
// }

