<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuWebsiteManagewp' ) ) {

	class NgfbSubmenuWebsiteManagewp {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'website_managewp_rows' => 3,		// $table_rows, $form, $submenu
			) );
		}

		public function filter_website_managewp_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			$form->get_select( 'managewp_order', 
				range( 1, count( $submenu->website ) ), 'short' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			( $submenu->show_on_checkboxes( 'managewp' ) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Allow for Platform',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$form->get_select( 'managewp_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Type',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			$form->get_select( 'managewp_type', 
				array( 
					'small' => 'Small',
					'big' => 'Big',
				)
			).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'NgfbWebsiteManagewp' ) ) {

	class NgfbWebsiteManagewp {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'managewp_order' => 10,
					'managewp_on_content' => 0,
					'managewp_on_excerpt' => 0,
					'managewp_on_sidebar' => 0,
					'managewp_on_admin_edit' => 1,
					'managewp_platform' => 'any',
					'managewp_type' => 'small',
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

			$lca = $this->p->cf['lca'];
			$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
			$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;      // get_sharing_url() argument

			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $mod, $atts['add_page'] ) : 
				apply_filters( $lca.'_sharing_url', $atts['url'], $mod, $atts['add_page'] );

			if ( empty( $atts['title'] ) )
				$atts['title'] = $this->p->webpage->get_title( null, null, $mod, true, false, true, null );

			$js_url = $this->p->util->get_cache_file_url( apply_filters( $this->p->cf['lca'].'_js_url_managewp', 
				SucomUtil::get_prot().'://managewp.org/share.js#'.SucomUtil::get_prot().'://managewp.org/share', '' ) );

			$html = '<!-- ManageWP Button -->'.
			'<div '.NgfbSharing::get_css_class_id( 'managewp', $atts ).'>'.
			'<script type="text/javascript" src="'.$js_url.'" data-url="'.$atts['url'].'" data-title="'.$atts['title'].'"'.
				( empty( $opts['managewp_type'] ) ? '' : ' data-type="'.$opts['managewp_type'].'"' ).'>'.
			'</script></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			return $html;
		}
	}
}

?>
