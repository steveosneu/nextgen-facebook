<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuSharingWhatsApp' ) && class_exists( 'NgfbSubmenuSharing' ) ) {

	class NgfbSubmenuSharingWhatsApp extends NgfbSubmenuSharing {

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->website_id = $id;
			$this->website_name = $name;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		protected function get_rows( $metabox, $key ) {
			$rows = array();

			$rows[] = $this->p->util->get_th( _x( 'Preferred Order',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$this->form->get_select( 'wa_order', 
				range( 1, count( $this->p->admin->submenu['sharing']->website ) ), 'short' ).  '</td>';

			$rows[] = $this->p->util->get_th( _x( 'Show Button in',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$this->show_on_checkboxes( 'wa' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( _x( 'Allow for Platform',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$this->form->get_select( 'wa_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			'<td colspan="2">'.$this->form->get_textarea( 'wa_html', 'average code' ).'</td>';

			return $rows;
		}
	}
}

if ( ! class_exists( 'NgfbSharingWhatsApp' ) ) {

	class NgfbSharingWhatsApp {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'wa_order' => 2,
					'wa_on_content' => 1,
					'wa_on_excerpt' => 0,
					'wa_on_sidebar' => 0,
					'wa_on_admin_edit' => 0,
					'wa_platform' => 'mobile',
					'wa_html' => '<div class="css-button whatsapp-button">
  <a href="whatsapp://send?text=%%title%%%20%%short_url%%" data-action="share/whatsapp/share">
    <span class="ngfb-icon">
      <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="auto" height="9" viewBox="0 0 90 90" enable-background="new 0 0 90 90;" xml:space="preserve">
        <path d="M90,43.841c0,24.213-19.779,43.841-44.182,43.841c-7.747,0-15.025-1.98-21.357-5.455L0,90l7.975-23.522c-4.023-6.606-6.34-14.354-6.34-22.637C1.635,19.628,21.416,0,45.818,0C70.223,0,90,19.628,90,43.841z M45.818,6.982c-20.484,0-37.146,16.535-37.146,36.859c0,8.065,2.629,15.534,7.076,21.61L11.107,79.14l14.275-4.537c5.865,3.851,12.891,6.097,20.437,6.097c20.481,0,37.146-16.533,37.146-36.857S66.301,6.982,45.818,6.982z M68.129,53.938c-0.273-0.447-0.994-0.717-2.076-1.254c-1.084-0.537-6.41-3.138-7.4-3.495c-0.993-0.358-1.717-0.538-2.438,0.537c-0.721,1.076-2.797,3.495-3.43,4.212c-0.632,0.719-1.263,0.809-2.347,0.271c-1.082-0.537-4.571-1.673-8.708-5.333c-3.219-2.848-5.393-6.364-6.025-7.441c-0.631-1.075-0.066-1.656,0.475-2.191c0.488-0.482,1.084-1.255,1.625-1.882c0.543-0.628,0.723-1.075,1.082-1.793c0.363-0.717,0.182-1.344-0.09-1.883c-0.27-0.537-2.438-5.825-3.34-7.977c-0.902-2.15-1.803-1.792-2.436-1.792c-0.631,0-1.354-0.09-2.076-0.09c-0.722,0-1.896,0.269-2.889,1.344c-0.992,1.076-3.789,3.676-3.789,8.963c0,5.288,3.879,10.397,4.422,11.113c0.541,0.716,7.49,11.92,18.5,16.223 C58.2,65.771,58.2,64.336,60.186,64.156c1.984-0.179,6.406-2.599,7.312-5.107C68.398,56.537,68.398,54.386,68.129,53.938z"/>
      </svg>
    </span>
    <span class="ngfb-text"></span>
  </a>
</div>',
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array(
				'get_defaults' => 1,
			) );
		}

		public function filter_get_defaults( $opts_def ) {
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		public function get_html( $atts = array(), &$opts = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$use_post = isset( $atts['use_post'] ) ?
				$atts['use_post'] : true;

			if ( ! isset( $atts['add_page'] ) )
				$atts['add_page'] = true;

			if ( ! isset( $atts['source_id'] ) )
				$atts['source_id'] = $this->p->util->get_source_id( 'whatsapp', $atts );

			return $this->p->util->replace_inline_vars( $this->p->options['wa_html'], $use_post, false, $atts, array(
			 	'title' => rawurlencode( $this->p->webpage->get_title( 0, '',
					$use_post, true, false, false, 'og_title', 'whatsapp' ) ),
			) );
		}
	}
}

?>
