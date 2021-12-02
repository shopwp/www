<?php

   $screenshot_1_image = get_sub_field('screenshot_1_image');   
   $screenshot_2_image = get_sub_field('screenshot_2_image');

   $screenshot_secondary_description = get_sub_field('screenshot_secondary_description');

   include(locate_template('components/screenshots-secondary/screenshots-secondary-view.php'));
