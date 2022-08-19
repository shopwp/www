<dl class="faqs">

   <?php 
   
      foreach ($faqs as $faq) {

      $question = get_field('faq_question', $faq->ID);
      
      ?>

      <div itemprop="mainEntity" itemscope itemtype="https://schema.org/Question">
         <dt class="faq-question l-row" id="<?php echo formatHashSlug($question); ?>" data-faq-question="<?php echo $question; ?>">
            <div itemprop="name"><?php echo $question; ?> <i class="fas fa-plus-square"></i></div>
         </dt>
         <dd class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
            <div itemprop="text"><?php the_field('faq_answer', $faq->ID); ?></div>
            <a href="/faqs/<?= $faq->post_name; ?>" style="margin-top: 20px;font-size:14px;display:block;">View FAQ detail page</a>
         </dd>
      </div>
      
   <?php } ?>
</dl>