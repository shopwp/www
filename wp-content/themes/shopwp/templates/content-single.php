<?php 

global $post;

?>
   <?php while (have_posts()) : the_post(); ?>

   <article <?php post_class(); ?>>

      <header class="post-single-header">

         <div class="l-row l-contain">

            <div class="l-box-2 latest-post-info">

               <?php if ( is_single() && $wp_query->current_post == 0 && !is_paged() ) { ?>
                  <a href="/blog" style="margin-top: -10px;display: block;color: #323232;font-size: 14px;margin-bottom: 10px;">< Back to blog</a>
               <?php } ?>

               <h1 class="entry-title">
                     <?php the_title(); ?>
                  </h1>

               <div class="post-meta-container">

                  <time class="updated" datetime="<?= get_post_time('c', true); ?>"><?= get_the_date(); ?></time>

                  <div class="post-categories">
                     <?php 
                                 
                     $categories = get_the_category();
                        if ( ! empty( $categories ) ) {
                           echo '<a href="/blog">' . esc_html( $categories[0]->name ) . '</a>';
                        }
                     ?>
                  </div>

               </div>

               <div class="post-excerpt"><?= the_excerpt(); ?></div>
               
               <?php get_template_part('templates/entry-meta'); ?>

            </div>

            <div class="l-box-2 latest-post-img">
               <div class="post-thumb" style="background-image: url('<?= get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');"></div>
            </div>

         </div>

      </header>

      <div class="entry-content <?= is_singular('post') ? 'l-contain-narrow' : ''; ?>">

		  <?php the_content(); ?>

         <p class="post-last-updated">Last updated on <?php the_modified_time('F jS, Y'); ?></p>

      </div>

      <div class="article-footer">
         <div class="inner">
            <a href="/blog" rel="author" class="btn btn-secondary"><svg aria-hidden="true" focusable="false" data-prefix="fal" data-icon="long-arrow-left" class="svg-inline--fa fa-long-arrow-left fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="max-width: 20px;margin-left: 0px;margin-right: 5px;"><path fill="currentColor" d="M136.97 380.485l7.071-7.07c4.686-4.686 4.686-12.284 0-16.971L60.113 273H436c6.627 0 12-5.373 12-12v-10c0-6.627-5.373-12-12-12H60.113l83.928-83.444c4.686-4.686 4.686-12.284 0-16.971l-7.071-7.07c-4.686-4.686-12.284-4.686-16.97 0l-116.485 116c-4.686 4.686-4.686 12.284 0 16.971l116.485 116c4.686 4.686 12.284 4.686 16.97-.001z"></path></svg> Back to blog</a>
         </div>
      </div>

   </article>
   
   <?php endwhile; ?>

   <?php include(locate_template('components/pro-purchase/view.php'));