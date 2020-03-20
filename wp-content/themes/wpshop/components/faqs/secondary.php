<section class="component component-faq-secondary">

   <div class="l-row">

      <div class="faqs-wrap">

         <h2 style="margin-bottom: 1em;">The most common questions</h2>

         <?php 

         $faq_ids = $faqs_selected;

         include(locate_template('components/faqs/content.php'));

         ?>

         <div class="l-row l-row-center">
            <a href="/faq" class="btn btn-l btn-secondary">Read all FAQs <i class="fal fa-long-arrow-right"></i></a>
         </div>
         

      </div>

   
   </div>

</section>
