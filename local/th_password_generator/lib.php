<?php
require_once $CFG->dirroot . '/config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

class th_password_generator {
	const ONE_DAY = 24 * 60 * 60;
}

//done

function randomPassword() {

	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_password_generator");

	$passwordsymbols = (int) $config->passwordsymbols;
	$passwordnumbers = (int) $config->passwordnumbers;
	$passwordlowercase = (int) $config->passwordlowercase;
	$passworduppercase = (int) $config->passworduppercase;
	$passwordsimilar = (int) $config->passwordsimilar;
	$passwordambiguous = (int) $config->passwordambiguous;
	$rdpasswordsymbols = (int) $config->lenpasswordsymbols;
	$rdpasswordnumbers = (int) $config->lenpasswordnumbers;
	$rdpasswordlowercase = (int) $config->lenpasswordlowercase;
	$rdpassworduppercase = (int) $config->lenpassworduppercase;
	$addrdpasswordsimilar = $config->addpasswordsimilar;
	$addrdpasswordambiguous = $config->addpasswordambiguous;

	$rdpassword = $rdpasswordsymbols + $rdpasswordnumbers + $rdpasswordlowercase + $rdpassworduppercase;

	$addrdpasswordambiguous = str_split($addrdpasswordambiguous);

	$rdpasswordsymbolschar = '!@#$%^&*()_+=-|{}[]()/\"`~,;:.<>';
	$rdpasswordsymbolschar1 = str_split($rdpasswordsymbolschar);
	$rdpasswordsymbolschar1 = array_diff($rdpasswordsymbolschar1, $addrdpasswordambiguous);
	$kitudacbiet = implode('', $rdpasswordsymbolschar1);

	$addrdpasswordsimilar = str_split($addrdpasswordsimilar);
	$rdpasswordnumberschar = '0123456789';
	$rdpasswordnumberschar1 = str_split($rdpasswordnumberschar);
	$rdpasswordnumberschar1 = array_diff($rdpasswordnumberschar1, $addrdpasswordsimilar);
	$so = implode('', $rdpasswordnumberschar1);

	$rdpasswordlowercasechar = 'abcdefghijklmnopqrstuvxwyz';
	$rdpasswordlowercasechar1 = str_split($rdpasswordlowercasechar);
	$rdpasswordlowercasechar1 = array_diff($rdpasswordlowercasechar1, $addrdpasswordsimilar);
	$chuthuong = implode('', $rdpasswordlowercasechar1);

	$rdpassworduppercasechar = 'ABCDEFGHIJKOLMNPQRSTUVXWYZ';
	$rdpassworduppercasechar1 = str_split($rdpassworduppercasechar);
	$rdpassworduppercasechar1 = array_diff($rdpassworduppercasechar1, $addrdpasswordsimilar);
	$chuhoa = implode('', $rdpassworduppercasechar1);

	// $r = array();

	// for ($i = 0; $i < $rdpassword; $i + 4) {
	// 	$r[] = substr($kitudacbiet, Rand(0, strlen($kitudacbiet) - 1), 1);

	// }
	// for ($i = 0; $i < $rdpassword; $i + 4) {
	// 	$r[] = substr($so, Rand(0, strlen($so) - 1), 1);

	// }
	// for ($i = 0; $i < $rdpassword; $i + 4) {
	// 	$r[] = substr($chuthuong, Rand(0, strlen($chuthuong) - 1), 1);

	// }
	// for ($i = 0; $i < $rdpassword; $i + 4) {
	// 	$r[] = substr($chuhoa, Rand(0, strlen($chuhoa) - 1), 1);

	// }
	$pass_order = Array();
	if ($passwordsymbols == 1 && $passwordambiguous == 1) {
		//Create contents of the password
		for ($i = 0; $i < $rdpasswordsymbols; $i++) {

			// $pass_order[] = subtrs($kitudacbiet, Rand(0, strlen($kitudacbiet) - 1));
			$pass_order[] = $kitudacbiet[Rand(0, strlen($kitudacbiet) - 1)];

		}
	} elseif ($passwordsymbols == 1 && $passwordambiguous == 0) {
		for ($i = 0; $i < $rdpasswordsymbols; $i++) {
			$pass_order[] = $rdpasswordsymbolschar[Rand(0, strlen($rdpasswordsymbolschar) - 1)];
		}
	}
	if ($passwordnumbers == 1 && $passwordsimilar == 1) {
		for ($i = 0; $i < $rdpasswordnumbers; $i++) {
			$pass_order[] = $so[Rand(0, strlen($so) - 1)];

		}
	} elseif ($passwordnumbers == 1 && $passwordsimilar == 0) {
		for ($i = 0; $i < $rdpasswordnumbers; $i++) {
			$pass_order[] = $rdpasswordnumberschar[Rand(0, strlen($rdpasswordnumberschar) - 1)];

		}
	}
	if ($passwordlowercase == 1 && $passwordsimilar == 1) {
		for ($i = 0; $i < $rdpasswordlowercase; $i++) {
			$pass_order[] = $chuthuong[Rand(0, strlen($chuthuong) - 1)];

		}
	} elseif ($passwordlowercase == 1 && $passwordsimilar == 0) {
		for ($i = 0; $i < $rdpasswordlowercase; $i++) {
			$pass_order[] = $rdpasswordlowercasechar[Rand(0, strlen($rdpasswordlowercasechar) - 1)];

		}
	}
	if ($passworduppercase == 1 && $passwordsimilar == 1) {
		for ($i = 0; $i < $rdpassworduppercase; $i++) {
			$pass_order[] = $chuhoa[Rand(0, strlen($chuhoa) - 1)];

		}
	} elseif ($passworduppercase == 1 && $passwordsimilar == 0) {
		for ($i = 0; $i < $rdpassworduppercase; $i++) {
			$pass_order[] = $rdpassworduppercasechar[Rand(0, strlen($rdpassworduppercasechar) - 1)];

		}
	}
	shuffle($pass_order);

	return implode('', $pass_order);

}
