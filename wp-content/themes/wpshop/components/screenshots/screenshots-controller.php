<?php

$screenshots = get_sub_field('screenshots');
$short_desc = get_sub_field('screenshots_short_description');
$has_bg = get_sub_field('has_background');

include(locate_template('components/screenshots/screenshots-view.php'));
