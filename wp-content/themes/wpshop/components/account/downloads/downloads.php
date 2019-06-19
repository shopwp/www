<?php 

$download = edd_get_download('wp-shopify');
$files = edd_get_download_files( $download->ID);
$latest_version = edd_software_licensing()->get_download_version($download->ID);

?>

<section class="component component-account-downloads" data-tab="Downloads">
  <h4 class="component-account-heading">Downloads</h4>

<table id="edd_user_history" class="edd-table">
   <thead>
      <tr class="edd_download_history_row">
         <th class="edd_download_download_name">Download Name</th>
         <th class="edd_download_download_files">Files</th>
      </tr>
   </thead>
   
            <tbody><tr class="edd_download_history_row">
                                 <td class="edd_download_download_name">WP Shopify Pro (<?= $latest_version; ?>)</td>

                                    <td class="edd_download_download_files">
                     
                              <div class="edd_download_file">
                                 <a href="<?= $files[0]['file']; ?>" class="edd_download_file_link" target="_blank">Download WP Shopify Pro <?php $latest_version; ?></a>
                              </div>

                                                   </td>
                              </tr>
               </tbody></table>
  
</section>
