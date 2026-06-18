<?php
$plugin->component = 'block_th_report_total_enrolments'; // Recommended since 2.0.2 (MDL-26035). Required since 3.0 (MDL-48494)
$plugin->version = 2026032600; // YYYYMMDDHH (year, month, day, 24-hr time)
$plugin->requires = 2010112400; // YYYYMMDDHH (This is the release version for Moodle 2.0)
$plugin->dependencies = array(
	'local_thlib' => '2021100000',
);