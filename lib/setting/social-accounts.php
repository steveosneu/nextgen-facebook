<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSettingSocialAccounts' ) && class_exists( 'NgfbAdmin' ) ) {

	class NgfbSettingSocialAccounts extends NgfbAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {
			$this->p =& $plugin;
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_social_accounts',
				_x( 'Social Pages and Accounts', 'metabox title', 'nextgen-facebook' ), 
					array( &$this, 'show_metabox_social_accounts' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_social_accounts() {
			$metabox = $this->menu_id;
			echo '<table class="sucom-setting '.$this->p->cf['lca'].'">';
			echo '<tr><td colspan="2">'.$this->p->msgs->get( 'info-'.$metabox ).'</td></tr>';

			$table_rows = array_merge( $this->get_table_rows( $metabox, 'general' ), 
				apply_filters( SucomUtil::sanitize_hookname( $this->p->cf['lca'].'_'.$metabox.'_general_rows' ), 
					array(), $this->form ) );
					
			foreach ( $table_rows as $num => $row ) 
				echo '<tr>'.$row.'</tr>';
			echo '</table>';
		}

		protected function get_table_rows( $metabox, $key ) {
			$table_rows = array();

			switch ( $metabox.'-'.$key ) {

				case 'social-accounts-general':

					foreach ( array(
						'fb_publisher_url' => array(
							'label' => _x( 'Facebook Business Page URL', 'option label', 'nextgen-facebook' ),
							'tooltip' => 'fb_publisher_url', 'input_class' => 'wide',
						),
						'seo_publisher_url' => array(
							'label' => _x( 'Google+ Business Page URL', 'option label', 'nextgen-facebook' ),
							'tooltip' => 'google_publisher_url', 'input_class' => 'wide',
						),
						'rp_publisher_url' => array(
							'label' => _x( 'Pinterest Company Page URL', 'option label', 'nextgen-facebook' ),
							'tooltip' => 'rp_publisher_url', 'input_class' => 'wide',
						),
						'tc_site' => array(
							'label' => _x( 'Twitter Business @username', 'option label', 'nextgen-facebook' ),
							'tooltip' => 'tc_site', 'input_class' => '',
						),
						'instgram_publisher_url' => array(
							'label' => _x( 'Instagram Business URL', 'option label', 'nextgen-facebook' ),
							'tooltip' => 'instgram_publisher_url', 'input_class' => 'wide',
						),
						'linkedin_publisher_url' => array(
							'label' => _x( 'LinkedIn Company Page URL', 'option label', 'nextgen-facebook' ),
							'tooltip' => 'linkedin_publisher_url', 'input_class' => 'wide',
						),
						'myspace_publisher_url' => array(
							'label' => _x( 'MySpace Business Page URL', 'option label', 'nextgen-facebook' ),
							'tooltip' => 'myspace_publisher_url', 'input_class' => 'wide',
						),
					) as $key => $att ) {
						$table_rows[$key] = $this->form->get_th_html( $att['label'],
							null, $att['tooltip'], array( 'is_locale' => true ) ).
						'<td>'.$this->form->get_input( SucomUtil::get_key_locale( $key,
							$this->p->options ), $att['input_class'] ).'</td>';
					}

					break;
			}
			return $table_rows;
		}
	}
}

?>
