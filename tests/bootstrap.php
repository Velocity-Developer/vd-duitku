<?php

$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = sys_get_temp_dir() . '/wordpress-tests-lib';
}

if (!file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "WordPress test suite tidak ditemukan. Set WP_TESTS_DIR ke lokasi wordpress-tests-lib.\n");
    exit(1);
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter('muplugins_loaded', function () {
    require dirname(__DIR__) . '/vd-duitku.php';
});

require $_tests_dir . '/includes/bootstrap.php';
