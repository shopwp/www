<?php get_template_part('templates/page', 'header'); ?>

<div class="alert alert-warning">
  <?php _e('Woops, looks like that page doesn\'t exist. If you\'re having trouble finding what you\'re looking for <a href="https://join.slack.com/wpshopify/shared_invite/MTg5OTQxODEwOTM1LTE0OTU5ODY2MTktN2Y1ODk0YzBlNg">join our Slack channel</a> and you\'ll be helped as soon as possible!', 'sage'); ?>
</div>

<?php get_search_form();
