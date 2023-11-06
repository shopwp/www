<?php 

global $post;

?>

<article <?php post_class('l-contain'); ?>>

	<div class="content l-contain-s">

		  <?php 
		  
		  $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); 
		  
		  ?>

		  <img src="<?= $image[0]; ?>" class="post-marquee angled-down" width="800" height="545" />

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<div class="post-info">
			<div class="l-row l-row-m-center">
				<div class="post-author-img-wrapper">
					<img class="post-author-img" src="/wp-content/uploads/2023/09/me-tall-square.jpg" alt="Andrew Robbins, creator of ShopWP" />
				</div>
				<div>
					<p class="post-author">By: <b>Andrew Robbins</b> <span>(Creator of ShopWP)</span></p>
					<p class="post-date">Updated on: <?= get_the_modified_date(); ?></p>
				</div>
			</div>
			
			
		</div>

		<div class="entry-content">
			<?= the_content(); ?>
		</div>

		<div class="footer-author l-row">
			<img class="post-author-img" src="/wp-content/uploads/2023/09/me-tall-square.jpg" alt="Andrew Robbins, creator of ShopWP" />
			<div class="footer-author-info">
				<p>Andrew Robbins</p>
				<p>Creator of ShopWP</p>
			</div>
			<a href="/blog/" class="btn-all-posts">See all posts</a>
		</div>

		<div class="post-footer">

			<p class="mail-heading">Stay up-to-date with ShopWP</p>
			<div class="footer-newsletter">
				<p>Hey y'all&mdash;I'll send you an email when updates are released for ShopWP. I'll also send you weekly tips on improving your WordPress site.</p>
				<div id="root-newsletter-posts"></div>
			</div>	
			
		</div>
		
	</div>

</article>