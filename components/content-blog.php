<?php 

$posts_page_id = get_option('page_for_posts');
$blog_page = get_post($posts_page_id);

$posts = get_posts([
    'post_type'             => 'post',
    'post_status'           => 'publish',
    'posts_per_page'        => -1,
    'ignore_sticky_posts'   => true,
    'post_status'           => 'publish'
]);

?>

<article <?php post_class('l-contain'); ?>>

	<div class="content l-contain-s l-text-center">
		<h1><?= $blog_page->post_title; ?></h1>

        <?php if (get_field('page_short_description', $posts_page_id)) { ?>
			<p><?= get_field('page_short_description', $posts_page_id); ?></p>
		<?php } ?>
	</div>

    <div class="component l-contain-m component-blog wrap">
        <?php foreach ($posts as $post) { 
            
            \setup_postdata($post);

            ?>
            <ul class="list-simple">
                <li>
                    <small><?= the_date(); ?><span>🔸</span></small>
                    <a href="<?= $post->guid; ?>"><?= $post->post_title; ?></a>
                </li>
            </ul>
        <?php } ?>
    </div>    

</article>