<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbAdmin' ) ) {

	class NgfbAdmin {
	
		protected $p;
		protected $menu_id;
		protected $menu_name;
		protected $pagehook;

		protected static $is;
		protected static $readme_info = array();

		public $form;
		public $lang = array();
		public $submenu = array();

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				// nothing to do

			} else {
				load_plugin_textdomain( 'nextgen-facebook', false, dirname( NGFB_PLUGINBASE ).'/languages/' );

				$this->set_objects();
				$this->pro_req_notices();
				$this->conflict_warnings();

				add_action( 'admin_init', array( &$this, 'register_setting' ) );
				add_action( 'admin_menu', array( &$this, 'add_admin_menus' ), NGFB_ADD_MENU_PRIORITY );
				add_action( 'admin_menu', array( &$this, 'add_admin_settings' ), NGFB_ADD_SETTINGS_PRIORITY );
				add_action( 'activated_plugin', array( &$this, 'check_activated_plugin' ), 10, 2 );

				add_filter( 'current_screen', array( &$this, 'screen_notices' ) );
				add_filter( 'plugin_action_links', array( &$this, 'add_plugin_action_links' ), 10, 2 );
	
				if ( is_multisite() ) {
					add_action( 'network_admin_menu', array( &$this, 'add_network_admin_menus' ), NGFB_ADD_MENU_PRIORITY );
					add_action( 'network_admin_edit_'.NGFB_SITE_OPTIONS_NAME, array( &$this, 'save_site_options' ) );
					add_filter( 'network_admin_plugin_action_links', array( &$this, 'add_plugin_action_links' ), 10, 2 );
				}

				add_filter( 'get_user_option_wpseo_dismissed_conflicts', 
					array( &$this, 'dismiss_wpseo_notice' ), 10, 3 );
			}

		}

		// load all submenu classes into the $this->submenu array
		// the id of each submenu item must be unique
		private function set_objects() {
			self::$is = $this->p->check->aop( $this->p->cf['lca'], true, 
				$this->p->is_avail['aop'] ) ? ' Pro' : ' Free';
			$menus = array( 
				'submenu', 
				'setting'	// setting must be last to extend submenu/advanced.php
			);
			if ( is_multisite() )
				$menus[] = 'sitesubmenu';
			foreach ( $menus as $sub ) {
				foreach ( $this->p->cf['plugin'] as $lca => $info ) {
					if ( isset( $info['lib'][$sub] ) ) {
						foreach ( $info['lib'][$sub] as $id => $name ) {
							if ( strpos( $id, 'separator' ) !== false ) 
								continue;
							$classname = apply_filters( $lca.'_load_lib', false, $sub.'/'.$id );
							if ( $classname !== false && class_exists( $classname ) )
								$this->submenu[$id] = new $classname( $this->p, $id, $name );
						}
					}
				}
			}
		}

		public function screen_notices( $screen ) {
			$lca = $this->p->cf['lca'];
			$screen_id = SucomUtil::get_screen_id( $screen );
			switch ( $screen_id ) {
				case 'dashboard':
				case ( strpos( $screen_id, '_page_'.$lca.'-' ) !== false ? true : false ):
					$this->timed_notices();
					break;
			}
		}

		public function timed_notices( $store = false ) {

			if ( ! $this->p->notice->can_dismiss ||			// true for wordpress 4.2+
				! current_user_can( 'manage_options' ) )
					return;

			global $wp_version;
			$wp_name = 'WordPress version '.$wp_version;
			$user_id = get_current_user_id();
			$dis_arr = empty( $user_id ) ? false : 			// just in case
				get_user_option( NGFB_DISMISS_NAME, $user_id );	// get dismissed message ids
			$ts = $this->p->util->get_all_times();
			$now_time = time();
			$few_days = $now_time - ( $this->p->cf['form']['time_by_name']['day'] * 3 );
			$few_weeks = $now_time - ( $this->p->cf['form']['time_by_name']['day'] * 21 );
			$type = 'inf';

			foreach ( $this->p->cf['plugin'] as $lca => $info ) {
				if ( empty( $info['version'] ) ||		// must be an active plugin
					empty( $info['url']['review'] ) )	// must be hosted on wordpress.org
						continue;

				$plugin_name = $info['name'].' version '.$info['version'];
				$msg_id_works = 'ask-'.$lca.'-'.$info['version'].'-plugin-works';	// unique for every version
				$msg_id_review = 'ask-'.$lca.'-plugin-review';
				$help_links = '<li>Got questions or need some help?';
				if ( ! empty( $info['url']['pro_support'] ) && 
					$this->p->check->aop( $lca, true, $this->p->is_avail['aop'] ) )
						$help_links .= ' <a href="'.$info['url']['pro_support'].'" target="_blank">Open a new ticket on the '.
							$info['short'].' Pro support website</a>.';
				elseif ( ! empty( $info['url']['wp_support'] ) )
					$help_links .= ' <a href="'.$info['url']['wp_support'].'" target="_blank">Open a new thread in the '.
						$info['short'].' Free version support forum</a>.';
				$help_links .= '</li>';

				if ( ! isset( $dis_arr[$type.'_'.$msg_id_works] ) && 
					isset( $ts[$lca.'_update_time'] ) && 
						$ts[$lca.'_update_time'] < $few_days ) {

					$this->p->notice->log( $type, '<b>Excellent!</b> It looks like you\'ve been running <b>'.
					$plugin_name.'</b> for a few days &mdash; How\'s it working with <b>'.$wp_name.'</b>?<ul>'.
					'<li><a href="https://wordpress.org/plugins/'.$info['slug'].'/?compatibility[version]='.$wp_version.
					'&compatibility[topic_version]='.$info['version'].'&compatibility[compatible]=1" target="_blank">'.
					'Let us know with your "Works" vote on wordpress.org!</a></li>'.
					$help_links.'</ul>', $store, $user_id, $msg_id_works, true, array( 'label' => false ) );

				} elseif ( ! isset( $dis_arr[$type.'_'.$msg_id_review] ) && 
					isset( $ts[$lca.'_install_time'] ) && 
						$ts[$lca.'_install_time'] < $few_weeks ) {

					$this->p->notice->log( $type, '<b>Fantastic!</b> It looks like you\'ve been running <b>'.
					$info['name'].'</b> for a few weeks &mdash; How do you like it so far?<ul>'.
					'<li><a href="'.$info['url']['review'].'" target="_blank">'.
					'Let us know with a 5-star rating and wonderful review on wordpress.org!</a> ;-)</li>'.
					$help_links.'</ul>', $store, $user_id, $msg_id_review, true, array( 'label' => false ) );
				}
			}
		}

		private function pro_req_notices() {
			// check that ngfb pro has an authentication id
			$lca = $this->p->cf['lca'];
			if ( $this->p->is_avail['aop'] === true && 
				empty( $this->p->options['plugin_'.$lca.'_tid'] ) && 
					( empty( $this->p->options['plugin_'.$lca.'_tid:is'] ) || 
						$this->p->options['plugin_'.$lca.'_tid:is'] !== 'disabled' ) )
							$this->p->notice->nag( $this->p->msgs->get( 'pro-activate-msg' ) );
			// check all *active* plugins / extensions to make sure pro version is installed
			$has_tid = false;
			foreach ( $this->p->cf['plugin'] as $lca => $info ) {
				if ( ! empty( $this->p->options['plugin_'.$lca.'_tid'] ) &&
					isset( $info['base'] ) && SucomUtil::active_plugins( $info['base'] ) ) {
					$has_tid = true;
					if ( ! $this->p->check->aop( $lca, false ) )
						$this->p->notice->inf( $this->p->msgs->get( 'pro-not-installed', array( 'lca' => $lca ) ), true );
				}
			}
			// if we have at least one tid, make sure the update manager is installed
			if ( $has_tid === true && ! $this->p->is_avail['util']['um'] ) {
				if ( ! function_exists( 'get_plugins' ) )
					require_once( ABSPATH.'wp-admin/includes/plugin.php' );
				$installed_plugins = get_plugins();
				if ( ! empty( $this->p->cf['plugin']['ngfbum']['base'] ) &&
					is_array( $installed_plugins[$this->p->cf['plugin']['ngfbum']['base']] ) )
						$this->p->notice->nag( $this->p->msgs->get( 'pro-um-activate-extension' ), true );
				else $this->p->notice->nag( $this->p->msgs->get( 'pro-um-extension-required' ), true );
			}
		}

		public function check_activated_plugin( $plugin = false, $sitewide = false ) {
			$lca = $this->p->cf['lca'];
			$um_base = $this->p->cf['plugin'][$lca.'um']['base'];
			switch ( $plugin ) {
				case $um_base:
					$this->p->notice->trunc( 'nag' );
					break;
			}
		}

		protected function set_form_property() {
			$def_opts = $this->p->opt->get_defaults();
			$this->form = new SucomForm( $this->p, NGFB_OPTIONS_NAME, $this->p->options, $def_opts );
		}

		protected function &get_form_reference() {	// returns a reference
			return $this->form;
		}

		public function register_setting() {
			register_setting( $this->p->cf['lca'].'_setting', 
				NGFB_OPTIONS_NAME, array( &$this, 'registered_setting_sanitation' ) );
		} 

		public function set_readme_info( $expire_secs = 86400 ) {
			foreach ( array_keys( $this->p->cf['plugin'] ) as $lca ) {
				if ( empty( self::$readme_info[$lca] ) )
					self::$readme_info[$lca] = $this->p->util->parse_readme( $lca, $expire_secs );
			}
		}

		public function add_admin_settings() {
			foreach ( $this->p->cf['*']['lib']['setting'] as $id => $name ) {
				$parent_slug = 'options-general.php';
				if ( array_key_exists( $id, $this->submenu ) ) {
					$this->submenu[$id]->add_submenu_page( $parent_slug );
				} else $this->add_submenu_page( $parent_slug, $id, $name );
			}
		}

		public function add_network_admin_menus() {
			$this->add_admin_menus( $this->p->cf['*']['lib']['sitesubmenu'] );
		}

		public function add_admin_menus( $submenus = false ) {

			if ( ! is_array( $submenus ) )
				$submenus = $this->p->cf['*']['lib']['submenu'];

			$this->menu_id = key( $submenus );
			$this->menu_name = $submenus[ $this->menu_id ];

			if ( array_key_exists( $this->menu_id, $this->submenu ) ) {
				$menu_slug = $this->p->cf['lca'].'-'.$this->menu_id;
				$this->submenu[$this->menu_id]->add_menu_page( $menu_slug );
			}

			foreach ( $submenus as $id => $name ) {
				$parent_slug = $this->p->cf['lca'].'-'.$this->menu_id;
				if ( array_key_exists( $id, $this->submenu ) )
					$this->submenu[$id]->add_submenu_page( $parent_slug );
				else $this->add_submenu_page( $parent_slug, $id, $name );
			}
		}

		protected function add_menu_page( $menu_slug ) {
			global $wp_version;
			// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
			$this->pagehook = add_menu_page( 
				$this->p->cf['plugin'][$this->p->cf['lca']]['short'].self::$is.' &mdash; '.$this->menu_name, 
				$this->p->cf['menu'].self::$is, 
				'manage_options', 
				$menu_slug, 
				array( &$this, 'show_form_page' ), 
				( version_compare( $wp_version, 3.8, '<' ) ? null : 'dashicons-share' ),
				NGFB_MENU_ORDER
			);
			add_action( 'load-'.$this->pagehook, array( &$this, 'load_form_page' ) );
		}

		protected function add_submenu_page( $parent_slug, $menu_id = false, $menu_name = false ) {
			$menu_id = $menu_id === false ? $this->menu_id : $menu_id;
			$menu_name = $menu_name === false ? $this->menu_name : $menu_name;

			if ( strpos( $menu_id, 'separator' ) !== false ) {
				$menu_title = '<div style="z-index:999;
					padding:2px 0;
					margin:0;
					cursor:default;
					border-bottom:1px dotted;
					color:#666;" onclick="return false;">'.
						( $menu_name === $this->p->cf['menu'] ? 
							$menu_name.self::$is : $menu_name ).'</div>';
				$menu_slug = '';
				$page_title = '';
				$function = '';
			} else {
				// highlight the "extension plugins" part of the menu title
				if ( strpos( $menu_name, 'Extension Plugins' ) !== false )
					$menu_title = preg_replace( '/(Extension Plugins)/',
						'<div style="color:#'.$this->p->cf['color'].';">$1</div>', $menu_name );
				else $menu_title = $menu_name;
				$menu_slug = $this->p->cf['lca'].'-'.$menu_id;
				$page_title = $this->p->cf['plugin'][$this->p->cf['lca']]['short'].self::$is.' &mdash; '.$menu_title;
				$function = array( &$this, 'show_form_page' );
			}
			// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
			$this->pagehook = add_submenu_page( 
				$parent_slug, 
				$page_title, 
				$menu_title, 
				'manage_options', 
				$menu_slug, 
				$function
			);
			if ( $function )
				add_action( 'load-'.$this->pagehook, array( &$this, 'load_form_page' ) );
		}

		// add links on the main plugins page
		public function add_plugin_action_links( $links, $file ) {

			if ( ! isset( $this->p->cf['*']['base'][$file] ) )
				return $links;

			$lca = $this->p->cf['*']['base'][$file];
			$info = $this->p->cf['plugin'][$lca];

			foreach ( $links as $num => $val )
				if ( strpos( $val, '>Edit<' ) !== false )
					unset ( $links[$num] );

			if ( ! empty( $info['url']['faq'] ) )
				$links[] = '<a href="'.$info['url']['faq'].'">'.
					__( 'FAQ', 'nextgen-facebook' ).'</a>';

			if ( ! empty( $info['url']['notes'] ) )
				$links[] = '<a href="'.$info['url']['notes'].'">'.
					__( 'Notes', 'nextgen-facebook' ).'</a>';

			if ( ! empty( $info['url']['latest_zip'] ) )
				$links[] = '<a href="'.$info['url']['latest_zip'].'">'.
					__( 'Download Latest', 'nextgen-facebook' ).'</a>';

			if ( ! empty( $info['url']['pro_support'] ) &&
				$this->p->check->aop( $lca, true, $this->p->is_avail['aop'] ) ) {
					$links[] = '<a href="'.$info['url']['pro_support'].'">'.
						__( 'Pro Support', 'nextgen-facebook' ).'</a>';
			} else {
				if ( ! empty( $info['url']['wp_support'] ) )
					$links[] = '<a href="'.$info['url']['wp_support'].'">'.
						__( 'Support Forum', 'nextgen-facebook' ).'</a>';

				if ( ! empty( $info['url']['purchase'] ) ) {
					if ( $this->p->check->aop( $lca, false, $this->p->is_avail['aop'] ) )
						$links[] = '<a href="'.$info['url']['purchase'].'">'.
							__( 'Purchase License', 'nextgen-facebook' ).'</a>';
					else $links[] = '<a href="'.$info['url']['purchase'].'">'.
						__( 'Purchase Pro', 'nextgen-facebook' ).'</a>';
				}
			}

			return $links;
		}

		// this method receives only a partial options array, so re-create a full one
		// wordpress handles the actual saving of the options
		public function registered_setting_sanitation( $opts ) {
			$network = false;
			if ( ! is_array( $opts ) ) {
				add_settings_error( NGFB_OPTIONS_NAME, 'notarray', '<b>'.$this->p->cf['uca'].' Error</b> : '.
					__( 'Submitted options are not an array.', 'nextgen-facebook' ), 'error' );
				return $opts;
			}
			// get default values, including css from default stylesheets
			$def_opts = $this->p->opt->get_defaults();
			$opts = SucomUtil::restore_checkboxes( $opts );
			$opts = array_merge( $this->p->options, $opts );
			$this->p->notice->trunc();	// clear all messages before sanitation checks
			$opts = $this->p->opt->sanitize( $opts, $def_opts, $network );
			$opts = apply_filters( $this->p->cf['lca'].'_save_options', $opts, NGFB_OPTIONS_NAME, $network );
			$clear_cache_link = wp_nonce_url( $this->p->util->get_admin_url( '?action=clear_all_cache' ), $this->get_nonce(), NGFB_NONCE );
			$this->p->notice->inf( __( 'Plugin settings have been updated.', 'nextgen-facebook' ).' '.
				sprintf( __( 'Wait %1$d seconds for cache objects to expire or <a href="%2$s">Clear All Cache(s)</a> now.',
					'nextgen-facebook' ), $this->p->options['plugin_object_cache_exp'], $clear_cache_link ), true );
			return $opts;
		}

		public function save_site_options() {
			$network = true;
			$page = empty( $_POST['page'] ) ? 
				key( $this->p->cf['*']['lib']['sitesubmenu'] ) : $_POST['page'];

			if ( empty( $_POST[ NGFB_NONCE ] ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'nonce token validation post field missing' );
				wp_redirect( $this->p->util->get_admin_url( $page ) );
				exit;
			} elseif ( ! wp_verify_nonce( $_POST[ NGFB_NONCE ], $this->get_nonce() ) ) {
				$this->p->notice->err( __( 'Nonce token validation failed for network options (update ignored).',
					'nextgen-facebook' ), true );
				wp_redirect( $this->p->util->get_admin_url( $page ) );
				exit;
			} elseif ( ! current_user_can( 'manage_network_options' ) ) {
				$this->p->notice->err( __( 'Insufficient privileges to modify network options.',
					'nextgen-facebook' ), true );
				wp_redirect( $this->p->util->get_admin_url( $page ) );
				exit;
			}

			$def_opts = $this->p->opt->get_site_defaults();
			$opts = empty( $_POST[NGFB_SITE_OPTIONS_NAME] ) ? $def_opts : 
				SucomUtil::restore_checkboxes( $_POST[NGFB_SITE_OPTIONS_NAME] );
			$opts = array_merge( $this->p->site_options, $opts );
			$this->p->notice->trunc();	// clear all messages before sanitation checks
			$opts = $this->p->opt->sanitize( $opts, $def_opts, $network );
			$opts = apply_filters( $this->p->cf['lca'].'_save_site_options', $opts, $def_opts, $network );
			update_site_option( NGFB_SITE_OPTIONS_NAME, $opts );
			$this->p->notice->inf( __( 'Plugin settings have been updated.', 'nextgen-facebook' ), true );
			wp_redirect( $this->p->util->get_admin_url( $page ).'&settings-updated=true' );
			exit;	// stop here
		}

		public function load_single_page() {
			wp_enqueue_script( 'postbox' );
			$this->p->admin->submenu[$this->menu_id]->add_meta_boxes();
		}

		public function load_form_page() {
			wp_enqueue_script( 'postbox' );

			if ( ! empty( $_GET['action'] ) ) {
				if ( empty( $_GET[ NGFB_NONCE ] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'nonce token validation query field missing' );
				} elseif ( ! wp_verify_nonce( $_GET[ NGFB_NONCE ], $this->get_nonce() ) ) {
					$this->p->notice->err( __( 'Nonce token validation failed for plugin action (action ignored).', 'nextgen-facebook' ) );
				} else {
					switch ( $_GET['action'] ) {
						case 'check_for_updates': 
							if ( $this->p->is_avail['util']['um'] ) {
								self::$readme_info = array();
								$ngfbum = NgfbUm::get_instance();
								$ngfbum->update->check_for_updates( null, true, false );
							} else {
								$um_lca = $this->p->cf['lca'].'um';
								$um_name = $this->p->cf['plugin'][$um_lca]['name'];
								$this->p->notice->err( sprintf( __( 'The <strong>%s</strong> extension is required to check for plugin and extension updates.', 'nextgen-facebook' ), $um_name ) );
							}
							break;

						case 'clear_all_cache': 
							$this->p->util->clear_all_cache();
							break;

						case 'clear_metabox_prefs': 
							$user = get_userdata( get_current_user_id() );
							$user_name = $user->first_name.' '.$user->last_name;
							NgfbUser::delete_metabox_prefs( $user->ID );
							$this->p->notice->inf( sprintf( __( 'Metabox layout preferences for user "%s" have been reset.', 'nextgen-facebook' ), $user_name ) );
							break;

						case 'clear_hidden_notices': 
							$user = get_userdata( get_current_user_id() );
							$user_name = $user->first_name.' '.$user->last_name;
							delete_user_option( $user->ID, NGFB_DISMISS_NAME );
							$this->p->notice->inf( sprintf( __( 'Hidden notices for user "%s" have been cleared.', 'nextgen-facebook' ), $user_name ) );
							break;

						case 'change_show_options': 
							if ( isset( $this->p->cf['form']['show_options'][$_GET['show_opts']] ) )
								NgfbUser::save_pref( array( 'show_opts' => $_GET['show_opts'] ) );
							break;
					}
				}
			}

			// the plugin information metabox on all settings pages needs this
			$this->p->admin->set_readme_info( $this->p->cf['feed_cache_exp'] );

			// add child metaboxes first, since they contain the default reset_metabox_prefs()
			$this->p->admin->submenu[ $this->menu_id ]->add_meta_boxes();

			if ( empty( $this->p->options['plugin_'.$this->p->cf['lca'].'_tid'] ) || 
				! $this->p->check->aop( $this->p->cf['lca'], true, $this->p->is_avail['aop'] ) ) {

				add_meta_box( $this->pagehook.'_purchase', __( 'Pro Version', 'nextgen-facebook' ), 
					array( &$this, 'show_metabox_purchase' ), $this->pagehook, 'side' );
				add_filter( 'postbox_classes_'.$this->pagehook.'_'.$this->pagehook.'_purchase', 
					array( &$this, 'add_class_postbox_highlight_side' ) );
				$this->p->mods['util']['user']->reset_metabox_prefs( $this->pagehook, 
					array( 'purchase' ), null, 'side', true );
			}

			add_meta_box( $this->pagehook.'_info', __( 'Version Information', 'nextgen-facebook' ), 
				array( &$this, 'show_metabox_info' ), $this->pagehook, 'side' );

			add_meta_box( $this->pagehook.'_status_gpl', __( 'Basic / Common Features', 'nextgen-facebook' ), 
				array( &$this, 'show_metabox_status_gpl' ), $this->pagehook, 'side' );

			add_meta_box( $this->pagehook.'_status_pro', __( 'Pro Version Features', 'nextgen-facebook' ), 
				array( &$this, 'show_metabox_status_pro' ), $this->pagehook, 'side' );

			add_meta_box( $this->pagehook.'_help', __( 'Help and Support', 'nextgen-facebook' ), 
				array( &$this, 'show_metabox_help' ), $this->pagehook, 'side' );

		}

		public function show_single_page() {
			?>
			<div class="wrap" id="<?php echo $this->pagehook; ?>">
				<h1><?php $this->show_follow_icons(); echo $this->menu_name; ?></h1>
				<div id="poststuff" class="metabox-holder">
					<div id="post-body" class="">
						<div id="post-body-content" class="">
							<?php $this->show_single_content(); ?>
						</div><!-- .post-body-content -->
					</div><!-- .post-body -->
				</div><!-- .metabox-holder -->
			</div><!-- .wrap -->
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( 
					function($) {
						$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
						postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
					}
				);
				//]]>
			</script>
			<?php
		}

		public function show_form_page() {

			if ( ! $this->is_setting( $this->menu_id ) )	// the "setting" pages display their own error messages
				settings_errors( NGFB_OPTIONS_NAME );	// display "error" and "updated" messages

			$this->set_form_property();			// define form for side boxes and show_form_content()

			if ( $this->p->debug->enabled ) {
				$this->p->debug->show_html( print_r( $this->p->is_avail, true ), 'available features' );
				$this->p->debug->show_html( print_r( NgfbUtil::active_plugins(), true ), 'active plugins' );
				$this->p->debug->show_html( null, 'debug log' );
			}
			?>

			<div class="wrap" id="<?php echo $this->pagehook; ?>">
				<h1><?php $this->show_follow_icons(); 
					echo $this->p->cf['plugin'][$this->p->cf['lca']]['short'].
						self::$is.' &ndash; '.$this->menu_name; ?></h1>
				<div id="poststuff" class="metabox-holder has-right-sidebar">
					<div id="side-info-column" class="inner-sidebar">
						<?php do_meta_boxes( $this->pagehook, 'side', null ); ?>
					</div><!-- .inner-sidebar -->
					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">
							<?php $this->show_form_content(); ?>
						</div><!-- .post-body-content -->
					</div><!-- .post-body -->
				</div><!-- .metabox-holder -->
			</div><!-- .wrap -->
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( 
					function($) {
						// close postboxes that should be closed
						$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
						// postboxes setup
						postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
					}
				);
				//]]>
			</script>
			<?php
		}

		public function add_class_postbox_highlight_side( $classes ) {
			array_push( $classes, 'postbox_highlight_side' );
			return $classes;
		}

		protected function show_single_content() {
			do_meta_boxes( $this->pagehook, 'normal', null ); 
		}

		protected function show_form_content() {

			if ( $this->is_submenu( $this->menu_id ) ||
				$this->is_setting( $this->menu_id ) ) {

				echo '<form name="'.$this->p->cf['lca'].'" 
					id="'.$this->p->cf['lca'].'_settings_form" 
					action="options.php" method="post">';

				settings_fields( $this->p->cf['lca'].'_setting' ); 

			} elseif ( $this->is_sitesubmenu( $this->menu_id ) ) {

				echo '<form name="'.$this->p->cf['lca'].'" 
					id="'.$this->p->cf['lca'].'_settings_form" 
					action="edit.php?action='.NGFB_SITE_OPTIONS_NAME.'" method="post">';
				echo '<input type="hidden" name="page" value="'.$this->menu_id.'">';
			}

			// wp_nonce_field( $action, $name, $referer, $echo
			// $name = the hidden form field to be created (aka $_POST[$name]).
			wp_nonce_field( $this->get_nonce(), NGFB_NONCE );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

			do_meta_boxes( $this->pagehook, 'normal', null ); 

			do_action( $this->p->cf['lca'].'_form_content_metaboxes_'.$this->menu_id, $this->pagehook );

			switch ( $this->menu_id ) {
				case 'readme':
				case 'setup':
				case 'sitereadme':
				case 'sitesetup':
					break;
				default:
					echo $this->get_submit_buttons();
					break;
			}
			echo '</form>', "\n";
		}

		public function show_metabox_info() {
			echo '<table class="sucom-setting '.$this->p->cf['lca'].' side">';
			foreach ( $this->p->cf['plugin'] as $lca => $info ) {

				if ( empty( $info['version'] ) )	// filter out extensions that are not active
					continue;

				$stable_version = __( 'N/A', 'nextgen-facebook' );
				$latest_version = __( 'N/A', 'nextgen-facebook' );
				$installed_version = $info['version'];
				$installed_style = '';
				$latest_notice = '';
				$changelog_url = $info['url']['changelog'];

				// the readme_info array is populated by set_readme_info(), which is called from load_form_page()
				if ( ! empty( self::$readme_info[$lca]['stable_tag'] ) ) {
					$stable_version = self::$readme_info[$lca]['stable_tag'];
					$upgrade_notice = self::$readme_info[$lca]['upgrade_notice'];
					if ( is_array( $upgrade_notice ) ) {
						reset( $upgrade_notice );
						$latest_version = key( $upgrade_notice );
						$latest_notice = $upgrade_notice[$latest_version];
					}
					$installed_style = version_compare( $installed_version, $stable_version, '<' ) ?
						'style="background-color:#f00;"' : 
						'style="background-color:#0f0;"';
				}

				echo '<tr><td colspan="2"><h4>'.$info['short'].
					( $this->p->check->aop( $lca, true, 
						$this->p->is_avail['aop'] ) ? ' Pro' : ' Free' ).'</h4></td></tr>';
				echo '<tr><th class="side">'.__( 'Installed', 'nextgen-facebook' ).':</th>
					<td class="side_version" '.$installed_style.'>'.$installed_version.'</td></tr>';
				echo '<tr><th class="side">'.__( 'Stable', 'nextgen-facebook' ).':</th>
					<td class="side_version">'.$stable_version.'</td></tr>';
				echo '<tr><th class="side">'.__( 'Latest', 'nextgen-facebook' ).':</th>
					<td class="side_version">'.$latest_version.'</td></tr>';
				echo '<tr><td colspan="2" id="latest_notice"><p>'.$latest_notice.'</p>'.
					'<p><a href="'.$changelog_url.'" target="_blank">'.
						sprintf( __( 'View %s changelog...', 'nextgen-facebook' ), $info['short'] ).'</a></p></td></tr>';
			}
			echo '</table>';
		}

		public function show_metabox_status_gpl() {
			$metabox = 'status';
			$plugin_count = 0;
			foreach ( $this->p->cf['plugin'] as $lca => $info )
				if ( isset( $info['lib']['gpl'] ) )
					$plugin_count++;
			echo '<table class="sucom-setting '.$this->p->cf['lca'].' side"
				style="margin-bottom:10px;">';
			/*
			 * GPL version features
			 */
			foreach ( $this->p->cf['plugin'] as $lca => $info ) {
				if ( ! isset( $info['lib']['gpl'] ) )
					continue;
				if ( $lca === $this->p->cf['lca'] )	// features for this plugin
					$features = array(
						'Author JSON-LD' => array( 
							'status' => $this->p->options['schema_author_json'] ? 'on' : 'rec',
						),
						'Debug Messages' => array(
							'classname' => 'SucomDebug',
						),
						'Non-Persistant Cache' => array(
							'status' => $this->p->is_avail['cache']['object'] ? 'on' : 'rec',
						),
						'Open Graph / Rich Pin' => array( 
							'status' => class_exists( $this->p->cf['lca'].'opengraph' ) ? 'on' : 'rec',
						),
						'Publisher JSON-LD' => array(
							'status' => $this->p->options['schema_publisher_json'] ? 'on' : 'rec',
						),
						'Transient Cache' => array(
							'status' => $this->p->is_avail['cache']['transient'] ? 'on' : 'rec',
						),
						'Twitter Cards' => array( 
							'status' => class_exists( $this->p->cf['lca'].'twittercard' ) ? 'on' : 'rec',
						),
					);
				else $features = array();

				$features = apply_filters( $lca.'_'.$metabox.'_gpl_features', $features, $lca, $info );

				if ( ! empty( $features ) ) {
					if ( $plugin_count > 1 )
						echo '<tr><td><h4>'.$this->p->cf['plugin'][$lca]['short'].'</h4></td></tr>';
					$this->show_plugin_status( $features );
				}
			}
			echo '</table>';
		}

		public function show_metabox_status_pro() {
			$metabox = 'status';
			$plugin_count = 0;
			foreach ( $this->p->cf['plugin'] as $lca => $info )
				if ( isset( $info['lib']['pro'] ) )
					$plugin_count++;
			echo '<table class="sucom-setting '.$this->p->cf['lca'].' side"
				style="margin-bottom:10px;">';
			/*
			 * Pro version features
			 */
			foreach ( $this->p->cf['plugin'] as $lca => $info ) {
				if ( ! isset( $info['lib']['pro'] ) )
					continue;
				$features = array();
				$aop = $this->p->check->aop( $lca, true, 
					$this->p->is_avail['aop'] );
				foreach ( $info['lib']['pro'] as $sub => $libs ) {
					if ( $sub === 'admin' ) 
						continue;	// skip status for admin menus and tabs
					foreach ( $libs as $id => $name ) {
						$off = $this->p->is_avail[$sub][$id] ? 'rec' : 'off';
						$features[$name] = array( 
							'status' => class_exists( $lca.'pro'.$sub.$id ) ? ( $aop ? 'on' : $off ) : $off,
							'tooltip' => 'If the '.$name.' plugin is detected, '.$this->p->cf['plugin'][$lca]['short'].
								' Pro will load integration modules to provide additional support and features for '.$name.'.',
							'td_class' => $aop ? '' : 'blank',
						);
					}
				}

				$features = apply_filters( $lca.'_'.$metabox.'_pro_features', $features, $lca, $info );

				if ( ! empty( $features ) ) {
					if ( $plugin_count > 1 )
						echo '<tr><td><h4>'.$this->p->cf['plugin'][$lca]['short'].'</h4></td></tr>';
					$this->show_plugin_status( $features );
				}
			}
			echo '</table>';
		}

		private function show_plugin_status( $features = array() ) {
			$images = array( 
				'on' => 'green-circle.png',
				'off' => 'gray-circle.png',
				'rec' => 'red-circle.png',
			);
			uksort( $features, 'strcasecmp' );
			$first = key( $features );
			foreach ( $features as $name => $arr ) {

				$td_class = empty( $arr['td_class'] ) ? '' : ' '.$arr['td_class'];

				if ( array_key_exists( 'classname', $arr ) )
					$status = class_exists( $arr['classname'] ) ? 'on' : 'off';
				elseif ( array_key_exists( 'status', $arr ) )
					$status = $arr['status'];
				else $status = '';

				if ( ! empty( $status ) ) {
					$tooltip_text = empty( $arr['tooltip'] ) ? '' : $arr['tooltip'];
					$tooltip_text = $this->p->msgs->get( 'tooltip-side-'.$name, 
						array( 'text' => $tooltip_text, 'class' => 'sucom_tooltip_side' ) );

					echo '<tr><td class="side'.$td_class.'">'.
					$tooltip_text.( $status == 'rec' ? '<strong>'.$name.'</strong>' : $name ).
					'</td><td style="min-width:0;text-align:center;" class="'.$td_class.'">
					<img src="'.NGFB_URLPATH.'images/'.$images[$status].'" width="12" height="12" /></td></tr>';
				}
			}
		}

		public function show_metabox_purchase() {
			$purchase_url = $this->p->cf['plugin'][$this->p->cf['lca']]['url']['purchase'];
			echo '<table class="sucom-setting '.$this->p->cf['lca'].'" side><tr><td>';
			echo $this->p->msgs->get( 'side-purchase' );
			echo '<p class="centered">';
			echo $this->form->get_button( 
				( $this->p->is_avail['aop'] ? 
					__( 'Purchase Pro License(s)', 'nextgen-facebook' ) :
					__( 'Purchase Pro Version', 'nextgen-facebook' ) ), 
				'button-primary', null, $purchase_url, true );
			echo '</p></td></tr></table>';
		}

		public function show_metabox_help() {
			echo '<table class="sucom-setting '.$this->p->cf['lca'].'" side><tr><td>';
			echo $this->p->msgs->get( 'side-help' );
			foreach ( $this->p->cf['plugin'] as $lca => $info ) {
				if ( empty( $info['version'] ) )	// filter out extensions that are not installed
					continue;

				$help_links = '';
				if ( ! empty( $info['url']['faq'] ) ) {
					$help_links .= '<li>Review <a href="'.$info['url']['faq'].'" target="_blank">FAQs</a>';
					if ( ! empty( $info['url']['notes'] ) )
						$help_links .= ' and <a href="'.$info['url']['notes'].'" target="_blank">Notes</a>';
					$help_links .= '</li>';
				}
				if ( ! empty( $info['url']['pro_support'] ) && 
					$this->p->check->aop( $lca, true, $this->p->is_avail['aop'] ) )
						$help_links .= '<li><a href="'.$info['url']['pro_support'].'" 
							target="_blank">Open a Support Ticket</a></li>';
				elseif ( ! empty( $info['url']['wp_support'] ) )
					$help_links .= '<li><a href="'.$info['url']['wp_support'].'" 
						target="_blank">Post in Support Forum</a></li>';

				if ( ! empty( $help_links ) ) {
					echo '<p><strong>'.$info['short'].
						( $this->p->check->aop( $lca, true, $this->p->is_avail['aop'] ) ? 
							' Pro' : ' Free' ).' Help</strong></p>';
					echo '<ul>'.$help_links.'</ul>';
				}
			}
			echo '</td></tr></table>';
		}

		protected function show_follow_icons() {
			echo '<div class="follow_icons">';
			$img_size = $this->p->cf['follow']['size'];
			foreach ( $this->p->cf['follow']['src'] as $img => $url )
				echo '<a href="'.$url.'" target="_blank"><img src="'.NGFB_URLPATH.'images/'.$img.'" 
					width="'.$img_size.'" height="'.$img_size.'" /></a> ';
			echo '</div>';
		}

		protected function get_submit_buttons( $submit_text = '', $class = 'submit-buttons' ) {
			if ( empty( $submit_text ) ) 
				$submit_text = __( 'Save All Plugin Settings', 'nextgen-facebook' );

			$show_opts_next = SucomUtil::next_key( NgfbUser::show_opts(), $this->p->cf['form']['show_options'] );
			$show_opts_text = 'View '.$this->p->cf['form']['show_options'][$show_opts_next].' by Default';
			$show_opts_url = $this->p->util->get_admin_url( '?action=change_show_options&show_opts='.$show_opts_next );

			$action_buttons = '<input type="submit" class="button-primary" value="'.$submit_text.'" />'.
				$this->form->get_button( $show_opts_text, 'button-secondary button-highlight', null, 
					wp_nonce_url( $show_opts_url, $this->get_nonce(), NGFB_NONCE ) ).'<br/>';

			if ( empty( $this->p->cf['*']['lib']['sitesubmenu'][$this->menu_id] ) )	// don't show on the network admin pages
				$action_buttons .= $this->form->get_button( __( 'Clear All Cache(s)', 'nextgen-facebook' ), 
					'button-secondary', null, wp_nonce_url( $this->p->util->get_admin_url( '?action=clear_all_cache' ),
						$this->get_nonce(), NGFB_NONCE ) );

			$action_buttons .= $this->form->get_button( __( 'Check for Update(s)', 'nextgen-facebook' ), 'button-secondary', null,
				wp_nonce_url( $this->p->util->get_admin_url( '?action=check_for_updates' ), $this->get_nonce(), NGFB_NONCE ),
				false, ( $this->p->is_avail['util']['um'] ? false : true )	// disable button if update manager is not available
			);

			if ( empty( $this->p->cf['*']['lib']['sitesubmenu'][$this->menu_id] ) )	// don't show on the network admin pages
				$action_buttons .= $this->form->get_button( __( 'Reset Metabox Layout', 'nextgen-facebook' ), 
					'button-secondary', null, wp_nonce_url( $this->p->util->get_admin_url( '?action=clear_metabox_prefs' ),
						$this->get_nonce(), NGFB_NONCE ) );

			if ( empty( $this->p->cf['*']['lib']['sitesubmenu'][$this->menu_id] ) )	// don't show on the network admin pages
				$action_buttons .= $this->form->get_button( __( 'Reset Hidden Notices', 'nextgen-facebook' ), 
					'button-secondary', null, wp_nonce_url( $this->p->util->get_admin_url( '?action=clear_hidden_notices' ),
						$this->get_nonce(), NGFB_NONCE ) );

			return '<div class="'.$class.'">'.$action_buttons.'</div>';
		}

		protected function get_nonce() {
			return ( defined( 'NONCE_KEY' ) ? NONCE_KEY : '' ).plugin_basename( __FILE__ );
		}

		private function is_setting( $menu_id ) {
			return isset( $this->p->cf['*']['lib']['setting'][$menu_id] ) ? true : false;
		}

		private function is_submenu( $menu_id ) {
			return isset( $this->p->cf['*']['lib']['submenu'][$menu_id] ) ? true : false;
		}

		private function is_sitesubmenu( $menu_id ) {
			return isset( $this->p->cf['*']['lib']['sitesubmenu'][$menu_id] ) ? true : false;
		}

		public function licenses_metabox_content( $network = false ) {
			echo '<table class="sucom-setting '.$this->p->cf['lca'].' licenses-metabox"
				style="padding-bottom:10px">'."\n";
			echo '<tr><td colspan="'.( $network ? 5 : 4 ).'">'.
				$this->p->msgs->get( 'info-plugin-tid' ).'</td></tr>'."\n";

			$num = 0;
			$total = count( $this->p->cf['plugin'] );
			foreach ( $this->p->cf['plugin'] as $lca => $info ) {
				$num++;
				$links = '';
				$img_href = '';
				$view_text = 'View Plugin Details';

				if ( ! empty( $info['slug'] ) && 
					( empty( $info['url']['latest_zip'] ) ||
						$this->p->is_avail['util']['um'] ) ) {

					$img_href = add_query_arg( array(
						'tab' => 'plugin-information',
						'plugin' => $info['slug'],
						'TB_iframe' => 'true',
						'width' => 600,
						'height' => 550
					), get_admin_url( null, 'plugin-install.php' ) );

					// check to see if plugin is installed or not
					if ( is_dir( WP_PLUGIN_DIR.'/'.$info['slug'] ) ) {
						$update_plugins = get_site_transient('update_plugins');
						if ( isset( $update_plugins->response ) ) {
							foreach ( (array) $update_plugins->response as $file => $plugin ) {
								if ( $plugin->slug === $info['slug'] ) {
									$view_text = '<font color="red">View Plugin Details + Update</font>';
									break;
								}
							}
						}
					} else $view_text = 'View Plugin Details + Install';

					$links .= ' | <a href="'.$img_href.'" class="thickbox">'.$view_text.'</a>';

				} elseif ( ! empty( $info['url']['download'] ) ) {
					$img_href = $info['url']['download'];
					$links .= ' | <a href="'.$img_href.'" target="_blank">Plugin Description Page</a>';
				}

				if ( ! empty( $info['url']['latest_zip'] ) )
					$links .= ' | <a href="'.$info['url']['latest_zip'].'">Download the Latest Version</a> (zip file)';

				if ( ! empty( $info['url']['purchase'] ) ) {
					if ( $this->p->cf['lca'] === $lca || 
						$this->p->check->aop( $this->p->cf['lca'], false, $this->p->is_avail['aop'] ) )
							$links .= ' | <a href="'.$info['url']['purchase'].
								'" target="_blank">Purchase Pro License(s)</a>';
					else $links .= ' | <em>Purchase Pro License(s)</em>';
				}

				if ( ! empty( $info['img']['icon_small'] ) )
					$img_icon = $info['img']['icon_medium'];	// resized as small icon for retina displays
				else $img_icon = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

				// logo image
				echo '<tr><td style="width:148px; padding:10px;" rowspan="3" valign="top" align="left">';
				if ( ! empty( $img_href ) ) 
					echo '<a href="'.$img_href.'"'.( strpos( $img_href, 'TB_iframe' ) ?
						' class="thickbox"' : ' target="_blank"' ).'>';
				echo '<img src="'.$img_icon.'" width="128" height="128">';
				if ( ! empty( $img_href ) ) 
					echo '</a>';
				echo '</td>';

				// plugin name
				echo '<td colspan="'.( $network ? 4 : 3 ).'" style="padding:10px 0 0 0;">
					<p><strong>'.$info['name'].'</strong></p>';

				if ( ! empty( $info['desc'] ) )
					echo '<p>'.$info['desc'].'</p>';

				if ( ! empty( $links ) )
					echo '<p>'.trim( $links, ' |' ).'</p>';

				echo '</td></tr>'."\n";

				if ( $network ) {
					if ( ! empty( $info['update_auth'] ) || 
						! empty( $this->p->options['plugin_'.$lca.'_tid'] ) ) {

						if ( $this->p->cf['lca'] === $lca || 
							$this->p->check->aop( $this->p->cf['lca'], 
								true, $this->p->is_avail['aop'] ) ) {

							echo '<tr>'.$this->p->util->get_th( __( 'Pro Authentication ID',
								'nextgen-facebook' ), 'medium nowrap' ).
							'<td class="tid">'.$this->form->get_input( 'plugin_'.$lca.'_tid', 'tid mono' ).'</td>'.
							$this->p->util->get_th( __( 'Site Use', 'nextgen-facebook' ), 'site_use' ).
							'<td>'.$this->form->get_select( 'plugin_'.$lca.'_tid:use', 
								$this->p->cf['form']['site_option_use'], 'site_use' ).'</td></tr>'."\n";
						} else {
							echo '<tr>'.$this->p->util->get_th( __( 'Pro Authentication ID',
								'nextgen-facebook' ), 'medium nowrap' ).
							'<td class="blank">'.( empty( $this->p->options['plugin_'.$lca.'_tid'] ) ?
								$this->form->get_no_input( 'plugin_'.$lca.'_tid', 'tid mono' ) :
								$this->form->get_input( 'plugin_'.$lca.'_tid', 'tid mono' ) ).
							'</td><td colspan="2">'.( $this->p->check->aop( $this->p->cf['lca'], 
								true, $this->p->is_avail['aop'] ) ?
									'' : $this->p->msgs->get( 'pro-option-msg' ) ).'</td></tr>'."\n";
						}
					} else echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>'."\n";
				} else {
					if ( ! empty( $info['update_auth'] ) || 
						! empty( $this->p->options['plugin_'.$lca.'_tid'] ) ) {

						if ( $this->p->cf['lca'] === $lca || 
							$this->p->check->aop( $this->p->cf['lca'], 
								true, $this->p->is_avail['aop'] ) ) {

							$qty_used = class_exists( 'SucomUpdate' ) ?
								SucomUpdate::get_option( $lca, 'qty_used' ) : false;

							echo '<tr>'.$this->p->util->get_th( __( 'Pro Authentication ID',
								'nextgen-facebook' ), 'medium nowrap' ).
							'<td class="tid">'.$this->form->get_input( 'plugin_'.$lca.'_tid', 'tid mono' ).
							'</td><td><p>'.( empty( $qty_used ) ? 
								'' : $qty_used.' Licenses Assigned' ).'</p></td></tr>'."\n";
						} else {
							echo '<tr>'.$this->p->util->get_th( __( 'Pro Authentication ID',
								'nextgen-facebook' ), 'medium nowrap' ).
							'<td class="blank">'.( empty( $this->p->options['plugin_'.$lca.'_tid'] ) ?
								$this->form->get_no_input( 'plugin_'.$lca.'_tid', 'tid mono' ) :
								$this->form->get_input( 'plugin_'.$lca.'_tid', 'tid mono' ) ).
							'</td><td>'.( $this->p->check->aop( $this->p->cf['lca'], 
								true, $this->p->is_avail['aop'] ) ? 
									'' : $this->p->msgs->get( 'pro-option-msg' ) ).'</td></tr>'."\n";
						}
					} else echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</tr>'."\n";
				}

				echo '<tr><td'.( $num < $total ? ' style="border-bottom:1px dotted #ddd;"' : '' ).
					' colspan="'.( $network ? 4 : 3 ).'">&nbsp;</td></tr>'."\n";
			}
			echo '</table>'."\n";
		}

		public function conflict_warnings() {

			if ( ! is_admin() ) 
				return;

			$lca = $this->p->cf['lca'];
			$base = $this->p->cf['plugin'][$lca]['base'];
			$short = $this->p->cf['plugin'][$lca]['short'];
			$short_pro = $short.' Pro';
			$purchase_url = $this->p->cf['plugin'][$lca]['url']['purchase'];
			$err_pre =  __( 'Plugin conflict detected', 'nextgen-facebook' ) . ' - ';
			$log_pre = 'plugin conflict detected - ';	// don't translate the debug 

			// PHP
			if ( empty( $this->p->is_avail['curl'] ) ) {
				if ( ! empty( $this->p->options['plugin_shortener'] ) && 
					$this->p->options['plugin_shortener'] !== 'none' ) {

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'url shortening is enabled but curl function is missing' );
					$this->p->notice->err( sprintf( __( 'URL shortening has been enabled, but PHP\'s <a href="%s" target="_blank">Client URL Library</a> (cURL) is missing.', 'nextgen-facebook' ), 'http://ca3.php.net/curl' ).' '.__( 'Please contact your hosting provider to have the missing cURL library files installed.', 'nextgen-facebook' ) );
				} elseif ( ! empty( $this->p->options['plugin_file_cache_exp'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'file caching is enabled but curl function is missing' );
					$this->p->notice->err( sprintf( __( 'The file caching feature has been enabled but PHP\'s <a href="%s" target="_blank">Client URL Library</a> (cURL) is missing.', 'nextgen-facebook' ), 'http://ca3.php.net/curl' ).' '.__( 'Please contact your hosting provider to have the missing cURL library files installed.', 'nextgen-facebook' ) );
				}
			}

			// Yoast SEO
			if ( $this->p->is_avail['seo']['wpseo'] === true ) {
				$opts = get_option( 'wpseo_social' );
				if ( ! empty( $opts['opengraph'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'wpseo opengraph meta data option is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please uncheck the \'<em>Add Open Graph meta data</em>\' Facebook option in the <a href="%s">Yoast SEO: Social</a> settings.', 'nextgen-facebook' ), get_admin_url( null, 'admin.php?page=wpseo_social#top#facebook' ) ) );
				}
				if ( ! empty( $this->p->options['tc_enable'] ) && 
					! empty( $opts['twitter'] ) &&
					$this->p->check->aop( $this->p->cf['lca'], true, $this->p->is_avail['aop'] ) ) {

					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'wpseo twitter meta data option is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please uncheck the \'<em>Add Twitter card meta data</em>\' Twitter option in the <a href="%s">Yoast SEO: Social</a> settings.', 'nextgen-facebook' ), get_admin_url( null, 'admin.php?page=wpseo_social#top#twitterbox' ) ) );
				}
				if ( ! empty( $opts['googleplus'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'wpseo googleplus meta data option is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please uncheck the \'<em>Add Google+ specific post meta data</em>\' Google+ option in the <a href="%s">Yoast SEO: Social</a> settings.', 'nextgen-facebook' ), get_admin_url( null, 'admin.php?page=wpseo_social#top#google' ) ) );
				}
				if ( ! empty( $opts['plus-publisher'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'wpseo google plus publisher option is defined' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please remove the \'<em>Google Publisher Page</em>\' value entered in the <a href="%s">Yoast SEO: Social</a> settings.', 'nextgen-facebook' ), get_admin_url( null, 'admin.php?page=wpseo_social#top#google' ) ) );
				}
			}

			// SEO Ultimate
			if ( $this->p->is_avail['seo']['seou'] === true ) {
				$opts = get_option( 'seo_ultimate' );
				if ( ! empty( $opts['modules'] ) && is_array( $opts['modules'] ) ) {
					if ( array_key_exists( 'opengraph', $opts['modules'] ) && $opts['modules']['opengraph'] !== -10 ) {
						if ( $this->p->debug->enabled )
							$this->p->debug->log( $log_pre.'seo ultimate opengraph module is enabled' );
						$this->p->notice->err( $err_pre.sprintf( __( 'Please disable the \'<em>Open Graph Integrator</em>\' module in the <a href="%s">SEO Ultimate plugin Module Manager</a>.', 'nextgen-facebook' ), get_admin_url( null, 'admin.php?page=seo' ) ) );
					}
				}
			}

			// All in One SEO Pack
			if ( $this->p->is_avail['seo']['aioseop'] === true ) {
				$opts = get_option( 'aioseop_options' );
				if ( ! empty( $opts['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_opengraph'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'aioseop social meta fetaure is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please deactivate the \'<em>Social Meta</em>\' feature in the <a href="%s">All in One SEO Pack Feature Manager</a>.', 'nextgen-facebook' ), get_admin_url( null, 'admin.php?page=all-in-one-seo-pack/aioseop_feature_manager.php' ) ) );
				}
				if ( array_key_exists( 'aiosp_google_disable_profile', $opts ) && empty( $opts['aiosp_google_disable_profile'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'aioseop google plus profile is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please check the \'<em>Disable Google Plus Profile</em>\' option in the <a href="%s">All in One SEO Pack Plugin Options</a>.', 'nextgen-facebook' ), get_admin_url( null, 'admin.php?page=all-in-one-seo-pack/aioseop_class.php' ) ) );
				}
			}

			// JetPack Photon
			if ( $this->p->is_avail['media']['photon'] === true && 
				! $this->p->check->aop( $this->p->cf['lca'], true, $this->p->is_avail['aop'] ) ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( $log_pre.'jetpack photon is enabled' );
				$this->p->notice->err( $err_pre.__( '<strong>JetPack\'s Photon module cripples the WordPress image size functions on purpose</strong>.', 'nextgen-facebook' ).' '.sprintf( __( 'Please <a href="%s">deactivate the JetPack Photon module</a> or deactivate the %s Free plugin.', 'nextgen-facebook' ), get_admin_url( null, 'admin.php?page=jetpack' ), $short ).' '.sprintf( __( 'You may also upgrade to the <a href="%1$s">%2$s version</a> which includes an <a href="%3$s">integration module for JetPack Photon</a> to re-enable image size functions specifically for %4$s images.', 'nextgen-facebook' ), $purchase_url, $short_pro, 'http://surniaulula.com/codex/plugins/nextgen-facebook/notes/modules/jetpack-photon/', $short ) );
			}

			// WooCommerce
			if ( class_exists( 'Woocommerce' ) && 
				! empty( $this->p->options['plugin_filter_content'] ) &&
				! $this->p->check->aop( $this->p->cf['lca'], true, $this->p->is_avail['aop'] ) ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( $log_pre.'woocommerce shortcode support not available in the admin interface' );
				$this->p->notice->err( $err_pre.__( '<strong>WooCommerce does not include shortcode support in the admin interface</strong> (required by WordPress for its content filters).', 'nextgen-facebook' ).' '.sprintf( __( 'Please uncheck the \'<em>Apply WordPress Content Filters</em>\' option on the <a href="%s">%s Advanced settings page</a>.', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_content' ), $this->p->cf['menu'] ).' '.sprintf( __( 'You may also upgrade to the <a href="%1$s">%2$s version</a> that includes an <a href="%3$s">integration module specifically for WooCommerce</a> (shortcodes, products, categories, tags, images, etc.).', 'nextgen-facebook' ), $purchase_url, $short_pro, 'http://surniaulula.com/codex/plugins/nextgen-facebook/notes/modules/woocommerce/' ) );
			}
		}

		// Dismiss Incorrect Yoast SEO Notification
		public function dismiss_wpseo_notice( $dismissed, $opt_name, $user_obj ) {
			$lca = $this->p->cf['lca'];
			$base = $this->p->cf['plugin'][$lca]['base'];
			if ( ! is_array( $dismissed['open_graph'] ) ||
				! in_array( $base, $dismissed['open_graph'] ) )
					$dismissed['open_graph'][] = $base;
			return $dismissed;
		}
	}
}

?>
