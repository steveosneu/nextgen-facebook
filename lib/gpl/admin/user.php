<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbGplAdminUser' ) ) {

	class NgfbGplAdminUser {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'user_header_rows' => 3,
				'user_media_rows' => 3,
			) );
		}

		public function filter_user_header_rows( $rows, $form, $head_info ) {

			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$rows[] = $this->p->util->get_th( __( 'Default Title',
				'nextgen-facebook' ), 'medium', 'meta-og_title', $head_info ). 
			'<td class="blank">'.$this->p->webpage->get_title( $this->p->options['og_title_len'],
				'...', false, true, false, true, 'none' ).'</td>';	// $use_post = false, $md_idx = 'none'
		
			$rows[] = $this->p->util->get_th( __( 'Default (Facebook / Open Graph, LinkedIn, Pinterest Rich Pin) Description',
				'nextgen-facebook' ), 'medium', 'meta-og_desc', $head_info ).
			'<td class="blank">'.$this->p->webpage->get_description( $this->p->options['og_desc_len'],
				'...', false, true, true, true, 'none' ).'</td>';	// $use_post = false, $md_idx = 'none'
	
			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Google+ / Schema Description',
				'nextgen-facebook' ), 'medium', 'meta-schema_desc', $head_info ).
			'<td class="blank">'.$this->p->webpage->get_description( $this->p->options['schema_desc_len'],
				'...', false ).'</td>';				// $use_post = false
	
			$rows[] = $this->p->util->get_th( __( 'Google Search / SEO Description',
				'nextgen-facebook' ), 'medium', 'meta-seo_desc', $head_info ).
			'<td class="blank">'.$this->p->webpage->get_description( $this->p->options['seo_desc_len'],
				'...', false, true, false ).'</td>';		// $use_post = false, $add_hashtags = false

			$rows[] = $this->p->util->get_th( __( 'Twitter Card Description',
				'nextgen-facebook' ), 'medium', 'meta-tc_desc', $head_info ).
			'<td class="blank">'.$this->p->webpage->get_description( $this->p->options['tc_desc_len'],
				'...', false ).'</td>';				// $use_post = false

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Sharing URL',
				'nextgen-facebook' ), 'medium', 'meta-sharing_url', $head_info ).
			'<td class="blank">'.$this->p->util->get_sharing_url( false ).'</td>';	// $use_post = false

			return $rows;
		}

		public function filter_user_media_rows( $rows, $form, $head_info ) {

			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$rows[] = '<td colspan="2" class="subsection"><h4 
				style="margin-top:0;">'.__( 'All Social Websites / Open Graph',
					'nextgen-facebook' ).'</h4></td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Image Dimensions',
				'nextgen-facebook' ), 'medium', 'og_img_dimensions' ).
			'<td class="blank">'.$form->get_image_dimensions_text( 'og_img', true ).'</td>';

			$rows[] = $this->p->util->get_th( __( 'Image ID',
				'nextgen-facebook' ), 'medium', 'meta-og_img_id', $head_info ).
			'<td class="blank">&nbsp;</td>';

			$rows[] = $this->p->util->get_th( __( 'or an Image URL',
				'nextgen-facebook' ), 'medium', 'meta-og_img_url', $head_info ).
			'<td class="blank">&nbsp;</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Maximum Images',
				'nextgen-facebook' ), 'medium', 'meta-og_img_max', $head_info ).
			'<td class="blank">'.$this->p->options['og_img_max'].'</td>';

			$rows[] = $this->p->util->get_th( __( 'Video Embed HTML',
				'nextgen-facebook' ), 'medium', 'meta-og_vid_embed', $head_info ).
			'<td class="blank">&nbsp;</td>';

			$rows[] = $this->p->util->get_th( __( 'or a Video URL',
				'nextgen-facebook' ), 'medium', 'meta-og_vid_url', $head_info ).
			'<td class="blank">&nbsp;</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Maximum Videos',
				'nextgen-facebook' ), 'medium', 'meta-og_vid_max', $head_info ).
			'<td class="blank">'.$this->p->options['og_vid_max'].'</td>';

			$rows[] = $this->p->util->get_th( __( 'Include Preview Image(s)',
				'nextgen-facebook' ), 'medium', 'meta-og_vid_prev_img', $head_info ).
			'<td class="blank">'.$form->get_no_checkbox( 'og_vid_prev_img' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			'<td colspan="2" class="subsection"><h4>'.__( 'Pinterest (Rich Pin)',
				'nextgen-facebook' ).'</h4></td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Image Dimensions',
				'nextgen-facebook' ), 'medium', 'rp_img_dimensions' ).
			'<td class="blank">'.$form->get_image_dimensions_text( 'rp_img', true ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'Image ID',
				'nextgen-facebook' ), 'medium', 'meta-rp_img_id', $head_info ).
			'<td class="blank">&nbsp;</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( __( 'or an Image URL',
				'nextgen-facebook' ), 'medium', 'meta-rp_img_url', $head_info ).
			'<td class="blank">&nbsp;</td>';

			return $rows;
		}
	}
}

?>
