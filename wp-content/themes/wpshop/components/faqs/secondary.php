<section class="component component-faq-secondary">

   
   <div class="l-row">
   
      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/imgs/illustrations/faq.svg" />

      <div class="faqs-wrap">

         <h2>You have questions and we have answers!</h2>
         <p>Below are the most common questions about the plugin. Don't see your question below? <a href="/faq">View the full list</a> or contact us directly.</p>
         <?php 

         $faq_ids = $faqs_selected;

         include(locate_template('components/faqs/content.php'));

         ?>

         <a href="/faq" class="btn btn-secondary">Read more FAQs <i class="fal fa-long-arrow-right"></i></a>

      </div>

   
   </div>

</section>
