<?php 

$changelog = \get_post_meta(35, '_edd_sl_changelog', true);

?>
<section class="component component-changelog l-contain-s">
    <?= stripslashes($changelog); ?>
</section>