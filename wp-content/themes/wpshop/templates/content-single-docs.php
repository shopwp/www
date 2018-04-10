<?php

/*

Determine the doc type

*/

$type = get_field('doc_type', $post->ID);
$since = get_field('doc_since', $post->ID);
$source = get_field('doc_source', $post->ID);

include(locate_template('templates/content-single-docs-meta.php'));
include(locate_template('templates/content-single-docs-' . $type[0]->slug . '.php'));
