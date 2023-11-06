<?php 

global $wp;

if ($wp->request === 'account/licenses') {   
   wp_redirect('/account?accountpage=licenses');
   exit;
}

if ($wp->request === 'account/subscriptions') {   
   wp_redirect('/account?accountpage=subscriptions');
   exit;
}

if ($wp->request === 'account/purchases') {   
   wp_redirect('/account?accountpage=purchases');
   exit;
}

if ($wp->request === 'account/downloads') {   
   wp_redirect('/account?accountpage=downloads');
   exit;
}

get_header();

if (is_page('account')) {
    get_template_part('components/account/view');
    exit;
}

get_template_part('components/header');

?>

<main>

<?php 

if (is_404()) {
 get_template_part('components/404');

} else if ( is_front_page() ) {
    get_template_part('components/marquee');
    get_template_part('components/buy-buttons'); 
    get_template_part('components/syncing'); 
    get_template_part('components/release'); 
    get_template_part('components/screenshots'); 
    get_template_part('components/testimonials'); 
    get_template_part('components/features-list');
    // get_template_part('components/features-demo');

} else {

    if (is_home()) {

        get_template_part( 'components/content-blog');

    } else if (is_search()) {
        get_template_part( 'components/content', 'search');

    } else {

        if ( have_posts() ) {

            while ( have_posts() ) :

                the_post();

                get_template_part( 'components/content', get_post_type() );

            endwhile;

        } else {
            get_template_part( 'components/content', 'none' );
        }

        if (have_rows('components')):

            while(have_rows('components')) : the_row();

                if (get_row_layout() == 'component_faqs') {
                    get_template_part('components/faqs/faqs-controller');

                } else if (get_row_layout() == 'component_purchase') {
                    get_template_part('components/pricing');

                } else if (get_row_layout() == 'component_support') {
                    get_template_part('components/support');

                } else if (get_row_layout() == 'component_features_demo') {
                    get_template_part('components/features-demo');

                } else if (get_row_layout() == 'component_changelog') {
                    get_template_part('components/changelog');

                } else if (get_row_layout() == 'component_examples') {
                    get_template_part('components/examples');

                } else if (get_row_layout() == 'component_about') {
                    get_template_part('components/about');

                } else if (get_row_layout() == 'component_slack') {
                    get_template_part('components/slack');

                } else if (get_row_layout() == 'component_video') {
                    get_template_part('components/video');

                } else if (get_row_layout() == 'component_testimonials') {
                    get_template_part('components/testimonials');

                } else if (get_row_layout() == 'component_affiliate_register') {
                    get_template_part('components/affiliate');
                }

            endwhile;

        endif;
    }

    
}

?>

</main>

<?php 

get_footer();

?>