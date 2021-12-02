<?php

if ($isChangelong) {

  get_template_part('templates/content', 'changelogs');

} else {
  echo $post->post_content;

}
