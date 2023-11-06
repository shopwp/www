<dl class="faqs">

   <?php 
   
      foreach ($faqs as $faq) {

      $question = get_field('faq_question', $faq->ID);
      
      ?>

      <div class="l-col" itemprop="mainEntity" itemscope itemtype="https://schema.org/Question">
         <dt class="faq-question l-row" data-faq-question="<?php echo $question; ?>">
            
            <div itemprop="name">
				   
               <?php echo $question; ?> 

               <svg class="is-showing faq-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>

               <svg class="is-hiding faq-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M432 256c0 17.7-14.3 32-32 32L48 288c-17.7 0-32-14.3-32-32s14.3-32 32-32l352 0c17.7 0 32 14.3 32 32z"/></svg>

			</div>

         </dt>
         <dd class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
            <div itemprop="text"><?php the_field('faq_answer', $faq->ID); ?></div>
            <a href="/faqs/<?= $faq->post_name; ?>" style="visibility:hidden;">View FAQ detail page</a>
         </dd>
      </div>
      
   <?php } ?>
   
</dl>