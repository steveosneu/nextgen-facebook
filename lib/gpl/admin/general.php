<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbGplAdminGeneral' ) ) {

	class NgfbGplAdminGeneral {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'og_author_rows' => 2,
				'og_videos_rows' => 2,
			) );
		}

		public function filter_og_author_rows( $rows, $form ) {

			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';
		
			$rows[] = $this->p->util->get_th( __( 'Include Author Gravatar Image',
				'nextgen-facebook' ), null, 'og_author_gravatar' ).
			'<td class="blank"><input type="checkbox" disabled="disabled" /></td>';

			return $rows;
		}

		public function filter_og_videos_rows( $rows, $form ) {

			$rows[] = '<td colspan="2" align="center">'.
				'<p>'.__( 'Video discovery and integration modules are provided with the Pro version.',
					'nextgen-facebook' ).'</p>'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';
		
			$rows[] = $this->p->util->get_th( __( 'Maximum Videos to Include',
				'nextgen-facebook' ), null, 'og_vid_max' ).
			'<td class="blank">'.$this->p->options['og_vid_max'].'</td>';
	
			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Use HTTPS for Video API Calls',
				'nextgen-facebook' ), null, 'og_vid_https' ).
			'<td class="blank"><input type="checkbox" disabled="disabled" /></td>';

			$rows[] = $this->p->util->get_th( __( 'Include Video Preview Image(s)',
				'nextgen-facebook' ), null, 'og_vid_prev_img' ).
			'<td class="blank"><input type="checkbox" disabled="disabled" />'.
			'&nbsp;&nbsp;video preview images (when available) are included first</td>';

			$rows[] = $this->p->util->get_th( __( 'Include Embed text/html Type',
				'nextgen-facebook' ), null, 'og_vid_html_type' ).
			'<td class="blank"><input type="checkbox" disabled="disabled" /></td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Default / Fallback Video URL',
				'nextgen-facebook' ), null, 'og_def_vid_url' ).
			'<td class="blank">'.$this->p->options['og_def_vid_url'].'</td>';
	
			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Use Default Video on Indexes',
				'nextgen-facebook' ), null, 'og_def_vid_on_index' ).
			'<td class="blank"><input type="checkbox" disabled="disabled" /></td>';
	
			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Use Default Video on Search Results',
				'nextgen-facebook' ), null, 'og_def_vid_on_search' ).
			'<td class="blank"><input type="checkbox" disabled="disabled" /></td>';

			return $rows;
		}
	}
}

?>
