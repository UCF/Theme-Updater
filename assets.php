<?php
/******************************************************************************\
	Most of this code is pulled directly from the WP source
	modifications are noted.
\******************************************************************************/

function github_theme_update_row( $theme_key, $theme ) {
	/*
		http://core.trac.wordpress.org/browser/trunk/wp-admin/includes/update.php?rev=17984#L264
		changes:  
			- disables the iframe popup and uses a new window and makes a pop-up linking to the github project
			- calls 'upgrade-github-theme' vs 'upgrade-theme'
	*/  
	
	$current = get_site_transient( 'update_themes' );
	if ( !isset( $current->response[ $theme_key ] ) )
		return false;
		
	$r = $current->response[ $theme_key ];
	$themes_allowedtags = array('a' => array('href' => array(),'title' => array()),'abbr' => array('title' => array()),'acronym' => array('title' => array()),'code' => array(),'em' => array(),'strong' => array());
	$theme_name = wp_kses( $theme['Name'], $themes_allowedtags );
	$details_url = $r['url'];
	$wp_list_table = _get_list_table('WP_MS_Themes_List_Table');
	
	echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';
	if( isset($r['error']) ){
		printf('Error with Github Theme Updater: <span style="color:#BC0B0B">%1$s</span>', $r['error']);
	} else if ( ! current_user_can('update_themes') )
		printf( __('From GitHub, there is a new version of %1$s. <a href="%2$s" target="blank" title="%3$s">View version %4$s details</a>.'), $theme['Name'], esc_url($details_url), esc_attr($theme['Name']), $r->new_version );
	else if ( empty( $r['package'] ) )
		printf( __('From GitHub, there is a new version of %1$s. <a href="%2$s" target="blank" title="%3$s">View version %4$s details</a>. <em>Automatic update is unavailable for this plugin.</em>'), $theme['Name'], esc_url($details_url), esc_attr($theme['Name']), $r['new_version'] );
	else
		printf( __('From GitHub, there is a new version of %1$s. <a href="%2$s" target="blank" title="%3$s">View version %4$s details</a> or <a href="%5$s">update automatically</a>.'), $theme['Name'], esc_url($details_url), esc_attr($theme['Name']), $r['new_version'], wp_nonce_url( self_admin_url('update.php?action=upgrade-github-theme&theme=') . $theme_key, 'upgrade-theme_' . $theme_key) );
	do_action( "in_theme_update_message-$theme_key", $theme, $r );
	echo '</div></td></tr>';
}


include ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
class Github_Theme_Upgrader extends Theme_Upgrader {
	function download_url( $url ) {
		/*
			http://core.trac.wordpress.org/browser/trunk/wp-admin/includes/file.php?rev=17928#L467
			changes:  
				- wanted a timeout < 5 min
				- SSL fails when trying to access github
		*/
		if ( ! $url )
			return new WP_Error('http_no_url', __('Invalid URL Provided.'));

		$tmpfname = wp_tempnam($url);
		if ( ! $tmpfname )
			return new WP_Error('http_no_file', __('Could not create Temporary file.'));

		$handle = @fopen($tmpfname, 'wb');
		if ( ! $handle )
			return new WP_Error('http_no_file', __('Could not create Temporary file.'));

		// This! is the one line I wanted to get at
		$response = wp_remote_get($url , array('sslverify' => false, 'timeout' => 30));
		
		if ( is_wp_error($response) ) {
			fclose($handle);
			unlink($tmpfname);
			return $response;
		}

		if ( $response['response']['code'] != '200' ){
			fclose($handle);
			unlink($tmpfname);
			return new WP_Error('http_404', trim($response['response']['message']));
		}

		fwrite($handle, $response['body']);
		fclose($handle);

		return $tmpfname;
	}
	
	function download_package($package) {
		/*
			http://core.trac.wordpress.org/browser/trunk/wp-admin/includes/class-wp-upgrader.php?rev=17771#L108
			changes:
				- use customized download_url
		*/
		if ( ! preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package) ) //Local file or remote?
			return $package; //must be a local file..

		if ( empty($package) )
			return new WP_Error('no_package', $this->strings['no_package']);

		$this->skin->feedback('downloading_package', $package);
		
		// This! is the one line I wanted to get at
		$download_file = $this->download_url($package);

		if ( is_wp_error($download_file) )
			return new WP_Error('download_failed', $this->strings['download_failed'], $download_file->get_error_message());

		return $download_file;
	}
	
}

add_action('update-custom_upgrade-github-theme', 'github_theme_updater', 10, 2);
function github_theme_updater(){
	/*
		http://core.trac.wordpress.org/browser/trunk/wp-admin/update.php?rev=17632#L145
		changes:  
			- use customized theme upgrader
	*/ 
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

	$upgrader = new Github_Theme_Upgrader( new Theme_Upgrader_Skin( compact('title', 'nonce', 'url', 'theme') ) );
	$upgrader->upgrade($theme);
	
	include(ABSPATH . 'wp-admin/admin-footer.php');
}