<?php

if (post_password_required()) {
  return;
}

?>

<!-- <div id="disqus_thread"></div>
<script>

    var disqus_config = function () {
      this.page.url = '<?php echo get_permalink(); ?>';
      this.page.identifier = '<?php echo wps_identifier_for_post($post); ?>';
    };

    (function() {

        var d = document, s = d.createElement('script');

        s.src = 'https://wp-shopify.disqus.com/embed.js';

        s.setAttribute('data-timestamp', +new Date());
        (d.head || d.body).appendChild(s);

    })();

</script>
<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript> -->
