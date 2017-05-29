<?php
/**
 * Social Login Data
 * 
 * Handles to show social login data
 * on the page
 * 
 * @package WPSocial Deals Engine
 * @since 1.0.0
 */

	global $edd_slg_model, $edd_options;
	
	//social model class
	$model = $edd_slg_model;
?>

<div class="wrap">
	
	<h2 class="edd-slg-settings-title"><?php _e( 'Social Login', 'eddslg' ); ?></h2><br />
	
	<?php
		//save order of social networks
		if( isset( $_POST['edd-slg-settings-social-submit'] ) && $_POST['edd-slg-settings-social-submit'] == __('Save Changes','eddslg') ) {
			
			$edd_social_order = $_POST['social_order'];
			
			update_option( 'edd_social_order', $edd_social_order );
			
			echo '<div id="message" class="updated fade below-h2">
						<p><strong>'.__( 'Changes Saved.','eddslg').'</strong></p>
				  </div>';
		}
	?>
	
	<form action="" method="POST">
		<h3><?php _e( 'Drag to Change Order', 'eddslg' );?></h3>
		
		<table class="edd-slg-sortable widefat">
			<thead>
				<tr>
					<th width="1%"></th>
					<th width="1%" class="edd-slg-social-none"><?php _e('Chage Order', 'eddslg'); ?></th>
					<?php
							//do action to add header before
							do_action( 'edd_slg_social_table_header_before' );
					?>
					<th><?php _e( 'Network', 'eddslg');?></th>
					<?php
							//do action to add social table header network after
							do_action( 'edd_slg_social_table_header_network_after' );
					?>
					<th><?php _e( 'Register Count', 'eddslg');?></th>
					<?php
							//do action to add social table header after
							do_action( 'edd_slg_social_table_header_after' );
					?>
				</tr>
			</thead>
			<tbody>
				<?php
					//do action to add social table content before
					do_action( 'edd_slg_social_table_content_before' );
					
					//get all social networks
					$allnetworks = edd_slg_get_sorted_social_network();
					
					//register user count
					$regusers = array();
					
					foreach ( $allnetworks as $key => $value ) {
						
						$countargs = array( 
											'getcount' =>	'1',
											'network'	=>	$key 
										  );
						$regusers[$key]['count'] = $model->edd_slg_social_get_users( $countargs );
						$regusers[$key]['label'] = $value;
				?>
						<tr>
							<?php
								//do action to add social table data before
								do_action( 'edd_slg_social_table_data_before', $key, $value );
							?>
							<td><img src="<?php echo EDD_SLG_IMG_URL.'/backend/'.$key.'.png';?>" alt="<?php echo $value;?>" /></td>
							<td width="1%" class="edd-slg-social-none">
								<input type="hidden" name="social_order[]" value="<?php echo $key;?>" />
							</td>
							<?php
								//do action to add social icon after
								do_action( 'edd_slg_social_table_data_icon_after', $key, $value );
							?>
							<td><?php echo $value;?></td>
							<?php
								//do action to add social table data network
								do_action( 'edd_slg_social_table_data_network_after', $key, $value );
							?>
							<td><?php echo $regusers[$key]['count'];?></td>
							<?php
								//do action to add social table data reg count after
								do_action( 'edd_slg_social_table_data_reg_count_after', $key, $value );
							?>
						</tr>
			<?php	
					}
					
					//do action to add social table content after
					do_action( 'edd_slg_social_table_content_after' );
			?>
			</tbody>
			<tfoot>
				<tr>
					<th width="1%"></th>
					<th width="1%" class="edd-slg-social-none"><?php _e('Chage Order', 'eddslg'); ?></th>
					<?php
							//do action to add footer before
							do_action( 'edd_slg_social_table_footer_before' );
					?>
					<th><?php _e( 'Network', 'eddslg');?></th>
					<?php
							//do action to add social table footer network after
							do_action( 'edd_slg_social_table_foooter_network_after' );
					?>
					<th><?php _e( 'Register Count', 'eddslg');?></th>
					<?php
							//do action to add social table footer after
							do_action( 'edd_slg_social_table_footer_after' );
					?>
				</tr>
			</tfoot>
		</table>
		
		<?php
				//do action to add social table after
				do_action( 'edd_slg_social_data_table_after' );
		?>		
		<input type="submit" id="edd-slg-settings-social-submit" name="edd-slg-settings-social-submit" class="edd-slg-social-submit button-primary" value="<?php _e('Save Changes','eddslg');?>" />
	</form>
	<?php
		$colors = array(
						'facebook'		=>	'A200C2',
						'twitter'		=>	'46c0FB',
						'googleplus'	=>	'0083A8',
						'linkedin'		=>	'4E6CF7',
						'yahoo'			=>	'4863AE',
						'foursquare'	=>	'44A8E0',
						'vk'			=>	'4A63A3',
						'instagram'		=>	'A67C66'
					);
		//applying filter for chart color
		$colors = apply_filters( 'edd_slg_social_chart_colors', $colors );
		
		foreach( $regusers as $key => $val ){
			if( $val['count']== 0 ){
				unset( $regusers[$key] );
				unset( $colors[$key] );
			}
		}
	?>
	<script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback( EddSlgDrawChart );

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function EddSlgDrawChart() {
		
	      	<?php
	      		$datarows = '';
	      		foreach( $regusers as $key => $val ){
					$count = $val['count'];
					$datarows .= "['".$val['label']."', $count], ";
				}
				$datarows = trim( $datarows, ',');
	      	?>
      	
	        // Create the data table.
	        var deals_social_data = new google.visualization.DataTable();
	        deals_social_data.addColumn('string', 'Topping');
	        deals_social_data.addColumn('number', 'Slices');
	        deals_social_data.addRows([<?php echo $datarows;?>]);
	        
	        // Set chart options
	        var deals_social_chart_options = {
	        				'title':'<?php _e('Social Networks Register Percentage ', 'eddslg'); ?>',
	                       	'width':650,
	                       	'height':450
	        			};
	
	        // Instantiate and draw our chart, passing in some options.
	        var deals_social_chart = new google.visualization.PieChart(document.getElementById('edd_slg_social_chart_element'));
	        deals_social_chart.draw(deals_social_data, deals_social_chart_options );
      }
    </script>
	<div id="edd_slg_social_chart_element" class="edd-slg-social-chart-container"></div><!--.edd-slg-social-chart-container-->
	
</div><!--wrap-->