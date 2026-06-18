<?php

// require('../../config.php');
require_once($CFG->libdir . '/pdflib.php');


class thpagepdf extends pdf {

	public function Header() {
		if ($this->header_xobjid === false) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			$this->y = $this->header_margin;
			if ($this->rtl) {
				$this->x = $this->w - $this->original_rMargin;
			} else {
				$this->x = $this->original_lMargin;
			}
			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$imgtype = TCPDF_IMAGES::getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
				if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
					$this->ImageEps(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} elseif ($imgtype == 'svg') {
					$this->ImageSVG(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} else {
					$this->Image($headerdata['logo'], '', '', $headerdata['logo_width']);
				}
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->y;
			}
			$cell_height = $this->getCellHeight($headerfont[2] / 1);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
			} else {
				$header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
			}
			$cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
			$this->SetTextColorArray($this->header_text_color);
			// header title
			$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
			$this->SetX($header_x);
			// $this->SetY($this->GetY()  +2 );
			$this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, 'C', 0, '', 0, false);
			// header string
			// $this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
			// $this->SetX($header_x);
			// $this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
			$this->endTemplate();
		}
		// print header template
		$x = 0;
		$dx = 0;
		if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = false;
		}
	}

	public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M') {
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		$this->adjustCellPadding($border);
		if (!$ignore_min_height) {
			$min_cell_height = $this->getCellHeight($this->FontSize);
			if ($h < $min_cell_height) {
				$h = $min_cell_height;
			}
		}
		$this->checkPageBreak($h + $this->cell_margin['T'] + $this->cell_margin['B']);
		// apply text shadow if enabled
		if ($this->txtshadow['enabled']) {
			// save data
			$x = $this->x;
			$y = $this->y;
			$bc = $this->bgcolor;
			$fc = $this->fgcolor;
			$sc = $this->strokecolor;
			$alpha = $this->alpha;
			// print shadow
			$this->x += $this->txtshadow['depth_w'];
			$this->y += $this->txtshadow['depth_h'];
			$this->SetFillColorArray($this->txtshadow['color']);
			$this->SetTextColorArray($this->txtshadow['color']);
			$this->SetDrawColorArray($this->txtshadow['color']);
			if ($this->txtshadow['opacity'] != $alpha['CA']) {
				$this->setAlpha($this->txtshadow['opacity'], $this->txtshadow['blend_mode']);
			}
			if ($this->state == 2) {
				$this->_out($this->getCellCode($w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, true, $calign, $valign));
			}
			//restore data
			$this->x = $x;
			$this->y = $y;
			$this->SetFillColorArray($bc);
			$this->SetTextColorArray($fc);
			$this->SetDrawColorArray($sc);
			if ($this->txtshadow['opacity'] != $alpha['CA']) {
				$this->setAlpha($alpha['CA'], $alpha['BM'], $alpha['ca'], $alpha['AIS']);
			}
		}
		if ($this->state == 2) {
			$this->_out($this->getCellCode($w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, true, $calign, $valign));
		}
		$this->cell_padding = $prev_cell_padding;
		$this->cell_margin = $prev_cell_margin;
	}
}