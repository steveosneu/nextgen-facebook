<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbGplUtilUser' ) && class_exists( 'NgfbUser' ) ) {

	// NgfbGplUtilUser extends NgfbUser which extends NgfbMeta
	class NgfbGplUtilUser extends NgfbUser {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$this->add_actions();
		}

		public function get_meta_image( $num, $size_name, $id, $mod_name = 'user', $check_dupes = true, $force_regen = false, $md_pre = 'og', $mt_pre = 'og' ) {
			return $this->not_implemented( __METHOD__, array() );
		}

		public function get_og_image( $num, $size_name, $id, $check_dupes = true, $force_regen = false, $md_pre = 'og' ) {
			return $this->not_implemented( __METHOD__, array() );
		}

		public function get_og_video( $num, $id, $check_dupes = false, $md_pre = 'og', $mt_pre = 'og' ) {
			return $this->not_implemented( __METHOD__, array() );
		}
	}
}

?>
