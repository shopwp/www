<?php
/*
Plugin Name: Plugin Organizer MU
Plugin URI: http://www.sterupdesign.com
Description: A plugin for specifying the load order of your plugins.
Version: 10.1.3
Author: Jeff Sterup
Author URI: http://www.sterupdesign.com
License: GPL2
*/

class PluginOrganizerMU {
	var $ignoreProtocol, $ignoreArguments, $requestedPermalink, $postTypeSupport, $debugMsg;
	var $protocol, $mobile, $detectMobile, $requestedPermalinkHash, $permalinkSearchField, $secure;
	function __construct() {
		$this->ignoreProtocol = get_option('PO_ignore_protocol');
		$this->ignoreArguments = get_option('PO_ignore_arguments');
		$this->postTypeSupport = get_option('PO_custom_post_type_support');
		$this->postTypeSupport[] = 'plugin_filter';
		$this->detectMobile = get_option('PO_disable_plugins_mobile');
		$this->secure=0;
		$this->debugMsg=array();
		$this->adminMsg=array();
		if ($this->detectMobile == 1) {
			$this->detect_mobile();
		}
	}
	
	function disable_plugins($pluginList, $networkPlugin=0) {
		global $wpdb, $pagenow;
		$newPluginList = array();
		if (is_array($pluginList) && get_option("PO_disable_plugins_frontend") == "1" && (get_option('PO_disable_plugins_admin') == "1" || !is_admin())) {
			$displayDebugMsg = get_option('PO_display_debug_msg');
			
			if ($displayDebugMsg == 1) {
				$roleNames = array('_'=>'Not Logged In', '-'=>'Default Logged In');
				if (is_multisite()) {
					if ($networkPlugin == 0) {
						$this->debugMsg[] ='Checking standard plugins -- START.';
					} else {
						$this->debugMsg[] ='Checking network plugins -- START.';
					}
				}

				if ($this->detectMobile == 1 && $this->mobile) {
					$this->debugMsg[] ='A mobile browser has been detected.';
				}
			}
			
			$assignedRoles = array('_');
			if (get_option("PO_disable_plugins_by_role") == '1') {
				if (@count(@preg_grep('/^wordpress_logged_in/', @array_keys($_COOKIE))) > 0) {
					if (isset($_COOKIE['po_assigned_roles']) && is_array($_COOKIE['po_assigned_roles'])) {
						$assignedRoles = $_COOKIE['po_assigned_roles'];
						$assignedRoles[] = '-';
					} else {
						$assignedRoles = array('-');
					}
				}

				$enabledRoles = get_option("PO_enabled_roles");
				if (is_array($enabledRoles)) {
					$enabledRoles[] = '-';
					$enabledRoles[] = '_';
				} else {
					$enabledRoles = array('-', '_');
				}
				$assignedRoles = array_intersect($enabledRoles, $assignedRoles);
			}
			
			$this->set_requested_permalink();
			if (get_option('PO_updating_plugin') != '1' && get_option("PO_version_num") != "10.1.3") {
				$newPluginList = $pluginList;
				$this->adminMsg[] = '<strong>WARNING:</strong> Selective plugin loading for Plugin Organizer has been disabled because the version numbers of the MU plugin and the standard plugin don\'t match.<br />The current version number returned from the database is '.get_option("PO_version_num").' and the current MU plugin version number is 10.1.3.<br />If you are using a caching plugin try clearing the cache.';
			} else {
				$sql = "SELECT disabled_plugins, disabled_mobile_plugins, disabled_groups, disabled_mobile_groups FROM ".$wpdb->prefix."po_plugins WHERE post_type='global_plugin_lists' AND post_id=0";
				$storedPluginLists = $wpdb->get_row($sql, ARRAY_A);
				
				if ($this->detectMobile == 1 && $this->mobile) {
					$globalPlugins = (is_array(@unserialize($storedPluginLists['disabled_mobile_plugins'])))? @unserialize($storedPluginLists['disabled_mobile_plugins']):array();
					$globalGroups = (is_array(@unserialize($storedPluginLists['disabled_mobile_groups'])))? @unserialize($storedPluginLists['disabled_mobile_groups']):array();
				} else {
					$globalPlugins = (is_array(@unserialize($storedPluginLists['disabled_plugins'])))? @unserialize($storedPluginLists['disabled_plugins']):array();
					$globalGroups = (is_array(@unserialize($storedPluginLists['disabled_groups'])))? @unserialize($storedPluginLists['disabled_groups']):array();
				}

				##Search page
				if (!is_admin() && isset($_REQUEST['s'])) {
					$sql = "SELECT disabled_plugins, enabled_plugins, disabled_mobile_plugins, enabled_mobile_plugins, disabled_groups, enabled_groups, disabled_mobile_groups, enabled_mobile_groups, user_role FROM ".$wpdb->prefix."po_plugins WHERE post_type='search_plugin_lists' AND post_id=0 AND user_role IN ([R_IN]) ORDER BY FIELD(user_role, [R_IN])";
					$sql = $this->prepare_in($sql, $assignedRoles, '[R_IN]');
					$storedPluginLists = $wpdb->get_row($sql, ARRAY_A);
					
					if ($this->detectMobile == 1 && $this->mobile) {
						$disabledPlugins = (is_array(@unserialize($storedPluginLists['disabled_mobile_plugins'])))? @unserialize($storedPluginLists['disabled_mobile_plugins']):array();
						$enabledPlugins = (is_array(@unserialize($storedPluginLists['enabled_mobile_plugins'])))? @unserialize($storedPluginLists['enabled_mobile_plugins']):array();
						$disabledGroups = (is_array(@unserialize($storedPluginLists['disabled_mobile_groups'])))? @unserialize($storedPluginLists['disabled_mobile_groups']):array();
						$enabledGroups = (is_array(@unserialize($storedPluginLists['enabled_mobile_groups'])))? @unserialize($storedPluginLists['enabled_mobile_groups']):array();
					} else {
						$disabledPlugins = (is_array(@unserialize($storedPluginLists['disabled_plugins'])))? @unserialize($storedPluginLists['disabled_plugins']):array();
						$enabledPlugins = (is_array(@unserialize($storedPluginLists['enabled_plugins'])))? @unserialize($storedPluginLists['enabled_plugins']):array();
						$disabledGroups = (is_array(@unserialize($storedPluginLists['disabled_groups'])))? @unserialize($storedPluginLists['disabled_groups']):array();
						$enabledGroups = (is_array(@unserialize($storedPluginLists['enabled_groups'])))? @unserialize($storedPluginLists['enabled_groups']):array();
					}
					$detectedRole = $storedPluginLists['user_role'];
						
				}

				$disabledPlugins = (isset($disabledPlugins) && is_array($disabledPlugins))? $disabledPlugins : array();
				$enabledPlugins = (isset($enabledPlugins) && is_array($enabledPlugins))? $enabledPlugins : array();
				$disabledGroups = (isset($disabledGroups) && is_array($disabledGroups))? $disabledGroups : array();
				$enabledGroups = (isset($enabledGroups) && is_array($enabledGroups))? $enabledGroups : array();
				
				if (sizeof($disabledPlugins) == 0 && sizeof($enabledPlugins) == 0 && sizeof($disabledGroups) == 0 && sizeof($enabledGroups) == 0) {
					
					if ($this->ignoreProtocol == '0') {
						$requestedPostQuery = "SELECT * FROM ".$wpdb->prefix."po_plugins WHERE ".$this->permalinkSearchField." = %s AND status IN ('publish','private') AND secure = %d AND post_type IN ([IN]) AND user_role IN ([R_IN]) ORDER BY FIELD(post_type, [IN]), FIELD(user_role, [R_IN]), post_priority DESC";
						$requestedPostQuery = $wpdb->prepare($requestedPostQuery, $this->requestedPermalinkHash, $this->secure);
						$requestedPostQuery = $this->prepare_in($requestedPostQuery, $assignedRoles, '[R_IN]');
						$requestedPost = $wpdb->get_results($this->prepare_in($requestedPostQuery, $this->postTypeSupport, '[IN]'), ARRAY_A);
					} else {
						$requestedPostQuery = "SELECT * FROM ".$wpdb->prefix."po_plugins WHERE ".$this->permalinkSearchField." = %s AND status IN ('publish','private') AND post_type IN ([IN]) AND user_role IN ([R_IN]) ORDER BY FIELD(post_type, [IN]), FIELD(user_role, [R_IN]), post_priority DESC";
						$requestedPostQuery = $wpdb->prepare($requestedPostQuery, $this->requestedPermalinkHash);
						$requestedPostQuery = $this->prepare_in($requestedPostQuery, $assignedRoles, '[R_IN]');
						$requestedPost = $wpdb->get_results($this->prepare_in($requestedPostQuery, $this->postTypeSupport, '[IN]'), ARRAY_A);
					}
					
					if (!is_array($requestedPost)) {
						$requestedPost = array();
					}
					
					$disabledPlugins = array();
					$enabledPlugins = array();
					$disabledGroups = array();
					$enabledGroups = array();
					foreach($requestedPost as $currPost) {
						if ($this->detectMobile == 1 && $this->mobile) {
							$disabledPlugins = @unserialize($currPost['disabled_mobile_plugins']);
							$enabledPlugins = @unserialize($currPost['enabled_mobile_plugins']);
							$disabledGroups = @unserialize($currPost['disabled_mobile_groups']);
							$enabledGroups = @unserialize($currPost['enabled_mobile_groups']);
						} else {
							$disabledPlugins = @unserialize($currPost['disabled_plugins']);
							$enabledPlugins = @unserialize($currPost['enabled_plugins']);
							$disabledGroups = @unserialize($currPost['disabled_groups']);
							$enabledGroups = @unserialize($currPost['enabled_groups']);
						}
						if ((is_array($disabledPlugins) && sizeof($disabledPlugins) > 0) || (is_array($enabledPlugins) && sizeof($enabledPlugins) > 0) || (is_array($disabledGroups) && sizeof($disabledGroups) > 0) || (is_array($enabledGroups) && sizeof($enabledGroups) > 0)) {
							if ($displayDebugMsg == 1) {
								$this->debugMsg[] = 'An exact match to the URL has been found'.((get_option("PO_disable_plugins_by_role") == '1')? ' with the '.((array_key_exists($currPost['user_role'], $roleNames))?$roleNames[$currPost['user_role']]:$currPost['user_role'].' role').' settings':'').'. You can edit the plugin list affecting this page <a href="' . get_admin_url() . 'post.php?post=' . $currPost['post_id'] . '&action=edit" target="_blank">HERE</a>';
							}
							break;
						}
					}
				} else if ($displayDebugMsg == 1) {
					$this->debugMsg[] = 'This page has been detected as a search result and the search plugin lists are affecting it'.((get_option("PO_disable_plugins_by_role") == '1')? ' with the '.((array_key_exists($detectedRole, $roleNames))?$roleNames[$detectedRole]:$detectedRole.' role'):' settings').'.';
				}
				
				$disabledPlugins = (!is_array($disabledPlugins))? array() : $disabledPlugins;
				$enabledPlugins = (!is_array($enabledPlugins))? array() : $enabledPlugins;
				$disabledGroups = (!is_array($disabledGroups))? array() : $disabledGroups;
				$enabledGroups = (!is_array($enabledGroups))? array() : $enabledGroups;
				
				if (get_option("PO_fuzzy_url_matching") == "1" && sizeof($disabledPlugins) == 0 && sizeof($enabledPlugins) == 0 && sizeof($disabledGroups) == 0 && sizeof($enabledGroups) == 0) {
					$endChar = (preg_match('/\/$/', get_option('permalink_structure')) || is_admin())? '/':'';
					$lastUrl = $_SERVER['HTTP_HOST'].$endChar;
					
					$fuzzyPost = array();
					//Dont allow an endless loop
					$loopCount = 0;
	
					$permalinkHashes = array();
					$previousIndex = 8;
					$lastOcc = strrpos($this->requestedPermalink, "/");
					while ($loopCount < 25 && $previousIndex < $lastOcc) {
						$startReplace = strpos($this->requestedPermalink, '/', $previousIndex);
						$endReplace = strpos($this->requestedPermalink, '/', $startReplace+1);
						if ($endReplace === false) {
							$endReplace = strlen($this->requestedPermalink);
						}
						$permalinkHashes[] = $wpdb->prepare('%s', md5(substr_replace($this->requestedPermalink, "/*/", $startReplace, ($endReplace-$startReplace)+1)));
						$previousIndex = $endReplace;
						$loopCount++;
					}

					if (sizeof($permalinkHashes) > 0) {
						if ($this->ignoreProtocol == '0') {
							$fuzzyPostQuery = "SELECT * FROM ".$wpdb->prefix."po_plugins WHERE (".$this->permalinkSearchField." = ".implode(" OR ".$this->permalinkSearchField." = ", $permalinkHashes).") AND status IN ('publish','private') AND secure = %d AND post_type IN ([IN]) AND user_role IN ([R_IN]) ORDER BY dir_count DESC, FIELD(post_type, [IN]), FIELD(user_role, [R_IN]), post_priority DESC";
							$fuzzyPostQuery = $wpdb->prepare($fuzzyPostQuery, $this->secure);
							$fuzzyPostQuery = $this->prepare_in($fuzzyPostQuery, $assignedRoles, '[R_IN]');
							$fuzzyPost = $wpdb->get_results($this->prepare_in($fuzzyPostQuery, $this->postTypeSupport, '[IN]'), ARRAY_A);
						} else {
							$fuzzyPostQuery = "SELECT * FROM ".$wpdb->prefix."po_plugins WHERE (".$this->permalinkSearchField." = ".implode(" OR ".$this->permalinkSearchField." = ", $permalinkHashes).") AND status IN ('publish','private') AND post_type IN ([IN]) AND user_role IN ([R_IN]) ORDER BY dir_count DESC, FIELD(post_type, [IN]), FIELD(user_role, [R_IN]), post_priority DESC";
							$fuzzyPostQuery = $this->prepare_in($fuzzyPostQuery, $assignedRoles, '[R_IN]');
							$fuzzyPost = $wpdb->get_results($this->prepare_in($fuzzyPostQuery, $this->postTypeSupport, '[IN]'), ARRAY_A);
						}
					}
					
					#print $this->prepare_in($fuzzyPostQuery, $this->postTypeSupport);
					if (sizeof($fuzzyPost) == 0) {
						$permalinkHashes = array();
						$loopCount = 0;
						while ($loopCount < 25 && $this->requestedPermalink != $lastUrl && ($this->requestedPermalink = preg_replace('/\/[^\/]+\/?$/', $endChar, $this->requestedPermalink))) {
							$loopCount++;
							$this->requestedPermalinkHash = $wpdb->prepare('%s', md5($this->requestedPermalink));
							$permalinkHashes[] = $this->requestedPermalinkHash;

							$innerLoopCount = 0;
							$previousIndex = 8;
							$lastOcc = strrpos($this->requestedPermalink, "/");
							while ($innerLoopCount < 25 && $previousIndex < $lastOcc) {
								$startReplace = strpos($this->requestedPermalink, '/', $previousIndex);
								$endReplace = strpos($this->requestedPermalink, '/', $startReplace+1);
								if ($endReplace === false) {
									$endReplace = strlen($this->requestedPermalink);
								}
								$permalinkHashes[] = $wpdb->prepare('%s', md5(substr_replace($this->requestedPermalink, "/*/", $startReplace, ($endReplace-$startReplace)+1)));
								$previousIndex = $endReplace;
								$innerLoopCount++;
							}
						}
						
						if (sizeof($permalinkHashes) > 0) {
							if ($this->ignoreProtocol == '0') {
								$fuzzyPostQuery = "SELECT * FROM ".$wpdb->prefix."po_plugins WHERE (permalink_hash = ".implode(" OR permalink_hash = ", $permalinkHashes).") AND status IN ('publish','private') AND secure = %d AND children = 1 AND post_type IN ([IN]) AND user_role IN ([R_IN]) ORDER BY dir_count DESC, FIELD(post_type, [IN]), FIELD(user_role, [R_IN]), post_priority DESC";
								$fuzzyPostQuery = $wpdb->prepare($fuzzyPostQuery, $this->secure);
								$fuzzyPostQuery = $this->prepare_in($fuzzyPostQuery, $assignedRoles, '[R_IN]');
								$fuzzyPost = $wpdb->get_results($this->prepare_in($fuzzyPostQuery, $this->postTypeSupport, '[IN]'), ARRAY_A);
							} else {
								$fuzzyPostQuery = "SELECT * FROM ".$wpdb->prefix."po_plugins WHERE (permalink_hash = ".implode(" OR permalink_hash = ", $permalinkHashes).") AND status IN ('publish','private') AND children = 1 AND post_type IN ([IN]) AND user_role IN ([R_IN]) ORDER BY dir_count DESC, FIELD(post_type, [IN]), FIELD(user_role, [R_IN]), post_priority DESC";
								$fuzzyPostQuery = $this->prepare_in($fuzzyPostQuery, $assignedRoles, '[R_IN]');
								$fuzzyPost = $wpdb->get_results($this->prepare_in($fuzzyPostQuery, $this->postTypeSupport, '[IN]'), ARRAY_A);
							}
						}
					}

						
					#print $this->prepare_in($fuzzyPostQuery, $this->postTypeSupport);
					#print_r($fuzzyPost);
					$matchFound = 0;
					if (sizeof($fuzzyPost) > 0) {
						foreach($fuzzyPost as $currPost) {
							if ($this->detectMobile == 1 && $this->mobile) {
								$disabledFuzzyPlugins = @unserialize($currPost['disabled_mobile_plugins']);
								$enabledFuzzyPlugins = @unserialize($currPost['enabled_mobile_plugins']);
								$disabledFuzzyGroups = @unserialize($currPost['disabled_mobile_groups']);
								$enabledFuzzyGroups = @unserialize($currPost['enabled_mobile_groups']);
							} else {
								$disabledFuzzyPlugins = @unserialize($currPost['disabled_plugins']);
								$enabledFuzzyPlugins = @unserialize($currPost['enabled_plugins']);
								$disabledFuzzyGroups = @unserialize($currPost['disabled_groups']);
								$enabledFuzzyGroups = @unserialize($currPost['enabled_groups']);
							}
							if ((is_array($disabledFuzzyPlugins) && sizeof($disabledFuzzyPlugins) > 0) || (is_array($enabledFuzzyPlugins) && sizeof($enabledFuzzyPlugins) > 0) || (is_array($disabledFuzzyGroups) && sizeof($disabledFuzzyGroups) > 0) || (is_array($enabledFuzzyGroups) && sizeof($enabledFuzzyGroups) > 0)) {
								$matchFound = 1;
								if ($displayDebugMsg == 1) {
									$this->debugMsg[] = 'A match has been made to this url using Fuzzy URL Matching'.((get_option("PO_disable_plugins_by_role") == '1')? ' with the '.((array_key_exists($currPost['user_role'], $roleNames))?$roleNames[$currPost['user_role']]:$currPost['user_role'].' role').' settings':'').'. You can edit the plugin list affecting this page <a href="' . get_admin_url() . 'post.php?post=' . $currPost['post_id'] . '&action=edit" target="_blank">HERE</a>';
								}
								break;
							}
						}
						
						if ($matchFound > 0) {
							if (!is_array($disabledFuzzyPlugins)) {
								$disabledFuzzyPlugins = array();
							}

							if (!is_array($enabledFuzzyPlugins)) {
								$enabledFuzzyPlugins = array();
							}

							if (!is_array($disabledFuzzyGroups)) {
								$disabledFuzzyGroups = array();
							}

							if (!is_array($enabledFuzzyGroups)) {
								$enabledFuzzyGroups = array();
							}

							$disabledPlugins = $disabledFuzzyPlugins;
							$enabledPlugins = $enabledFuzzyPlugins;
							$disabledGroups = $disabledFuzzyGroups;
							$enabledGroups = $enabledFuzzyGroups;
						}
					}
				}

				$disabledGroupMembers = array();
				$enabledGroupMembers = array();
				if (is_array($disabledGroups)) {
					foreach($disabledGroups as $group) {
						$groupMembers = get_post_meta($group, '_PO_group_members', $single=true);
						if (!is_array($groupMembers)) {
							$groupMembers = array();
						}
						$disabledGroupMembers = array_merge($disabledGroupMembers, $groupMembers);
					}
				}

				if (is_array($enabledGroups)) {
					foreach($enabledGroups as $group) {
						$groupMembers = get_post_meta($group, '_PO_group_members', $single=true);
						if (!is_array($groupMembers)) {
							$groupMembers = array();
						}
						$enabledGroupMembers = array_merge($enabledGroupMembers, $groupMembers);
					}
				}
				$disabledGroupMembers = array_unique($disabledGroupMembers);
				$enabledGroupMembers = array_unique($enabledGroupMembers);
				

				foreach($disabledGroupMembers as $groupMember) {
					if (!in_array($groupMember, $disabledPlugins)) {
						$disabledPlugins[] = $groupMember;
					}
				}
				
				foreach($enabledGroupMembers as $groupMember) {
					if (!in_array($groupMember, $enabledPlugins)) {
						$enabledPlugins[] = $groupMember;
					}
				}


				if (is_array($globalPlugins) && sizeOf($globalPlugins) > 0) {
					if ($displayDebugMsg == 1) {
						$this->debugMsg[] = 'The global plugin lists are affecting this URL.';
					}
					foreach ($pluginList as $plugin) {
						if (in_array($plugin, $globalPlugins) && (!preg_match('/plugin-organizer.php$/', $plugin) || (!is_admin() && (!isset($_SERVER['SCRIPT_NAME']) || $_SERVER['SCRIPT_NAME'] != '/wp-login.php')))) {
							if (in_array($plugin, $enabledPlugins)) {
								$newPluginList[] = $plugin;
							}
						} else {
							$newPluginList[] = $plugin;
						}
					}
					$pluginList = $newPluginList;
					$newPluginList = array();
				}

				if (is_array($globalGroups) && sizeOf($globalGroups) > 0) {
					if ($displayDebugMsg == 1) {
						$this->debugMsg[] = 'The global plugin groups are affecting this URL.';
					}
					foreach($globalGroups as $group) {
						$groupMembers = get_post_meta($group, '_PO_group_members', $single=true);
						if (!is_array($groupMembers)) {
							$groupMembers = array();
						}
						
						foreach ($pluginList as $plugin) {
							if (in_array($plugin, $groupMembers) && (!preg_match('/plugin-organizer.php$/', $plugin) || (!is_admin() && (!isset($_SERVER['SCRIPT_NAME']) || $_SERVER['SCRIPT_NAME'] != '/wp-login.php')))) {
								if (in_array($plugin, $enabledPlugins)) {
									$newPluginList[] = $plugin;
								}
							} else {
								$newPluginList[] = $plugin;
							}
						}
						$pluginList = $newPluginList;
						$newPluginList = array();
					}
				}

				
				
				if (is_array($disabledPlugins)) {
					if (is_admin() || (isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] == '/wp-login.php')) {
						foreach ($disabledPlugins as $key=>$plugin) {
							if (preg_match('/plugin-organizer.php$/', $plugin)) {
								unset($disabledPlugins[$key]);
							}
						}
					}
					foreach ($pluginList as $plugin) {
						if (!in_array($plugin, $disabledPlugins)) {
							$newPluginList[] = $plugin;
						}
					}
				} else {
					$newPluginList = $pluginList;
				}
			}

			if ($displayDebugMsg == 1) {
				$this->debugMsg[] = "-------  Enabled Plugins  -------";
				foreach($newPluginList as $enabledPlugin) {
					$this->debugMsg[] = $enabledPlugin;
				}
				$this->debugMsg[] = "---------------------------------";
			}

			if (is_multisite() && $displayDebugMsg == 1) {
				if ($networkPlugin == 0) {
					$this->debugMsg[] ='Checking standard plugins -- END.';
					$this->debugMsg[] ='<hr>';
				} else {
					$this->debugMsg[] ='Checking network plugins -- END.';
					$this->debugMsg[] ='<hr>';
				}
			}
		} else {
			$newPluginList = $pluginList;
		}
		return $newPluginList;
	}
	
	function disable_network_plugins($pluginList) {
		$newPluginList = array();
		if (is_array($pluginList) && sizeOf($pluginList) > 0) {
			remove_filter('option_active_plugins', array($this, 'disable_plugins'), 1, 1);
			$activePlugins = get_option('active_plugins');
			add_filter('option_active_plugins', array($this, 'disable_plugins'), 1, 1);
			$tempPluginList = array_keys($pluginList);
			$tempPluginList = $this->disable_plugins($tempPluginList, 1);
			$newPluginList = array();
			$newPluginListOrder = array();
			foreach($tempPluginList as $pluginFile) {
				$newPluginList[$pluginFile] = $pluginList[$pluginFile];
				$newPluginListOrder[] = array_search($pluginFile, $activePlugins);
			}
			array_multisort($newPluginListOrder, $newPluginList);
		}
		
		return $newPluginList;
	}

	function set_requested_permalink() {
		if ($this->ignoreArguments == '1') {
			$splitPath = explode('?', $_SERVER['REQUEST_URI']);
			$requestedPath = $splitPath[0];
			$this->permalinkSearchField = 'permalink_hash';
		} else {
			$requestedPath = $_SERVER['REQUEST_URI'];
			$this->permalinkSearchField = 'permalink_hash_args';
		}
		
		$this->requestedPermalink = $_SERVER['HTTP_HOST'].$requestedPath;
		$this->requestedPermalinkHash = md5($this->requestedPermalink);

		if ($this->ignoreProtocol == '0') {
			$this->secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;
		} else {
			$this->secure = 0;
		}


	}

	function detect_mobile() {
		$userAgent = (isset($_SERVER['HTTP_USER_AGENT']))? $_SERVER['HTTP_USER_AGENT']:'';
		$mobileAgents = get_option('PO_mobile_user_agents');
		if (!is_array($mobileAgents)) {
			$mobileAgents = array();
		}
		$this->mobile = false;

		foreach ( $mobileAgents as $agent ) {
			if ( $agent != "" && stripos($userAgent, $agent) !== FALSE ) {
				$this->mobile = true;
				break;
			}
		}
	}

	function prepare_in($sql, $vals, $replaceText='[IN]'){
		global $wpdb;
		$in_count = substr_count($sql, $replaceText);
		if ( $in_count > 0 ){
			$args = array( str_replace($replaceText, implode(', ', array_fill(0, count($vals), '%s')), str_replace('%', '%%', $sql)));
			// This will populate ALL the [IN]'s with the $vals, assuming you have more than one [IN] in the sql
			for ($i=0; $i < substr_count($sql, $replaceText); $i++) {
				$args = array_merge($args, $vals);
			}
			$sql = call_user_func_array(array($wpdb, 'prepare'), array_merge($args));
		}
		return $sql;
	}

	function hack_file_filter($hackFile) {
		remove_action('plugins_loaded', array($this, 'remove_plugin_filters'), 1);
		$this->remove_plugin_filters();
		return $hackFile;
	}
	
	function remove_plugin_filters() {
		remove_filter('option_active_plugins', array($this, 'disable_plugins'), 1, 1);
		remove_filter('site_option_active_sitewide_plugins', array($this, 'disable_network_plugins'), 1, 1);
	}
}
$PluginOrganizerMU = new PluginOrganizerMU();

add_filter('option_active_plugins', array($PluginOrganizerMU, 'disable_plugins'), 1, 1);

add_filter('site_option_active_sitewide_plugins', array($PluginOrganizerMU, 'disable_network_plugins'), 1, 1);

add_filter('option_hack_file', array($PluginOrganizerMU, 'remove_plugin_filters'), 1);
add_action('plugins_loaded', array($PluginOrganizerMU, 'remove_plugin_filters'), 1);

?>