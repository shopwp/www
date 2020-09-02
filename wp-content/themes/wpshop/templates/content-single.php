<?php 

global $post;

?>
   <?php while (have_posts()) : the_post(); ?>

   <article <?php post_class(); ?>>

      <header class="post-single-header">

         <div class="l-row">

            

            <div class="l-box-2 latest-post-info">

            <?php if ( is_single() && $wp_query->current_post == 0 && !is_paged() ) { ?>
   <a href="/blog" style="margin-top: 0;display: block;color: #323232;font-size: 14px;margin-bottom: 10px;">< Back to blog</a>
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

   </article>

   

   <div class="article-footer">
   
      <div class="inner">

         <div class="post-sharing">
            <?= do_shortcode('[social_warfare buttons="twitter,facebook,linkedin"]'); ?>
         </div>
         
         <div class="post-affiliate">

            <a href="https://www.shopify.com/?ref=wps" target="_blank" class="affiliate-link">
               <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/imgs/Sell-online-728x90.png" alt="WP Shopify Pro feature product filtering" />
            </a>
         </div>
      
         <a href="/blog" rel="author" class="btn btn-secondary">Back to blog</a>

      </div>
   </div>

   

   
   <?php endwhile; ?>