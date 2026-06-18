<?php

$services = array(
    'th_woocommerce_key' => array(
        'functions' => array('th_woocommerce_key_api'),
        'requiredcapability' => 0,
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'th_woocommerce_key',
    ),
);

$functions = array(
    'th_woocommerce_key_api' => array(
        'classname' => 'local_th_woocommerce_key_external',
        'methodname' => 'th_woocommerce_key',
        'classpath' => 'local/th_woocommerce_key/externallib.php',
        'description' => 'gradebook student',
        'type' => 'write',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
    ),
);