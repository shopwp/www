<?php 

$download = edd_get_download('wp-shopify');
$bb_extension = edd_get_download('beaver-builder');

$bb_extension_files = edd_get_download_files( $bb_extension->ID);
$latest_version_bb = edd_software_licensing()->get_download_version($bb_extension->ID);

$files = edd_get_download_files( $download->ID);
$latest_version = edd_software_licensing()->get_download_version($download->ID);

$wp_shopify_pro = $files[key($files)];
$beaver_builder = $bb_extension_files[key($bb_extension_files)];

$has_purchased_extensions = edd_has_user_purchased(get_current_user_id(), $bb_extension->ID);

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
      <tbody>
         <tr class="edd_download_history_row">
            <td class="edd_download_download_name">ShopWP Pro (<?= $latest_version; ?>)</td>
            <td class="edd_download_download_files">
               <div class="edd_download_file">
                  <a href="<?= $wp_shopify_pro['file']; ?>" class="edd_download_file_link" target="_blank">Download</a>
               </div>
            </td>
         </tr>
         <?php if ($has_purchased_extensions) { ?>
            <tr class="edd_download_history_row_new">
               <td class="edd_download_download_name">Beaver Builder (<?= $latest_version_bb; ?>)</td>
               <td class="edd_download_download_files">
                  <div class="edd_download_file">
                     <a href="<?= $beaver_builder['file']; ?>" class="edd_download_file_link" target="_blank">Download</a>
                  </div>
               </td>
            </tr>
         <?php } ?>
      </tbody>
   </table>
</section>