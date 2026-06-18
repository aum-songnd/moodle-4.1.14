<?php
function local_th_register_dot_mo_mon_controls(context $context, moodle_url $currenturl) {
	$tabs = array();
	$currenttab = 'view';
	$view = new moodle_url('/local/th_register/dot_mo_mon/index.php');

	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/dot_mo_mon/index.php');
		$tabs[] = new tabobject('view', $addurl, get_string('all'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'view';
		}
	}
	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/dot_mo_mon/edit.php');
		$tabs[] = new tabobject('edit', $addurl, get_string('add'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'edit';
		}
	}

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}