<?php 

global $post;

function empty_content($str) {
    return trim(str_replace('&nbsp;','',strip_tags($str,'<img>'))) == '';
}

?>

<?php 

if (is_page('checkout') || is_page('purchase-confirmation')) {
	the_content();

} else { ?>

<article <?php post_class(is_page('checkout') ? '' : 'l-contain'); ?>>

	<div class="content l-contain-s l-text-center">

		<?php 

			if (!is_page('about') && !is_page('slack') && !is_page('testimonials')) {
				the_title( '<h1 class="entry-title">', '</h1>' );
			}
		
		?>
		
		<?php if (get_field('page_short_description')) { ?>
			<p><?= get_field('page_short_description'); ?></p>
		<?php } ?>
		
	</div>

	<?php if (!empty($post->post_content)) { ?>
		<div class="<?= is_page('login') ? 'l-contain-xs' : (is_page('checkout') ? '' : 'l-contain-s') ?> component-generic">
			<?= the_content(); ?>	
		</div>
	<?php } ?>

</article>

<?php }