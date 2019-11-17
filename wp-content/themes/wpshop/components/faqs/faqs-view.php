<section class="component component-faq l-contain-narrow wrap">

  <?php foreach ($faqs as $cat => $value) { ?>

    <div class="faqs-group">

      <h2 class="faqs-heading" id="<?php echo formatHashSlug($cat); ?>"><?php echo $cat; ?></h2>

      <dl class="faqs">
      <?php

      foreach ($faqs[$cat] as $faq => $value) {

          $question = get_field('faq_question', $faqs[$cat][$faq]->ID); ?>
         <div itemprop="mainEntity" itemscope itemtype="https://schema.org/Question">
          <dt class="faq-question l-row" id="<?php echo formatHashSlug($question); ?>">
            <div itemprop="name"><?php echo $question; ?> <i class="fas fa-plus-square"></i></div>
          </dt>
          <dd class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
            <div itemprop="text"><?php the_field('faq_answer', $faqs[$cat][$faq]->ID); ?></div>
          </dd>
      </div>
      <?php } ?>

      </dl>
    </div>

  <?php } ?>

</section>
