<dl class="faqs">
   <?php foreach ($faq_ids as $faq_id) {

      $question = get_field('faq_question', $faq_id); ?>

      <div itemprop="mainEntity" itemscope itemtype="https://schema.org/Question">
         <dt class="faq-question l-row" id="<?php echo formatHashSlug($question); ?>" data-faq-question="<?php echo $question; ?>">
         <div itemprop="name"><?php echo $question; ?> <i class="fas fa-plus-square"></i></div>
         </dt>
         <dd class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
         <div itemprop="text"><?php the_field('faq_answer', $faq_id); ?></div>
         </dd>
      </div>
      
   <?php } ?>
</dl>