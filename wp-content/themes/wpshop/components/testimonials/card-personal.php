<div class="testimonial grid-item">

   <blockquote class="testimonial-wrapper">
      <footer class="testimonial-footer">
         <a class="testimonials-author-link" href="<?= $testimonial_author_link; ?>">
            <img class="testimonials-author-avatar" src="<?= $testimonial_author_image['url']; ?>" alt="<?= $testimonial_author_image['alt']; ?>" />
            <cite class="testimonial-author" id="baseref">
               <span class="testimonial-author-name"><?= $testimonial_author_name; ?></span> 
               <span class="testimonial-author-title"><?= $testimonial_author_title; ?></span>
            </cite>
         </a>
      </footer>
      <div class="testimonial-content">
         <?= $testimonial_content; ?>
      </div>
   </blockquote>

</div>