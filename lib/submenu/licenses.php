<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuLicenses' ) && class_exists( 'NgfbAdmin' ) ) {

	class NgfbSubmenuLicenses extends NgfbAdmin {

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
			add_meta_box( $this->pagehook.'_licenses',
				_x( 'Pro Licenses and Extension Plugins', 'metabox title', 'nextgen-facebook' ),
					array( &$this, 'show_metabox_licenses' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_licenses() {
			$this->licenses_metabox_content( false );	// $network = false
		}
	}
}

?>
