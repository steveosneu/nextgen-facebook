<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbGplAdminAdvanced' ) ) {

	class NgfbGplAdminAdvanced {

		private $taglist_opts = array();

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'plugin_content_rows' => 2,	// $table_rows, $form
				'plugin_social_rows' => 2,	// $table_rows, $form
				'plugin_integration_rows' => 2,	// $table_rows, $form
				'plugin_cache_rows' => 3,	// $table_rows, $form, $network
				'plugin_apikeys_rows' => 2,	// $table_rows, $form
				'cm_custom_rows' => 2,		// $table_rows, $form
				'cm_builtin_rows' => 2,		// $table_rows, $form
				'taglist_og_rows' => 3,		// $table_rows, $form, $network
				'taglist_schema_rows' => 3,	// $table_rows, $form, $network
				'taglist_twitter_rows' => 3,	// $table_rows, $form, $network
				'taglist_other_rows' => 3,	// $table_rows, $form, $network
			), 20 );
		}

		public function filter_plugin_content_rows( $table_rows, $form ) {

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Use Filtered (SEO) Title',
				'option label', 'nextgen-facebook' ), null, 'plugin_filter_title' ).
			$this->get_nocb_cell( 'plugin_filter_title' );

			$table_rows[] = $form->get_th_html( _x( 'Apply WordPress Content Filters',
				'option label', 'nextgen-facebook' ), null, 'plugin_filter_content' ).
			$this->get_nocb_cell( 'plugin_filter_content' );

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Apply WordPress Excerpt Filters',
				'option label', 'nextgen-facebook' ), null, 'plugin_filter_excerpt' ).
			$this->get_nocb_cell( 'plugin_filter_excerpt' );

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Content Starts at 1st Paragraph',
				'option label', 'nextgen-facebook' ), null, 'plugin_p_strip' ).
			$this->get_nocb_cell( 'plugin_p_strip' );

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Use Image Alt if No Content',
				'option label', 'nextgen-facebook' ), null, 'plugin_use_img_alt' ).
			$this->get_nocb_cell( 'plugin_use_img_alt' );

			$table_rows['plugin_img_alt_prefix'] = $form->get_th_html( _x( 'Image Alt Text Prefix',
				'option label', 'nextgen-facebook' ), null, 'plugin_img_alt_prefix', array( 'is_locale' => true ) ).
			'<td class="blank">'.SucomUtil::get_locale_opt( 'plugin_img_alt_prefix', $this->p->options ).'</td>';

			$table_rows['plugin_p_cap_prefix'] = $form->get_th_html( _x( 'WP Caption Paragraph Prefix',
				'option label', 'nextgen-facebook' ), null, 'plugin_p_cap_prefix', array( 'is_locale' => true ) ).
			'<td class="blank">'.SucomUtil::get_locale_opt( 'plugin_p_cap_prefix', $this->p->options ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Check for Embedded Media',
				'option label', 'nextgen-facebook' ), null, 'plugin_embedded_media' ).
			'<td class="blank">'.
			'<p>'.$this->get_nocb( 'plugin_slideshare_api' ).' Slideshare Presentations</p>'.
			'<p>'.$this->get_nocb( 'plugin_vimeo_api' ).' Vimeo Videos</p>'.
			'<p>'.$this->get_nocb( 'plugin_wistia_api' ).' Wistia Videos</p>'.
			'<p>'.$this->get_nocb( 'plugin_youtube_api' ).' YouTube Videos and Playlists</p>'.
			'</td>';

			return $table_rows;
		}

		public function filter_plugin_social_rows( $table_rows, $form, $network = false ) {

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			foreach ( array( 
				'og_img' => sprintf( _x( 'Add \'%s\' Column for', 'option label', 'nextgen-facebook' ), 
					sprintf( _x( '%s Img', 'column title', 'nextgen-facebook' ), $this->p->cf['menu'] ) ),
				'og_desc' => sprintf( _x( 'Add \'%s\' Column for', 'option label', 'nextgen-facebook' ), 
					sprintf( _x( '%s Desc', 'column title', 'nextgen-facebook' ), $this->p->cf['menu'] ) ),
			) as $key => $label ) {

				if ( $network ) {
					$table_rows[] = $form->get_th_html( $label, null, 'plugin_'.$key.'_col', array( 'th_rowspan' => 3 ) ).
					$this->get_nocb_cell( 'plugin_'.$key.'_col_post', __( 'Posts, Pages, and Custom Post Types', 'nextgen-facebook' ) ).
					$this->p->admin->get_site_use( $form, $network, 'plugin_'.$key.'_col_post' );
	
					$table_rows[] = '<tr class="hide_in_basic">'.
					$this->get_nocb_cell( 'plugin_'.$key.'_col_term', __( 'Terms (Categories and Tags)', 'nextgen-facebook' ) ).
					$this->p->admin->get_site_use( $form, $network, 'plugin_'.$key.'_col_term' );
	
					$table_rows[] = '<tr class="hide_in_basic">'.
					$this->get_nocb_cell( 'plugin_'.$key.'_col_user', __( 'Users' ) ).
					$this->p->admin->get_site_use( $form, $network, 'plugin_'.$key.'_col_user' );
				} else {
					$table_rows[] = $form->get_th_html( $label, null, 'plugin_'.$key.'_col' ).
					'<td class="blank">'.
					'<p>'.$this->get_nocb( 'plugin_'.$key.'_col_post', __( 'Posts, Pages, and Custom Post Types', 'nextgen-facebook' ) ).'</p>'.
					'<p>'.$this->get_nocb( 'plugin_'.$key.'_col_term', __( 'Terms (Categories and Tags)', 'nextgen-facebook' ) ).'</p>'.
					'<p>'.$this->get_nocb( 'plugin_'.$key.'_col_user', __( 'Users' ) ).'</p>'.
					'</td>';
				}
			}
	
			$checkboxes = '';
			foreach ( $this->p->util->get_post_types() as $post_type )
				$checkboxes .= '<p>'.$this->get_nocb( 'plugin_add_to_'.$post_type->name ).' '.
					$post_type->label.'</p>';

			$checkboxes .= '<p>'.$this->get_nocb( 'plugin_add_to_term' ).
				' '.__( 'Terms (Categories and Tags)', 'nextgen-facebook' ).'</p>';

			$checkboxes .= '<p>'.$this->get_nocb( 'plugin_add_to_user' ).
				' '.__( 'User Profile', 'nextgen-facebook' ).'</p>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Include Social Metaboxes on',
				'option label', 'nextgen-facebook' ), null, 'plugin_add_to' ).
			'<td class="blank">'.$checkboxes.'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Image URL Custom Field',
				'option label', 'nextgen-facebook' ), null, 'plugin_cf_img_url' ).
			'<td class="blank">'.$form->get_hidden( 'plugin_cf_img_url' ).
				$this->p->options['plugin_cf_img_url'].'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Video URL Custom Field',
				'option label', 'nextgen-facebook' ), null, 'plugin_cf_vid_url' ).
			'<td class="blank">'.$form->get_hidden( 'plugin_cf_vid_url' ).
				$this->p->options['plugin_cf_vid_url'].'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Video Embed HTML Custom Field',
				'option label', 'nextgen-facebook' ), null, 'plugin_cf_vid_embed' ).
			'<td class="blank">'.$form->get_hidden( 'plugin_cf_vid_embed' ).
				$this->p->options['plugin_cf_vid_embed'].'</td>';

			return $table_rows;
		}

		public function filter_plugin_integration_rows( $table_rows, $form ) {

			$table_rows[] = '<td colspan="3" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( '&lt;html&gt; Attributes Filter Hook',
				'option label', 'nextgen-facebook' ), null, 'plugin_html_attr_filter' ).
			'<td class="blank">Name:&nbsp;'.$this->p->options['plugin_html_attr_filter_name'].', '.
				'Priority:&nbsp;'.$this->p->options['plugin_html_attr_filter_prio'].'</td>';

			if ( apply_filters( $this->p->cf['lca'].'_add_schema_head_attributes', true ) ) {
				$table_rows[] = $form->get_th_html( _x( '&lt;head&gt; Attributes Filter Hook',
					'option label', 'nextgen-facebook' ), null, 'plugin_head_attr_filter' ).
				'<td class="blank">Name:&nbsp;'.$this->p->options['plugin_head_attr_filter_name'].', '.
					'Priority:&nbsp;'.$this->p->options['plugin_head_attr_filter_prio'].'</td>';
			} else {
				$table_rows[] = '<tr class="hide_in_basic">'.
				$form->get_th_html( _x( '&lt;head&gt; Attributes Filter Hook',
					'option label', 'nextgen-facebook' ), null, 'plugin_head_attr_filter' ).
				'<td colspan="2"><em>'.__( 'head attributes filter disabled by extension plugin or custom filter',
					'nextgen-facebook' ).'<em></td>';
			}

			$table_rows[] = $form->get_th_html( _x( 'Check for Duplicate Meta Tags',
				'option label', 'nextgen-facebook' ), null, 'plugin_check_head' ).
			$this->get_nocb_cell( 'plugin_check_head' );

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Use WP Locale for Language',
				'option label', 'nextgen-facebook' ), null, 'plugin_filter_lang' ).
			$this->get_nocb_cell( 'plugin_filter_lang' );

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Generate Missing WP Media Sizes',
				'option label', 'nextgen-facebook' ), null, 'plugin_auto_img_resize' ).
			$this->get_nocb_cell( 'plugin_auto_img_resize' );

			$table_rows[] = $form->get_th_html( _x( 'Enforce Image Dimensions Check',
				'option label', 'nextgen-facebook' ), null, 'plugin_check_img_dims' ).
			$this->get_nocb_cell( 'plugin_check_img_dims' );

			$table_rows[] = $form->get_th_html( _x( 'Allow Upscaling of WP Media Images',
				'option label', 'nextgen-facebook' ), null, 'plugin_upscale_images' ).
			$this->get_nocb_cell( 'plugin_upscale_images' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Maximum Image Upscale Percentage',
				'option label', 'nextgen-facebook' ), null, 'plugin_upscale_img_max' ).
			'<td class="blank">'.$this->p->options['plugin_upscale_img_max'].' %</td>';

			if ( ! empty( $this->p->cf['*']['lib']['shortcode'] ) ) {
				$table_rows[] = '<tr class="hide_in_basic">'.
				$form->get_th_html( _x( 'Enable Plugin Shortcode(s)',
					'option label', 'nextgen-facebook' ), null, 'plugin_shortcodes' ).
				$this->get_nocb_cell( 'plugin_shortcodes' );
			}

			if ( ! empty( $this->p->cf['*']['lib']['widget'] ) ) {
				$table_rows[] = '<tr class="hide_in_basic">'.
				$form->get_th_html( _x( 'Enable Plugin Widget(s)',
					'option label', 'nextgen-facebook' ), null, 'plugin_widgets' ).
				$this->get_nocb_cell( 'plugin_widgets' );
			}

			$table_rows[] = $form->get_th_html( _x( 'Enable WP Excerpt for Pages',
				'option label', 'nextgen-facebook' ), null, 'plugin_page_excerpt' ).
			$this->get_nocb_cell( 'plugin_page_excerpt' );

			$table_rows[] = $form->get_th_html( _x( 'Enable WP Tags for Pages',
				'option label', 'nextgen-facebook' ), null, 'plugin_page_tags' ).
			$this->get_nocb_cell( 'plugin_page_tags' );

			return $table_rows;
		}

		public function filter_plugin_cache_rows( $table_rows, $form, $network = false ) {

			$table_rows[] = '<td colspan="'.( $network ? 4 : 2 ).'" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'ngfb' ) ).'</td>';

			$table_rows['plugin_object_cache_exp'] = $form->get_th_html( _x( 'Object Cache Expiry',
				'option label', 'nextgen-facebook' ), null, 'plugin_object_cache_exp' ).
			'<td nowrap class="blank">'.$this->p->options['plugin_object_cache_exp'].' seconds</td>'.
			$this->p->admin->get_site_use( $form, $network, 'plugin_object_cache_exp' );

			$table_rows['plugin_verify_certs'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Verify Peer SSL Certificate',
				'option label', 'nextgen-facebook' ), null, 'plugin_verify_certs' ).
			$this->get_nocb_cell( 'plugin_verify_certs' ).
			$this->p->admin->get_site_use( $form, $network, 'plugin_verify_certs' );

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Report Cache Purge Count',
				'option label', 'nextgen-facebook' ), null, 'plugin_cache_info' ).
			$this->get_nocb_cell( 'plugin_cache_info' ).
			$this->p->admin->get_site_use( $form, $network, 'plugin_cache_info' );

			return $table_rows;
		}

		public function filter_plugin_apikeys_rows( $table_rows, $form ) {

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'ngfb' ) ).'</td>';

			$table_rows['plugin_shortener'] = $form->get_th_html( _x( 'Preferred URL Shortening Service',
				'option label', 'nextgen-facebook' ), null, 'plugin_shortener' ).
			'<td class="blank">[None]</td>';

			$table_rows['plugin_shortlink'] = $form->get_th_html( _x( '<em>Get Shortlink</em> Gives Shortened URL',
				'option label', 'nextgen-facebook' ), null, 'plugin_shortlink' ).
			$this->get_nocb_cell( 'plugin_shortlink' );

			$table_rows['plugin_min_shorten'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Minimum URL Length to Shorten',
				'option label', 'nextgen-facebook' ), null, 'plugin_min_shorten' ). 
			'<td nowrap class="blank">'.$this->p->options['plugin_min_shorten'].' '.
				_x( 'characters', 'option comment', 'nextgen-facebook' ).'</td>';

			$table_rows['subsection_plugin_bitly'] = '<tr class="hide_in_basic">'.
				'<td></td><td class="subsection"><h4>'.
				_x( 'Bitly URL Shortener', 'metabox title', 'nextgen-facebook' ).'</h4></td>';

			$table_rows['plugin_bitly_login'] = $form->get_th_html( _x( 'Bitly Username',
				'option label', 'nextgen-facebook' ), null, 'plugin_bitly_login' ).
			'<td class="blank mono">'.$this->p->options['plugin_bitly_login'].'</td>';

			$table_rows['plugin_bitly_api_key'] = $form->get_th_html( _x( 'Bitly API Key',
				'option label', 'nextgen-facebook' ), null, 'plugin_bitly_api_key' ).
			'<td class="blank mono">'.$this->p->options['plugin_bitly_api_key'].'</td>';

			$table_rows['subsection_plugin_google'] = '<tr class="hide_in_basic">'.
				'<td></td><td class="subsection"><h4>'.
				_x( 'Google APIs', 'metabox title', 'nextgen-facebook' ).'</h4></td>';

			$table_rows['plugin_google_api_key'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Google Project App BrowserKey',
				'option label', 'nextgen-facebook' ), null, 'plugin_google_api_key' ).
			'<td class="blank mono">'.$this->p->options['plugin_google_api_key'].'</td>';

			$table_rows['plugin_google_shorten'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Google URL Shortener API is ON',
				'option label', 'nextgen-facebook' ), null, 'plugin_google_shorten' ).
			'<td class="blank">'._x( $this->p->cf['form']['yes_no'][$this->p->options['plugin_google_shorten']],
				'option value', 'nextgen-facebook' ).'</td>';

			$table_rows['subsection_plugin_owly'] = '<tr class="hide_in_basic">'.
				'<td></td><td class="subsection"><h4>'.
				_x( 'Ow.ly URL Shortener', 'metabox title', 'nextgen-facebook' ).'</h4></td>';

			$table_rows['plugin_owly_api_key'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Ow.ly API Key',
				'option label', 'nextgen-facebook' ), null, 'plugin_owly_api_key' ).
			'<td class="blank mono">'.$this->p->options['plugin_owly_api_key'].'</td>';

			$table_rows['plugin_owly_api_key'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Ow.ly API Key',
				'option label', 'nextgen-facebook' ), null, 'plugin_owly_api_key' ).
			'<td class="blank mono">'.$this->p->options['plugin_owly_api_key'].'</td>';

			$table_rows['subsection_plugin_yourls'] = '<tr class="hide_in_basic">'.
				'<td></td><td class="subsection"><h4>'.
				_x( 'Your Own URL Shortener (YOURLS)', 'metabox title', 'nextgen-facebook' ).'</h4></td>';

			$table_rows['plugin_yourls_api_url'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'YOURLS API URL',
				'option label', 'nextgen-facebook' ), null, 'plugin_yourls_api_url' ).
			'<td class="blank mono">'.$this->p->options['plugin_yourls_api_url'].'</td>';

			$table_rows['plugin_yourls_username'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'YOURLS Username',
				'option label', 'nextgen-facebook' ), null, 'plugin_yourls_username' ).
			'<td class="blank mono">'.$this->p->options['plugin_yourls_username'].'</td>';

			$table_rows['plugin_yourls_password'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'YOURLS Password',
				'option label', 'nextgen-facebook' ), null, 'plugin_yourls_password' ).
			'<td class="blank mono">'.$this->p->options['plugin_yourls_password'].'</td>';

			$table_rows['plugin_yourls_token'] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'YOURLS Token',
				'option label', 'nextgen-facebook' ), null, 'plugin_yourls_token' ).
			'<td class="blank mono">'.$this->p->options['plugin_yourls_token'].'</td>';

			return $table_rows;
		}

		public function filter_cm_custom_rows( $table_rows, $form ) {

			$table_rows[] = '<td colspan="4" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$table_rows[] = '<td></td>'.
			$form->get_th_html( _x( 'Show',
				'column title', 'nextgen-facebook' ), 'left checkbox' ).
			$form->get_th_html( _x( 'Contact Field Name',
				'column title', 'nextgen-facebook' ), 'left medium', 'custom-cm-field-name' ).
			$form->get_th_html( _x( 'Profile Contact Label',
				'column title', 'nextgen-facebook' ), 'left wide' );

			$sorted_opt_pre = $this->p->cf['opt']['pre'];
			ksort( $sorted_opt_pre );

			foreach ( $sorted_opt_pre as $id => $pre ) {

				$cm_enabled = 'plugin_cm_'.$pre.'_enabled';
				$cm_name = 'plugin_cm_'.$pre.'_name';
				$cm_label = 'plugin_cm_'.$pre.'_label';

				// not all social websites have a contact method field
				if ( isset( $this->p->options[$cm_enabled] ) ) {

					switch ( $id ) {
						case 'facebook':
						case 'gplus':
						case 'twitter':
							$tr = '';
							break;
						default:
							$tr = '<tr class="hide_in_basic">';
							break;
					}

					$name = empty( $this->p->cf['*']['lib']['website'][$id] ) ? 
						ucfirst( $id ) : $this->p->cf['*']['lib']['website'][$id];
					$name = $name == 'GooglePlus' ? 'Google+' : $name;

					$table_rows[] = $tr.$form->get_th_html( $name, 'medium' ).
					'<td class="blank checkbox">'.$this->get_nocb( $cm_enabled ).'</td>'.
					'<td class="blank">'.$form->get_no_input( $cm_name, 'medium' ).'</td>'.
					'<td class="blank">'.$form->get_no_input( $cm_label ).'</td>';
				}
			}

			return $table_rows;
		}

		public function filter_cm_builtin_rows( $table_rows, $form ) {

			$table_rows[] = '<td colspan="4" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$table_rows[] = '<td></td>'.
			$form->get_th_html( _x( 'Show',
				'column title', 'nextgen-facebook' ), 'left checkbox' ).
			$form->get_th_html( _x( 'Contact Field Name',
				'column title', 'nextgen-facebook' ), 'left medium', 'custom-cm-field-name' ).
			$form->get_th_html( _x( 'Profile Contact Label',
				'column title', 'nextgen-facebook' ), 'left wide' );

			$sorted_wp_cm = $this->p->cf['wp']['cm'];
			ksort( $sorted_wp_cm );

			foreach ( $sorted_wp_cm as $id => $name ) {

				$cm_enabled = 'wp_cm_'.$id.'_enabled';
				$cm_name = 'wp_cm_'.$id.'_name';
				$cm_label = 'wp_cm_'.$id.'_label';

				if ( array_key_exists( $cm_enabled, $this->p->options ) ) {
					$table_rows[] = $form->get_th_html( $name, 'medium' ).
					'<td class="blank checkbox">'.$this->get_nocb( $cm_enabled ).'</td>'.
					'<td>'.$form->get_no_input( $cm_name, 'medium' ).'</td>'.
					'<td class="blank">'.$form->get_no_input( $cm_label ).'</td>';
				}
			}

			return $table_rows;
		}

		public function filter_taglist_og_rows( $table_rows, $form, $network = false ) {
			return $this->get_taglist_rows( $table_rows, $form, $network,
				array( '/^add_(meta)_(property)_(.+)$/' ) );
		}

		public function filter_taglist_schema_rows( $table_rows, $form, $network = false ) {
			return $this->get_taglist_rows( $table_rows, $form, $network,
				array( '/^add_(meta)_(itemprop)_(.+)$/' ) );
		}

		public function filter_taglist_twitter_rows( $table_rows, $form, $network = false ) {
			return $this->get_taglist_rows( $table_rows, $form, $network,
				array( '/^add_(meta)_(name)_(twitter:.+)$/' ) );
		}

		public function filter_taglist_other_rows( $table_rows, $form, $network = false ) {
			return $this->get_taglist_rows( $table_rows, $form, $network,
				array( '/^add_(link)_([^_]+)_(.+)$/', '/^add_(meta)_(name)_(.+)$/' ) );
		}

		private function get_taglist_rows( &$table_rows, &$form, &$network, array $opt_preg ) {
			$cells = array();
			$opt_defs = $this->p->opt->get_defaults();
			foreach ( $opt_preg as $preg ) {
				foreach ( $opt_defs as $opt => $val ) {
					if ( strpos( $opt, 'add_' ) !== 0 ||			// optimize
						isset( $this->taglist_opts[$opt] ) ||		// check cache for tags already shown
							! preg_match( $preg, $opt, $match ) )	// check option name for a match
								continue;
					$highlight = '';
					$this->taglist_opts[$opt] = $val;
					switch ( $opt ) {
						// disable with a constant instead
						case 'add_meta_name_generator':
							continue 2;
						// highlight important meta tags
						case 'add_meta_name_canonical':
						case 'add_meta_name_description':
							$highlight = ' highlight';
							break;
					}
					$cells[] = '<!-- '.( implode( ' ', $match ) ).' -->'.	// required for sorting
						'<td class="checkbox blank">'.$this->get_nocb( $opt ).'</td>'.
						'<td class="xshort'.$highlight.'">'.$match[1].'</td>'.
						'<td class="taglist'.$highlight.'">'.$match[2].'</td>'.
						'<th class="taglist'.$highlight.'">'.$match[3].'</th>';
				}
			}
			return array_merge( $table_rows, SucomUtil::get_column_rows( $cells, 2 ) );
		}

		private function get_nocb( $name, $text = '' ) {
			return '<input type="checkbox" disabled="disabled" '.
				checked( $this->p->options[$name], 1, false ).'/>'.
					( empty( $text ) ? '' : ' '.$text );
		}

		private function get_nocb_cell( $name, $text = '', $comment = '' ) {
			return '<td class="blank">'.$this->get_nocb( $name, $text ).
				( empty( $comment ) ? '' : ' '.$comment ).'</td>';
		}
	}
}

?>
