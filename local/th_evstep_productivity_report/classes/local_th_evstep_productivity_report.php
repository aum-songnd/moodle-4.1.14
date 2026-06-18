<?php

require_once __DIR__ . '/../../../config.php';
require_once $CFG->dirroot . '/lib/excellib.class.php';
use \PhpOffice\PhpSpreadsheet\IOFactory;

class local_th_evstep_productivity_report extends MoodleExcelWorkbook {

	public function close1($filepath) {
		global $CFG;

		foreach ($this->objspreadsheet->getAllSheets() as $sheet) {
			$sheet->setSelectedCells('A1');
		}
		$this->objspreadsheet->setActiveSheetIndex(0);

		$filename = preg_replace('/\.xlsx?$/i', '', $this->filename);

		$filename = $filename . '.xlsx';

		if (core_useragent::is_ie() || core_useragent::is_edge()) {
			$filename = rawurlencode($filename);
		} else {
			$filename = s($filename);
		}

		$objwriter = IOFactory::createWriter($this->objspreadsheet, $this->type);
		$objwriter->save($filepath);
	}
}