<?php
/*
Plugin Name: Theme Updater
Plugin URI: http://webcom.dev.smca.ucf.edu/
Description: Some craziness
Author: Douglas Beck
Version: 0.1
Author URI: http://blurback.com/
*/
	
add_filter('site_transient_update_themes', 'transient_update_themes_filter');
function transient_update_themes_filter($data){
	
	$installed_themes = get_themes( );
	foreach ( (array) $installed_themes as $theme_title => $theme ) {
		
		// Get Theme's URI
		if(isset($theme['Stylesheet Files'][0]) && is_readable($theme['Stylesheet Files'][0])){
			$theme_file = $theme['Stylesheet Files'][0];
			$default_headers = array('UpdateURI' => 'Github Theme URI');
			$theme_data = get_file_data( $theme_file, $default_headers, 'theme' );
			if(empty($theme_data['UpdateURI'])){
				continue;
			}
			$theme['UpdateURI'] = $theme_data['UpdateURI'];
			$theme_key = $theme['Stylesheet'];
		}
		
		// Add Github Theme Updater to return $data and hook into admin
		if(!isset($data->github_response)){
			$data->github_response = array();
		}
		add_action( "after_theme_row_" . $theme['Stylesheet'], 'github_theme_update_row', 11, 2 );
		
		
		// Grab Github Tags
		preg_match(
			'/http(s)?:\/\/github.com\/(?<username>[\w-]+)\/(?<repo>[\w-]+)$/',
			$theme['UpdateURI'],
			$matches);
		if(!isset($matches['username']) or !isset($matches['repo'])){
			$data->github_response[$theme_key]['error'] = 'Incorrect github project url.  Format should be (no trailing slash): <code style="background:#FFFBE4;">https://github.com/&lt;username&gt;/&lt;repo&gt;</code>';
			continue;
		}
		$url = 'https://github.com/api/v2/json/repos/show/' . 
				$matches['username'] . '/' . $matches['repo'] .
				'/tags';
		$raw_response = wp_remote_get($url, array('sslverify' => false, 'timeout' => 10));
		if ( is_wp_error( $raw_response ) ){
			$data->github_response[$theme_key]['error'] = "Error response from " . $url;
			continue;
		}
		$response = json_decode($raw_response['body']);
		if(isset($response->error)){
			$data->github_response[$theme_key]['error'] = sprintf('While <a href="%s" style="color:#BC0B0B;text-decoration:underline;">fetching tags</a> api error</a> "%s"', $url, $response->error);
			continue;
		}
		if(!isset($response->tags) or count(get_object_vars($response->tags)) < 1){
			$data->github_response[$theme_key]['error'] = "Github theme does not have any tags";
			continue;
		}
		
		
		// Sort and get latest tag
		$tags = array_keys(get_object_vars($response->tags));
		usort($tags, "version_compare");
		$newest_tag = array_pop($tags);
		
		
		// check and generate download link
		if(version_compare($theme['Version'],  $newest_tag, '>')){
			// up-to-date!
			continue;
		}
		$download_link = $theme['UpdateURI'] . '/zipball/' . $newest_tag;
		
		
		// new update available, add to $data
		$update = array();
		$update['new_version'] = $newest_tag;
		$update['url']         = $theme['UpdateURI'];
		$update['package']     = $download_link;
		$data->github_response[$theme_key] = $update;
		$data->response[$theme_key] = $update;
		
	}
	
	return $data;
}


function github_theme_update_row( $theme_key, $theme ) {
	$current = get_site_transient( 'update_themes' );
	if ( !isset( $current->github_response[ $theme_key ] ) )
		return false;
	$r = $current->github_response[ $theme_key ];
	$themes_allowedtags = array('a' => array('href' => array(),'title' => array()),'abbr' => array('title' => array()),'acronym' => array('title' => array()),'code' => array(),'em' => array(),'strong' => array());
	$theme_name = wp_kses( $theme['Name'], $themes_allowedtags );
	$details_url = $r['url'];
	$wp_list_table = _get_list_table('WP_MS_Themes_List_Table');
	
	echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';
	if( isset($r['error']) ){
		printf('Error with Github Theme Updater: <span style="color:#BC0B0B">%1$s</span>', $r['error']);
	} else if ( ! current_user_can('update_themes') )
		printf( __('There is a new version of %1$s available. <a href="%2$s" target="blank" title="%3$s">View version %4$s details</a>.'), $theme['Name'], esc_url($details_url), esc_attr($theme['Name']), $r->new_version );
	else if ( empty( $r['package'] ) )
		printf( __('There is a new version of %1$s available. <a href="%2$s" target="blank" title="%3$s">View version %4$s details</a>. <em>Automatic update is unavailable for this plugin.</em>'), $theme['Name'], esc_url($details_url), esc_attr($theme['Name']), $r['new_version'] );
	else
		printf( __('There is a new version of %1$s available. <a href="%2$s" target="blank" title="%3$s">View version %4$s details</a> or <a href="%5$s">update automatically</a>.'), $theme['Name'], esc_url($details_url), esc_attr($theme['Name']), $r['new_version'], self_admin_url('update.php?action=upgrade-github-theme&theme=') . $theme_key);
	do_action( "in_theme_update_message-$theme_key", $theme, $r );
	echo '</div></td></tr>';
}




add_action('update-custom_upgrade-github-theme', 'github_theme_updater', 10, 2);
function github_theme_updater(){
	if ( ! current_user_can('update_themes') )
		wp_die(__('You do not have sufficient permissions to update themes for this site.'));
	
	$theme = isset($_REQUEST['theme']) ? urldecode($_REQUEST['theme']) : '';
	check_admin_referer('upgrade-theme_' . $theme);
	
	add_thickbox();
	wp_enqueue_script('theme-preview');
	$title = __('Update Theme');
	$parent_file = 'themes.php';
	$submenu_file = 'themes.php';
	require_once(ABSPATH . 'wp-admin/admin-header.php');

	$nonce = 'upgrade-theme_' . $theme;
	$url = 'update.php?action=upgrade-theme&theme=' . $theme;

	$upgrader = new Theme_Upgrader( new Theme_Upgrader_Skin( compact('title', 'nonce', 'url', 'theme') ) );
	$upgrader->upgrade($theme);

	include(ABSPATH . 'wp-admin/admin-footer.php');
}