<?php 

\date_default_timezone_set('America/Chicago');

// 9 === 9am 
// 17 === 5pm 

// 6 === Saturday 
// 7 === Sunday 

$day_of_week = date('N');
$hour_of_day = date('G');

$is_before_9_or_after_5 = ($hour_of_day < 9 || $hour_of_day >= 17);
$is_sat_or_sun = in_array($day_of_week, array(6,7));

if ($is_sat_or_sun || $is_before_9_or_after_5) {
    $is_available = 0;
    
} else {
    $is_available = 1;
}

?>

<section class="component component-support">
    <div class="l-contain">

        <div class="l-row l-row-center"> 

            <div class="support-triage">
                <div class="support-status-wrapper" data-is-available="<?= $is_available; ?>">

                    <?php if ($is_available) { ?>
                        <p class="support-status">Current status: <b>Available</b></p>
                    <?php } ?>
                    

                    <div class="support-hours">
                        <p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M255.4 48.2c.2-.1 .4-.2 .6-.2s.4 .1 .6 .2L460.6 194c2.1 1.5 3.4 3.9 3.4 6.5v13.6L291.5 355.7c-20.7 17-50.4 17-71.1 0L48 214.1V200.5c0-2.6 1.2-5 3.4-6.5L255.4 48.2zM48 276.2L190 392.8c38.4 31.5 93.7 31.5 132 0L464 276.2V456c0 4.4-3.6 8-8 8H56c-4.4 0-8-3.6-8-8V276.2zM256 0c-10.2 0-20.2 3.2-28.5 9.1L23.5 154.9C8.7 165.4 0 182.4 0 200.5V456c0 30.9 25.1 56 56 56H456c30.9 0 56-25.1 56-56V200.5c0-18.1-8.7-35.1-23.4-45.6L284.5 9.1C276.2 3.2 266.2 0 256 0z"/></svg> <b>Support Hours:</b> Monday&ndash;Friday, 9:00am&ndash;5:00pm CST</p>
                    </div>
                    
                </div>
                <p>Before <a href="#new-ticket">opening a ticket</a>, please look through the resources below. Solutions to the most common problems can be found in our <a href="https://docs.wpshop.io" target="_blank">documentation</a>.</p>
                <p>Customers also gain access to our <a href="/purchase/">private Slack channel</a> to chat with Andrew directly (creator of ShopWP). Simply <a href="/login">login to your ShopWP account</a> and the link will be inside your dashboard.</p>
            </div>

            <div class="support-common">
                <h2>Common resources</h2>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M48 24C48 10.7 37.3 0 24 0S0 10.7 0 24V64 350.5 400v88c0 13.3 10.7 24 24 24s24-10.7 24-24V388l80.3-20.1c41.1-10.3 84.6-5.5 122.5 13.4c44.2 22.1 95.5 24.8 141.7 7.4l34.7-13c12.5-4.7 20.8-16.6 20.8-30V66.1c0-23-24.2-38-44.8-27.7l-9.6 4.8c-46.3 23.2-100.8 23.2-147.1 0c-35.1-17.6-75.4-22-113.5-12.5L48 52V24zm0 77.5l96.6-24.2c27-6.7 55.5-3.6 80.4 8.8c54.9 27.4 118.7 29.7 175 6.8V334.7l-24.4 9.1c-33.7 12.6-71.2 10.7-103.4-5.4c-48.2-24.1-103.3-30.1-155.6-17.1L48 338.5v-237z"/></svg>
                <ul class="support-links card l-list">
                    <li>
                        <a class="l-row" href="https://docs.wpshop.io/getting-started/installing" target="_blank">
                            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#f4bc41" d="M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288H175.5L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7H272.5L349.4 44.6z"/></svg>
                            Getting started
                        </a>
                    </li>
                    <li>
                        <a class="l-row" href="https://docs.wpshop.io/getting-started/syncing#common-syncing-issues" target="_blank">
                            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#f4bc41" d="M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288H175.5L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7H272.5L349.4 44.6z"/></svg> 
                            Fixing syncing issues
                        </a>
                    </li>
                    <li>
                        <a class="l-row" href="https://docs.wpshop.io/shortcodes/wps_products" target="_blank">
                            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#f4bc41" d="M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288H175.5L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7H272.5L349.4 44.6z"/></svg> 
                            Using the shortcodes
                        </a>
                    </li>
                    <li>
                        <a class="l-row" href="https://docs.wpshop.io/getting-started/requirements" target="_blank">
                            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#f4bc41" d="M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288H175.5L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7H272.5L349.4 44.6z"/></svg> 
                            Requirements
                        </a>
                    </li>
                    <li>
                        <a class="l-row" href="https://docs.wpshop.io/getting-started/requirements#known-plugin-conflicts" target="_blank">
                            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#f4bc41" d="M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288H175.5L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7H272.5L349.4 44.6z"/></svg> 
                            Known issues
                        </a>
                    </li>
                </ul>
            </div>

        </div>
                
        <div class="support-ticket" id="new-ticket">
            <div class="l-contain-s">
                <div class="support-ticket-heading l-text-center">
                    <h3>Submit a support ticket</h3>
                    <p>We do our best to respond same day. Please provide as much detail as you can so we can help solve your problem faster.</p>
                    <p>Already a customer? <a href="/login?redirect=support">Login here</a> to prefill the below form.</p>
                </div>
                <div id="root-support"></div>
            </div>
        </div>

    </div>
</section>