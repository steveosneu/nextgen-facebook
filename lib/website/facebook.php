<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) )
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuSharingFacebook' ) && class_exists( 'NgfbSubmenuSharing' ) ) {

	class NgfbSubmenuSharingFacebook extends NgfbSubmenuSharing {

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->website_id = $id;
			$this->website_name = $name;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		public function show_metabox_website() {
			$metabox = 'fb';
			$tabs = array( 
				'all' => _x( 'All Buttons', 'metabox tab', 'nextgen-facebook' ),
				'like' => _x( 'Like and Send', 'metabox tab', 'nextgen-facebook' ),
				'share' => _x( 'Share', 'metabox tab', 'nextgen-facebook' ),
			);
			$table_rows = array();
			foreach ( $tabs as $key => $title )
				$table_rows[$key] = $this->get_table_rows( $metabox, $key );
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );
		}

		protected function get_table_rows( $metabox, $key ) {
			$table_rows = array();
			switch ( $metabox.'-'.$key ) {

				case 'fb-all':

					$table_rows[] = $this->form->get_th_html( _x( 'Preferred Order',
						'option label (short)', 'nextgen-facebook' ), 'short' ).
					'<td>'.$this->form->get_select( 'fb_order', 
						range( 1, count( $this->p->admin->submenu['sharing']->website ) ), 'short' ).'</td>';
	
					$table_rows[] = $this->form->get_th_html( _x( 'Show Button in',
						'option label (short)', 'nextgen-facebook' ), 'short' ).
					'<td>'.( $this->show_on_checkboxes( 'fb' ) ).'</td>';

					$table_rows[] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Allow for Platform',
						'option label (short)', 'nextgen-facebook' ), 'short' ).
					'<td>'.$this->form->get_select( 'fb_platform',
						$this->p->cf['sharing']['platform'] ).'</td>';

					$table_rows[] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'JavaScript in',
						'option label (short)', 'nextgen-facebook' ), 'short' ).
					'<td>'. $this->form->get_select( 'fb_script_loc',
						$this->p->cf['form']['script_locations'] ).'</td>';
	
					$table_rows[] = $this->form->get_th_html( _x( 'Default Language',
						'option label (short)', 'nextgen-facebook' ), 'short' ).
					'<td>'.$this->form->get_select( 'fb_lang',
						SucomUtil::get_pub_lang( 'facebook' ) ).'</td>';
	
					$table_rows[] = $this->form->get_th_html( _x( 'Button Type',
						'option label (short)', 'nextgen-facebook' ), 'short' ).
					'<td>'.$this->form->get_select( 'fb_button', 
						array( 'like' => 'Like and Send', 'share' => 'Share' ) ).'</td>';

					break;

				case 'fb-like':

					$table_rows[] = $this->form->get_th_html( _x( 'Markup Language',
						'option label (short)', 'nextgen-facebook' ), 'short' ).
					'<td>'.$this->form->get_select( 'fb_markup', 
						array( 'html5' => 'HTML5', 'xfbml' => 'XFBML' ) ).'</td>';
	
					$table_rows[] = $this->form->get_th_html( _x( 'Include Send',
						'option label (short)', 'nextgen-facebook' ), 'short', null, 
					'The Send button is only available in combination with the XFBML <em>Markup Language</em>.' ).
					'<td>'.$this->form->get_checkbox( 'fb_send' ).'</td>';
	
					$table_rows[] = $this->form->get_th_html( _x( 'Layout',
						'option label (short)', 'nextgen-facebook' ), 'short', null, 
					'The Standard layout displays social text to the right of the button, and friends\' profile photos below (if <em>Show Faces</em> is also checked). The Button Count layout displays the total number of likes to the right of the button, and the Box Count layout displays the total number of likes above the button. See the <a href="https://developers.facebook.com/docs/plugins/like-button#faqlayout" target="_blank">Facebook Layout Settings FAQ</a> for more details.' ).
					'<td>'.$this->form->get_select( 'fb_layout', 
						array(
							'standard' => 'Standard',
							'button' => 'Button',
							'button_count' => 'Button Count',
							'box_count' => 'Box Count',
						) 
					).'</td>';
	
					$table_rows[] = $this->form->get_th_html( _x( 'Show Faces',
						'option label (short)', 'nextgen-facebook' ), 'short', null, 
					'Show profile photos below the Standard button (Standard button <em>Layout</em> only).' ).
					'<td>'.$this->form->get_checkbox( 'fb_show_faces' ).'</td>';
	
					$table_rows[] = $this->form->get_th_html( _x( 'Font',
						'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
					$this->form->get_select( 'fb_font', 
						array( 
							'arial' => 'Arial',
							'lucida grande' => 'Lucida Grande',
							'segoe ui' => 'Segoe UI',
							'tahoma' => 'Tahoma',
							'trebuchet ms' => 'Trebuchet MS',
							'verdana' => 'Verdana',
						) 
					).'</td>';
	
					$table_rows[] = $this->form->get_th_html( _x( 'Color Scheme',
						'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
					$this->form->get_select( 'fb_colorscheme', 
						array( 
							'light' => 'Light',
							'dark' => 'Dark',
						)
					).'</td>';
	
					$table_rows[] = $this->form->get_th_html( _x( 'Action Name',
						'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
					$this->form->get_select( 'fb_action', 
						array( 
							'like' => 'Like',
							'recommend' => 'Recommend',
						)
					).'</td>';

					break;
	
				case 'fb-share':

					$table_rows[] = $this->form->get_th_html( _x( 'Layout',
						'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
					$this->form->get_select( 'fb_type', 
						array(
							'button' => 'Button',
							'button_count' => 'Button Count',
							'box_count' => 'Box Count',
							'icon' => 'Small Icon',
							'icon_link' => 'Icon Link',
							'link' => 'Text Link',
						) 
					).'</td>';

					break;
			}
			return $table_rows;
		}
	}
}

if ( ! class_exists( 'NgfbSharingFacebook' ) ) {

	class NgfbSharingFacebook {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'fb_order' => 4,
					'fb_on_content' => 1,
					'fb_on_excerpt' => 0,
					'fb_on_sidebar' => 0,
					'fb_on_admin_edit' => 1,
					'fb_platform' => 'any',
					'fb_script_loc' => 'header',
					'fb_button' => 'like',
					'fb_markup' => 'xfbml',
					'fb_send' => 1,
					'fb_layout' => 'button_count',
					'fb_font' => 'arial',
					'fb_show_faces' => 0,
					'fb_colorscheme' => 'light',
					'fb_action' => 'like',
					'fb_type' => 'button_count',
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 'get_defaults' => 1 ) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		// do not use an $atts reference to allow for local changes
		public function get_html( array $atts, array &$opts, array &$mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
			$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;      // get_sharing_url() argument

			$atts['source_id'] = 'facebook';
			switch ( $opts['fb_button'] ) {
				case 'like':
					$atts['source_id'] = $this->p->util->get_source_id( 'facebook', $atts );
					break;
				case 'share':
					$atts['source_id'] = $this->p->util->get_source_id( 'fb-share', $atts );
					break;
			}

			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $atts['use_post'], $atts['add_page'], $atts['source_id'] ) : 
				apply_filters( $this->p->cf['lca'].'_sharing_url', $atts['url'], 
					$atts['use_post'], $atts['add_page'], $atts['source_id'] );

			$atts['send'] = $opts['fb_send'] ? 'true' : 'false';
			$atts['show_faces'] = $opts['fb_show_faces'] ? 'true' : 'false';

			$html = '';
			switch ( $opts['fb_button'] ) {
				case 'like':
					switch ( $opts['fb_markup'] ) {
						case 'xfbml':
							// XFBML
							$html .= '<!-- Facebook Like / Send Button(s) --><div '.
							NgfbSharing::get_css_class_id( 'facebook', $atts, 'fb-like' ).'><fb:like href="'.
							$atts['url'].'" send="'.$atts['send'].'" layout="'.$opts['fb_layout'].'" show_faces="'.
							$atts['show_faces'].'" font="'.$opts['fb_font'].'" action="'.
							$opts['fb_action'].'" colorscheme="'.$opts['fb_colorscheme'].'"></fb:like></div>';
							break;
						case 'html5':
							// HTML5
							$html .= '<!-- Facebook Like / Send Button(s) --><div '.
							NgfbSharing::get_css_class_id( 'facebook', $atts, 'fb-like' ).' data-href="'.
							$atts['url'].'" data-send="'.$atts['send'].'" data-layout="'.
							$opts['fb_layout'].'" data-show-faces="'.$atts['show_faces'].'" data-font="'.
							$opts['fb_font'].'" data-action="'.$opts['fb_action'].'" data-colorscheme="'.
							$opts['fb_colorscheme'].'"></div>';
							break;
					}
					break;
				case 'share':
					$html .= '<!-- Facebook Share Button --><div '.
					NgfbSharing::get_css_class_id( 'fb-share', $atts, 'fb-share' ).'><fb:share-button href="'.
					$atts['url'].'" font="'.$opts['fb_font'].'" type="'.$opts['fb_type'].'"></fb:share-button></div>';
					break;
			}

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			return $html;
		}
		
		public function get_script( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$app_id = empty( $this->p->options['fb_app_id'] ) ? '' : $this->p->options['fb_app_id'];
			$lang = empty( $this->p->options['fb_lang'] ) ? 'en_US' : $this->p->options['fb_lang'];
			$lang = apply_filters( $this->p->cf['lca'].'_pub_lang', $lang, 'facebook' );

			// do not use get_cache_file_url() since the facebook javascript does not work when hosted locally
			$js_url = apply_filters( $this->p->cf['lca'].'_js_url_facebook', 
				SucomUtil::get_prot().'://connect.facebook.net/'.$lang.'/sdk.js#xfbml=1&version=v2.3&appId='.$app_id, $pos );

			$html = '<script type="text/javascript" id="fb-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "fb-script-'.$pos.'", "'.$js_url.'" );</script>';

			return $html;
		}
	}
}

?>
