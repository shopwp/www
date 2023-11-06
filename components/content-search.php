<section class="l-contain">
	
	<div class="content l-contain-s">
	<?php 
		$args = [
			's' => get_search_query()
        ];
    
		$the_query = new WP_Query($args);
		
		if ( $the_query->have_posts() ) { ?>

			<h1 class="page-title"><?php esc_html_e('Search Results for: ' . get_query_var('s'), 'shopwp' ); ?></h1>
        
			<?php while ( $the_query->have_posts() ) {
				$the_query->the_post(); ?>
						<li>
							<span></span><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
			<?php }

		} else { ?>
			<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'dash' ); ?></h1>
			<div class="alert alert-info">
			<p>Sorry, but nothing matched your search criteria. Please try again with some different keywords.</p>
			</div>
	<?php } ?>
		</div>
</section>
