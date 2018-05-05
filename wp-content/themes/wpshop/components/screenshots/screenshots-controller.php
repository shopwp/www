<?php

  $screenshots = get_sub_field('screenshots');
  $short_desc = get_sub_field('screenshots_short_description');

  include(locate_template('components/screenshots/screenshots-view.php'));
