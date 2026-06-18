<?php
defined('MOODLE_INTERNAL') || die();

$settings = null;

if ($hassiteconfig) {
    $ADMIN->add(
        'localplugins',
        new admin_externalpage(
            'local_th_slideshow',
            get_string('pluginname', 'local_th_slideshow'),
            new moodle_url('/local/th_slideshow/index.php'),
            'local/th_slideshow:manage'
        )
    );
}