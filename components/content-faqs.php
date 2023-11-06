<article <?php post_class(); ?> itemprop="mainEntity" itemscope="" itemtype="https://schema.org/Question">

	<div class="content l-contain-s">
		<header class="l-text-center">
			<a href="/faq" class="back-to">< Back to all FAQs</a>
			<h1 class="entry-title" itemprop="name"><?php the_title(); ?></h1>
		</header>

		<div class="entry-summary" itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
			<?php the_field('faq_answer', get_the_ID()); ?>
		</div>
	</div>

</article>

<?php // include(locate_template('components/contact/view.php'));