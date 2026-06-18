<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->libdir . '/csvlib.class.php';
require_once $CFG->dirroot . '/local/th_register/lib.php';

global $DB, $CFG, $COURSE, $USER;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_register/index.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('register_course', 'local_th_register'));
$PAGE->set_title(get_string('register_course', 'local_th_register'));
$editurl = new moodle_url('/local/th_register/index.php');
$PAGE->settingsnav->add(get_string('register_course', 'local_th_register'), $editurl);

echo $OUTPUT->header();

$link = $CFG->wwwroot . '/local/th_register/index.php';
$link1 = $CFG->wwwroot . '/local/th_register/mon_hoc/index.php';
$link2 = $CFG->wwwroot . '/local/th_register/dot_mo_mon/index.php';
$link3 = $CFG->wwwroot . '/local/th_register/doi_tuong/index.php';
$link4 = $CFG->wwwroot . '/local/th_register/nganh/index.php';
$link5 = $CFG->wwwroot . '/local/th_register/mon_has_dot_mo/index.php';
$link6 = $CFG->wwwroot . '/local/th_register/hoa_don/index.php';

$ds_monhoc = $DB->get_records_sql("SELECT * FROM {th_mon_hoc}");
$ds_nganh = $DB->get_records_sql("SELECT * FROM {th_nganh_hoc}");
$ds_doi_tuong = $DB->get_records_sql("SELECT * FROM {th_doi_tuong}");
$ds_dot_mo = $DB->get_records_sql("SELECT * FROM {th_dot_mo_mon}");
$ds_hoa_don = $DB->get_records_sql("SELECT * FROM {th_hoa_don}");
if (!empty($ds_monhoc)) {
    $so_mon = count($ds_monhoc);
} else {
    $so_mon = 0;
}

if (!empty($ds_nganh)) {
    $so_nganh = count($ds_nganh);
} else {
    $so_nganh = 0;
}

if (!empty($ds_doi_tuong)) {
    $so_doi_tuong = count($ds_doi_tuong);
} else {
    $so_doi_tuong = 0;
}

if (!empty($ds_dot_mo)) {
    $so_dot_mo = count($ds_dot_mo);
} else {
    $so_dot_mo = 0;
}

if (!empty($ds_hoa_don)) {
    $so_hoa_don = count($ds_hoa_don);
} else {
    $so_hoa_don = 0;
}

$manage_registration = get_string('manage_registration', 'local_th_register');
$manage_courses = get_string('manage_courses', 'local_th_register');
$total_courses = get_string('total_courses', 'local_th_register', $so_mon);
$manage_majors = get_string('manage_majors', 'local_th_register');
$total_majors = get_string('total_majors', 'local_th_register', $so_nganh);
$manage_students = get_string('manage_students', 'local_th_register');
$total_students = get_string('total_students', 'local_th_register', $so_doi_tuong);
$manage_opening = get_string('manage_opening', 'local_th_register');
$total_opening = get_string('total_opening', 'local_th_register', $so_dot_mo);
$manage_invoices = get_string('manage_invoices', 'local_th_register');
$total_invoices = get_string('total_invoices', 'local_th_register', $so_hoa_don);
$manage_course_opening = get_string('manage_course_opening', 'local_th_register');
$course_opening_list = get_string('course_opening_list', 'local_th_register');
echo "<head>
<meta charset='UTF-8'>
<link rel='stylesheet' href='./style.css'>
</head>
<body>
<div id='container'>
	<div class='content1'>
		<h1><center>$manage_registration</center></h1>
		<div id='box'>
			<div class='box-top'>
				<a href='$link1'><strong>$manage_courses</strong></a>
			</div>
			<div class='box-panel'>$total_courses</div>
		</div>
		<div id='box'>
			<div class='box-top'>
				<a href='$link4'><strong>$manage_majors</strong></a>
			</div>
			<div class='box-panel'>$total_majors</div>
		</div>
		<div id='box'>
			<div class='box-top'>
				<a href='$link3'><strong>$manage_students</strong></a>
			</div>
			<div class='box-panel'>$total_students</div>
		</div>
		<div id='box'>
			<div class='box-top'>
				<a href='$link2'><strong>$manage_opening</strong></a>
			</div>
			<div class='box-panel'>$total_opening</div>
		</div>
		<div id='box'>
			<div class='box-top'>
				<a href='$link6'><strong>$manage_invoices</strong></a>
			</div>
			<div class='box-panel'>$total_invoices</div>
		</div>
		<div id='box'>
			<div class='box-top'>
				<a href='$link5'><strong>$manage_course_opening</strong></a>
			</div>
			<div class='box-panel'>$course_opening_list</div>
		</div>
	</div>
</div>
</body>";

echo $OUTPUT->footer();
