<?php 

if ( $wp_query->current_post == 0 && !is_paged() ) { ?>

   <article <?php post_class('is-latest-post'); ?>>

   <header>

      <div class="l-row">

         <div class="l-box-2 latest-post-img">
            <a href="<?php the_permalink(); ?>" class="post-link">

               <div class="post-thumb" style="background-image: url('<?= get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');"></div>

            </a>
         </div>

         <div class="l-box-2 latest-post-info">
            <a href="<?php the_permalink(); ?>" class="post-link">
               <h1 class="entry-title">
                  <?php the_title(); ?>
               </h1>
            </a>

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

            <div class="moretag-wrapper">
               <a class="btn" href="<?= get_permalink(); ?>">Read more →</a>
            </div>

         </div>
      </div>
         
   </header>
   
   </article>

<?php } else { ?>

   <article <?php post_class('is-not-latest-post'); ?>>

   <header>

   <a href="<?php the_permalink(); ?>" class="post-link">

      <div class="post-thumb" style="background-image: url('<?= get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');"></div>

         <h2 class="entry-title"><?php the_title(); ?></h2>

      </a>
      
      <div class="post-meta-container">

               <time class="updated" datetime="<?= get_post_time('c', true); ?>"><?= get_the_date(); ?></time>

               <div class="post-categories">
                  <?php 
                              
                  $categories = get_the_category();
                     if ( ! empty( $categories ) ) {
                        echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
                     }
                  ?>
               </div>

            </div>
            
            <?php get_template_part('templates/entry-meta'); ?>
            
   </header>

   <div class="entry-summary">
      <?php the_excerpt(); ?>
   </div>
   
   </article>

<?php } ?>