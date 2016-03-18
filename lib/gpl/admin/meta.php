<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbGplAdminMeta' ) ) {

	class NgfbGplAdminMeta {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'meta_header_rows' => array(
					'user_header_rows' => 4,	// $table_rows, $form, $head, $mod
					'taxonomy_header_rows' => 4,	// $table_rows, $form, $head, $mod
				),
				'meta_media_rows' => array(
					'user_media_rows' => 4,		// $table_rows, $form, $head, $mod
					'taxonomy_media_rows' => 4,	// $table_rows, $form, $head, $mod
				),
			) );
		}

		public function filter_meta_header_rows( $table_rows, $form, $head, $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$table_rows[] = '<td colspan="2" align="center">'.$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$form_rows = array(
				'og_title' => array(
					'label' => _x( 'Default Title', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_title', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( $this->p->webpage->get_title( $this->p->options['og_title_len'],
						'...', $mod['use_post'], true, false, true, 'none' ), 'wide' ),	// $md_idx = 'none'
				),
				'og_desc' => array(
					'label' => _x( 'Default Description (Facebook / Open Graph, LinkedIn, Pinterest Rich Pin)', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $this->p->webpage->get_description( $this->p->options['og_desc_len'],
						'...', $mod['use_post'], true, true, true, 'none' ), '', '', $this->p->options['og_desc_len'] ),	// $md_idx = 'none'
				),
				'schema_desc' => array(
					'label' => _x( 'Google / Schema Description', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $this->p->webpage->get_description( $this->p->options['schema_desc_len'], 
						'...', $mod['use_post'] ), '', '', $this->p->options['schema_desc_len'] ),
				),
				'seo_desc' => array(
					'tr_class' => ( $this->p->options['add_meta_name_description'] ? '' : 'hide_in_basic' ),
					'label' => _x( 'Google Search / SEO Description', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-seo_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $this->p->webpage->get_description( $this->p->options['seo_desc_len'], 
						'...', $mod['use_post'], true, false ), '', '', $this->p->options['seo_desc_len'] ),	// $add_hashtags = false
				),
				'tc_desc' => array(
					'label' => _x( 'Twitter Card Description', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-tc_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $this->p->webpage->get_description( $this->p->options['tc_desc_len'],
						'...', $mod['use_post'] ), '', '', $this->p->options['tc_desc_len'] ),
				),
				'sharing_url' => array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Sharing URL', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-sharing_url', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( $this->p->util->get_sharing_url( $mod['use_post'] ), 'wide' ),
				),
			);

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}

		public function filter_meta_media_rows( $table_rows, $form, $head, $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$media_info = $this->p->og->get_the_media_info( $this->p->cf['lca'].'-opengraph', 
				$mod, 'none', array( 'pid', 'img_url' ), $head );	// md_pre = none
			$table_rows[] = '<td colspan="2" align="center">'.$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$form_rows = array(
				'subsection_opengraph' => array(
					'td_class' => 'subsection top',
					'header' => 'h4',
					'label' => _x( 'All Social Websites / Open Graph', 'metabox title', 'nextgen-facebook' )
				),
				'subsection_priority_image' => array(
					'header' => 'h5',
					'label' => _x( 'Priority Image Information', 'metabox title', 'nextgen-facebook' )
				),
				'og_img_dimensions' => array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Image Dimensions', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'og_img_dimensions', 'td_class' => 'blank',
					'content' => $form->get_no_image_dimensions_input( 'og_img', true, false, true ),
				),
				'og_img_id' => array(
					'label' => _x( 'Image ID', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_img_id', 'td_class' => 'blank',
					'content' => $form->get_no_image_upload_input( 'og_img', $media_info['pid'], true ),
				),
				'og_img_url' => array(
					'label' => _x( 'or an Image URL', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_img_url', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( $media_info['img_url'], 'wide' ),
				),
				'subsection_priority_video' => array(
					'header' => 'h5',
					'label' => _x( 'Priority Video Information', 'metabox title', 'nextgen-facebook' )
				),
				'og_vid_embed' => array(
					'label' => _x( 'Video Embed HTML', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_vid_embed', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( '' ),
				),
				'og_vid_url' => array(
					'label' => _x( 'or a Video URL', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_vid_url', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( '', 'wide' ),
				),
				'og_vid_title' => array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Video Name / Title', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_vid_title', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( '', 'wide' ),
				),
				'og_vid_desc' => array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Video Description', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_vid_desc', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( '', 'wide' ),
				),
				'og_vid_prev_img' => array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Include Preview Image(s)', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_vid_prev_img', 'td_class' => 'blank',
					'content' => $form->get_no_checkbox( 'og_vid_prev_img' ),
				),
			);

			if ( ! SucomUtil::get_const( 'NGFB_RICH_PIN_DISABLE' ) ) {
				$form_rows['subsection_pinterest'] = array(
					'tr_class' => 'hide_in_basic',
					'td_class' => 'subsection',
					'header' => 'h4',
					'label' => _x( 'Pinterest / Rich Pin', 'metabox title', 'nextgen-facebook' )
				);
				$form_rows['rp_img_dimensions'] = array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Image Dimensions', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'rp_img_dimensions', 'td_class' => 'blank',
					'content' => $form->get_no_image_dimensions_input( 'rp_img', true, false, true ),
				);
				$form_rows['rp_img_id'] = array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Image ID', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-rp_img_id', 'td_class' => 'blank',
					'content' => $form->get_no_image_upload_input( 'rp_img', $media_info['pid'], true ),
				);
				$form_rows['rp_img_url'] = array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'or an Image URL', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-rp_img_url', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( $media_info['img_url'], 'wide' ),
				);
			}

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}
	}
}

?>