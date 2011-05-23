<?php
/*
Plugin Name: Theme Updater
Plugin URI: http://webcom.dev.smca.ucf.edu/
Description: A theme updater for GitHub hosted Wordpress themes.  This Wordpress plugin automatically checks GitHub for theme updates and enables automatic install.  For more information read <a href="https://github.com/UCF/Theme-Updater/blob/master/readme.markdown">plugin documentation</a>.
Author: Douglas Beck
Version: 0.1
*/
require_once('assets.php');

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
		remove_action( "after_theme_row_" . $theme['Stylesheet'], 'wp_theme_update_row');
		add_action( "after_theme_row_" . $theme['Stylesheet'], 'github_theme_update_row', 11, 2 );
		
		// Grab Github Tags
		preg_match(
			'/http(s)?:\/\/github.com\/(?<username>[\w-]+)\/(?<repo>[\w-]+)$/',
			$theme['UpdateURI'],
			$matches);
		if(!isset($matches['username']) or !isset($matches['repo'])){
			$data->response[$theme_key]['error'] = 'Incorrect github project url.  Format should be (no trailing slash): <code style="background:#FFFBE4;">https://github.com/&lt;username&gt;/&lt;repo&gt;</code>';
			continue;
		}
		$url = 'https://github.com/api/v2/json/repos/show/' . 
				$matches['username'] . '/' . $matches['repo'] .
				'/tags';
		$raw_response = wp_remote_get($url, array('sslverify' => false, 'timeout' => 10));
		if ( is_wp_error( $raw_response ) ){
			$data->response[$theme_key]['error'] = "Error response from " . $url;
			continue;
		}
		$response = json_decode($raw_response['body']);
		if(isset($response->error)){
			$data->response[$theme_key]['error'] = sprintf('While <a href="%s" style="color:#BC0B0B;text-decoration:underline;">fetching tags</a> api error</a> "%s"', $url, $response->error);
			continue;
		}
		if(!isset($response->tags) or count(get_object_vars($response->tags)) < 1){
			$data->response[$theme_key]['error'] = "Github theme does not have any tags";
			continue;
		}
		
		
		// Sort and get latest tag
		$tags = array_keys(get_object_vars($response->tags));
		usort($tags, "version_compare");
		$newest_tag = array_pop($tags);
		
		
		// check and generate download link
		if(version_compare($theme['Version'],  $newest_tag, '>=')){
			// up-to-date!
			continue;
		}
		$download_link = $theme['UpdateURI'] . '/zipball/' . $newest_tag;
		
		
		// new update available, add to $data
		$update = array();
		$update['new_version'] = $newest_tag;
		$update['url']         = $theme['UpdateURI'];
		$update['package']     = $download_link;
		$data->response[$theme_key] = $update;
		
	}
	
	return $data;
}


add_filter('upgrader_source_selection', 'upgrader_source_selection_filter', 10, 3);
function upgrader_source_selection_filter($source, $remote_source=NULL, $upgrader=NULL){
	/*
		Github delivers zip files as <Username>-<TagName>-<Hash>.zip
		must rename this zip file to the accurate theme folder
	*/
	if(isset($source, $remote_source, $upgrader->skin->theme)){
		$corrected_source = $remote_source . '/' . $upgrader->skin->theme . '/';
		if(@rename($source, $corrected_source)){
			return $corrected_source;
		} else {
			$upgrader->skin->feedback("Unable to rename downloaded theme.");
			return new WP_Error();
		}
	}
	return $source;
}