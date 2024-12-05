<?php
/*
Plugin Name: WPC Custom Related Products for WooCommerce
Plugin URI: https://wpclever.net/
Description: WPC Custom Related Products allows you to choose custom related products for each product.
Version: 3.1.1
Author: WPClever
Author URI: https://wpclever.net
Text Domain: wpc-custom-related-products
Domain Path: /languages/
Requires Plugins: woocommerce
Requires at least: 4.0
Tested up to: 6.4
WC requires at least: 3.0
WC tested up to: 8.7
*/

defined( 'ABSPATH' ) || exit;

! defined( 'WOOCR_VERSION' ) && define( 'WOOCR_VERSION', '3.1.1' );
! defined( 'WOOCR_LITE' ) && define( 'WOOCR_LITE', __FILE__ );
! defined( 'WOOCR_FILE' ) && define( 'WOOCR_FILE', __FILE__ );
! defined( 'WOOCR_URI' ) && define( 'WOOCR_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WOOCR_DIR' ) && define( 'WOOCR_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'WOOCR_SUPPORT' ) && define( 'WOOCR_SUPPORT', 'https://wpclever.net/support?utm_source=support&utm_medium=woocr&utm_campaign=wporg' );
! defined( 'WOOCR_REVIEWS' ) && define( 'WOOCR_REVIEWS', 'https://wordpress.org/support/plugin/wpc-custom-related-products/reviews/?filter=5' );
! defined( 'WOOCR_CHANGELOG' ) && define( 'WOOCR_CHANGELOG', 'https://wordpress.org/plugins/wpc-custom-related-products/#developers' );
! defined( 'WOOCR_DISCUSSION' ) && define( 'WOOCR_DISCUSSION', 'https://wordpress.org/support/plugin/wpc-custom-related-products' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WOOCR_URI );

include 'includes/dashboard/wpc-dashboard.php';
include 'includes/kit/wpc-kit.php';
include 'includes/hpos.php';

if ( ! function_exists( 'woocr_init' ) ) {
	add_action( 'plugins_loaded', 'woocr_init', 11 );

	function woocr_init() {
		// load text-domain
		load_plugin_textdomain( 'wpc-custom-related-products', false, basename( __DIR__ ) . '/languages/' );

		if ( ! function_exists( 'WC' ) || ! version_compare( WC()->version, '3.0', '>=' ) ) {
			add_action( 'admin_notices', 'woocr_notice_wc' );

			return null;
		}

		if ( ! class_exists( 'WPCleverWoocr' ) && class_exists( 'WC_Product' ) ) {
			class WPCleverWoocr {
				protected static $instance = null;
				protected static $settings = [];
				public static $rules = [];

				public static function instance() {
					if ( is_null( self::$instance ) ) {
						self::$instance = new self();
					}

					return self::$instance;
				}

				function __construct() {
					self::$settings = (array) get_option( 'woocr_settings', [] );
					self::$rules    = (array) get_option( 'woocr_rules', [] );

					// Settings
					add_action( 'admin_init', [ $this, 'register_settings' ] );
					add_action( 'admin_menu', [ $this, 'admin_menu' ] );

					// Enqueue backend scripts
					add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

					// Add settings link
					add_filter( 'plugin_action_links', [ $this, 'action_links' ], 10, 2 );
					add_filter( 'plugin_row_meta', [ $this, 'row_meta' ], 10, 2 );

					// AJAX
					add_action( 'wp_ajax_woocr_get_search_results', [ $this, 'ajax_get_search_results' ] );
					add_action( 'wp_ajax_woocr_add_rule', [ $this, 'ajax_add_rule' ] );
					add_action( 'wp_ajax_woocr_search_term', [ $this, 'ajax_search_term' ] );

					// Product data tabs
					add_filter( 'woocommerce_product_data_tabs', [ $this, 'product_data_tabs' ] );
					add_action( 'woocommerce_product_data_panels', [ $this, 'product_data_panels' ] );
					add_action( 'woocommerce_process_product_meta', [ $this, 'process_product_meta' ] );

					// Related products
					add_filter( 'woocommerce_product_related_posts_shuffle', '__return_false', 99 );
					add_filter( 'woocommerce_related_products', [ $this, 'related_products' ], 99, 2 );
					add_filter( 'woocommerce_output_related_products_args', [ $this, 'related_products_args' ], 99 );

					// Search filters
					if ( self::get_setting( 'search_sku', 'no' ) === 'yes' ) {
						add_filter( 'pre_get_posts', [ $this, 'search_sku' ], 99 );
					}

					if ( self::get_setting( 'search_exact', 'no' ) === 'yes' ) {
						add_action( 'pre_get_posts', [ $this, 'search_exact' ], 99 );
					}

					if ( self::get_setting( 'search_sentence', 'no' ) === 'yes' ) {
						add_action( 'pre_get_posts', [ $this, 'search_sentence' ], 99 );
					}
				}

				public static function get_settings() {
					return apply_filters( 'woocr_get_settings', self::$settings );
				}

				public static function get_setting( $name, $default = false ) {
					if ( ! empty( self::$settings ) && isset( self::$settings[ $name ] ) ) {
						$setting = self::$settings[ $name ];
					} else {
						$setting = get_option( '_woocr_' . $name, $default );
					}

					return apply_filters( 'woocr_get_setting', $setting, $name, $default );
				}

				function register_settings() {
					// settings
					register_setting( 'woocr_settings', 'woocr_settings' );
					register_setting( 'woocr_rules', 'woocr_rules' );
				}

				function admin_menu() {
					add_submenu_page( 'wpclever', esc_html__( 'WPC Custom Related Products', 'wpc-custom-related-products' ), esc_html__( 'Related Products', 'wpc-custom-related-products' ), 'manage_options', 'wpclever-woocr', [
						$this,
						'admin_menu_content'
					] );
				}

				function admin_menu_content() {
					$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'how';
					?>
                    <div class="wpclever_settings_page wrap">
                        <h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Custom Related Products', 'wpc-custom-related-products' ) . ' ' . esc_html( WOOCR_VERSION ) . ' ' . ( defined( 'WOOCR_PREMIUM' ) ? '<span class="premium" style="display: none">' . esc_html__( 'Premium', 'wpc-custom-related-products' ) . '</span>' : '' ); ?></h1>
                        <div class="wpclever_settings_page_desc about-text">
                            <p>
								<?php printf( /* translators: stars */ esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'wpc-custom-related-products' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                                <br/>
                                <a href="<?php echo esc_url( WOOCR_REVIEWS ); ?>" target="_blank"><?php esc_html_e( 'Reviews', 'wpc-custom-related-products' ); ?></a> |
                                <a href="<?php echo esc_url( WOOCR_CHANGELOG ); ?>" target="_blank"><?php esc_html_e( 'Changelog', 'wpc-custom-related-products' ); ?></a> |
                                <a href="<?php echo esc_url( WOOCR_DISCUSSION ); ?>" target="_blank"><?php esc_html_e( 'Discussion', 'wpc-custom-related-products' ); ?></a>
                            </p>
                        </div>
						<?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { ?>
                            <div class="notice notice-success is-dismissible">
                                <p><?php esc_html_e( 'Settings updated.', 'wpc-custom-related-products' ); ?></p>
                            </div>
						<?php } ?>
                        <div class="wpclever_settings_page_nav">
                            <h2 class="nav-tab-wrapper">
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=how' ) ); ?>" class="<?php echo esc_attr( $active_tab === 'how' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>">
									<?php esc_html_e( 'How to use?', 'wpc-custom-related-products' ); ?>
                                </a>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=settings' ) ); ?>" class="<?php echo esc_attr( $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>">
									<?php esc_html_e( 'Settings', 'wpc-custom-related-products' ); ?>
                                </a>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=smart' ) ); ?>" class="<?php echo esc_attr( $active_tab === 'smart' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>">
									<?php esc_html_e( 'Smart Related', 'wpc-custom-related-products' ); ?>
                                </a>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=premium' ) ); ?>" class="<?php echo esc_attr( $active_tab === 'premium' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>" style="color: #c9356e">
									<?php esc_html_e( 'Premium Version', 'wpc-custom-related-products' ); ?>
                                </a>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-kit' ) ); ?>" class="nav-tab">
									<?php esc_html_e( 'Essential Kit', 'wpc-custom-related-products' ); ?>
                                </a>
                            </h2>
                        </div>
                        <div class="wpclever_settings_page_content">
							<?php if ( $active_tab === 'how' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>
										<?php esc_html_e( 'When creating/editing the product, please choose "Related Product" tab then you can search and add custom related products.', 'wpc-custom-related-products' ); ?>
                                    </p>
                                    <p>
                                        <img src="<?php echo esc_url( WOOCR_URI . 'assets/images/how-01.jpg' ); ?>"/>
                                    </p>
                                </div>
								<?php
							} elseif ( $active_tab === 'settings' ) {
								$default         = self::get_setting( 'default', 'related' );
								$search_limit    = self::get_setting( 'search_limit', '5' );
								$search_sku      = self::get_setting( 'search_sku', 'no' );
								$search_id       = self::get_setting( 'search_id', 'no' );
								$search_exact    = self::get_setting( 'search_exact', 'no' );
								$search_sentence = self::get_setting( 'search_sentence', 'no' );
								?>
                                <form method="post" action="options.php">
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'General', 'wpc-custom-related-products' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Default products', 'wpc-custom-related-products' ); ?></th>
                                            <td>
                                                <select name="woocr_settings[default]">
                                                    <option value="related" <?php selected( $default, 'related' ); ?>><?php esc_html_e( 'Default Related', 'wpc-custom-related-products' ); ?></option>
                                                    <option value="smart_related" <?php selected( $default, 'smart_related' ); ?>><?php esc_html_e( 'Smart Related', 'wpc-custom-related-products' ); ?></option>
                                                    <option value="up_sells" <?php selected( $default, 'up_sells' ); ?>><?php esc_html_e( 'Upsells', 'wpc-custom-related-products' ); ?></option>
                                                    <option value="cross_sells" <?php selected( $default, 'cross_sells' ); ?>><?php esc_html_e( 'Cross-sells', 'wpc-custom-related-products' ); ?></option>
                                                    <option value="up_cross_sells" <?php selected( $default, 'up_cross_sells' ); ?>><?php esc_html_e( 'Upsells & Cross-sells', 'wpc-custom-related-products' ); ?></option>
                                                    <option value="none" <?php selected( $default, 'none' ); ?>><?php esc_html_e( 'None', 'wpc-custom-related-products' ); ?></option>
                                                </select> <span class="description">If you choose "Smart Related", please configure the rules on Smart Related tab. If you choose Upsells/Cross-sells, you also can use
													<a href="https://wordpress.org/plugins/wpc-smart-linked-products/" target="_blank">WPC Smart Linked Products</a> to configure upsells/cross-sells products in bulk.
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Default products limit', 'wpc-custom-related-products' ); ?></th>
                                            <td>
                                                <input type="number" class="small-text" name="woocr_settings[default_limit]" value="<?php echo esc_attr( self::get_setting( 'default_limit', 5 ) ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Search', 'wpc-custom-related-products' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search limit', 'wpc-custom-related-products' ); ?></th>
                                            <td>
                                                <input name="woocr_settings[search_limit]" type="number" min="1" max="500" value="<?php echo esc_attr( $search_limit ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search by SKU', 'wpc-custom-related-products' ); ?></th>
                                            <td>
                                                <select name="woocr_settings[search_sku]">
                                                    <option value="yes" <?php selected( $search_sku, 'yes' ); ?>><?php esc_html_e( 'Yes', 'wpc-custom-related-products' ); ?></option>
                                                    <option value="no" <?php selected( $search_sku, 'no' ); ?>><?php esc_html_e( 'No', 'wpc-custom-related-products' ); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search by ID', 'wpc-custom-related-products' ); ?></th>
                                            <td>
                                                <select name="woocr_settings[search_id]">
                                                    <option value="yes" <?php selected( $search_id, 'yes' ); ?>><?php esc_html_e( 'Yes', 'wpc-custom-related-products' ); ?></option>
                                                    <option value="no" <?php selected( $search_id, 'no' ); ?>><?php esc_html_e( 'No', 'wpc-custom-related-products' ); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search exact', 'wpc-custom-related-products' ); ?></th>
                                            <td>
                                                <select name="woocr_settings[search_exact]">
                                                    <option value="yes" <?php selected( $search_exact, 'yes' ); ?>><?php esc_html_e( 'Yes', 'wpc-custom-related-products' ); ?></option>
                                                    <option value="no" <?php selected( $search_exact, 'no' ); ?>><?php esc_html_e( 'No', 'wpc-custom-related-products' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Match whole product title or content?', 'wpc-custom-related-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search sentence', 'wpc-custom-related-products' ); ?></th>
                                            <td>
                                                <select name="woocr_settings[search_sentence]">
                                                    <option value="yes" <?php selected( $search_sentence, 'yes' ); ?>><?php esc_html_e( 'Yes', 'wpc-custom-related-products' ); ?></option>
                                                    <option value="no" <?php selected( $search_sentence, 'no' ); ?>><?php esc_html_e( 'No', 'wpc-custom-related-products' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Do a phrase search?', 'wpc-custom-related-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2"><?php esc_html_e( 'Suggestion', 'wpc-custom-related-products' ); ?></th>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                To display custom engaging real-time messages on any wished positions, please install
                                                <a href="https://wordpress.org/plugins/wpc-smart-messages/" target="_blank">WPC Smart Messages</a> plugin. It's free!
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                Wanna save your precious time working on variations? Try our brand-new free plugin
                                                <a href="https://wordpress.org/plugins/wpc-variation-bulk-editor/" target="_blank">WPC Variation Bulk Editor</a> and
                                                <a href="https://wordpress.org/plugins/wpc-variation-duplicator/" target="_blank">WPC Variation Duplicator</a>.
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
												<?php settings_fields( 'woocr_settings' ); ?><?php submit_button(); ?>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab === 'smart' ) {
								self::rules( 'woocr_rules', self::$rules );
							} elseif ( $active_tab === 'premium' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>Get the Premium Version just $29!
                                        <a href="https://wpclever.net/downloads/custom-related-products?utm_source=pro&utm_medium=woocr&utm_campaign=wporg" target="_blank">https://wpclever.net/downloads/custom-related-products</a>
                                    </p>
                                    <p><strong>Extra features for Premium Version:</strong></p>
                                    <ul style="margin-bottom: 0">
                                        <li>- Use combined conditions for Smart Related.</li>
                                        <li>- Get the lifetime update & premium support.</li>
                                    </ul>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}

				function rules( $name = 'woocr_rules', $rules = [] ) {
					?>
                    <form method="post" action="options.php">
                        <table class="form-table">
                            <tr>
                                <td>
									<?php esc_html_e( 'Our plugin checks rules from the top down the list. When there are products that satisfy more than 1 rule, the first rule on top will be prioritized. Please make sure you put the rules in the order of the most to the least prioritized.', 'wpc-custom-related-products' ); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="woocr_rules">
										<?php
										if ( is_array( $rules ) && ( count( $rules ) > 0 ) ) {
											foreach ( $rules as $key => $rule ) {
												self::rule( $key, $name, $rule, false );
											}
										} else {
											self::rule( '', $name, null, true );
										}
										?>
                                    </div>
                                    <div class="woocr_add_rule">
                                        <div>
                                            <a href="#" class="woocr_new_rule button" data-name="<?php echo esc_attr( $name ); ?>">
												<?php esc_html_e( '+ Add rule', 'wpc-custom-related-products' ); ?>
                                            </a> <a href="#" class="woocr_expand_all">
												<?php esc_html_e( 'Expand All', 'wpc-custom-related-products' ); ?>
                                            </a> <a href="#" class="woocr_collapse_all">
												<?php esc_html_e( 'Collapse All', 'wpc-custom-related-products' ); ?>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="submit">
                                <th colspan="2">
									<?php settings_fields( $name ); ?><?php submit_button(); ?>
                                </th>
                            </tr>
                        </table>
                    </form>
					<?php
				}

				function rule( $key = '', $name = 'woocr_rules', $rule = null, $active = false ) {
					if ( empty( $key ) || is_numeric( $key ) ) {
						$key = self::generate_key();
					}

					$apply             = isset( $rule['apply'] ) ? $rule['apply'] : 'all';
					$apply_products    = isset( $rule['apply_products'] ) ? (array) $rule['apply_products'] : [];
					$apply_terms       = isset( $rule['apply_terms'] ) ? (array) $rule['apply_terms'] : [];
					$apply_combination = isset( $rule['apply_combination'] ) ? (array) $rule['apply_combination'] : [];
					$get               = isset( $rule['get'] ) ? $rule['get'] : 'all';
					$get_products      = isset( $rule['get_products'] ) ? (array) $rule['get_products'] : [];
					$get_terms         = isset( $rule['get_terms'] ) ? (array) $rule['get_terms'] : [];
					$get_combination   = isset( $rule['get_combination'] ) ? (array) $rule['get_combination'] : [];
					$get_limit         = isset( $rule['get_limit'] ) ? absint( $rule['get_limit'] ) : 3;
					$get_orderby       = isset( $rule['get_orderby'] ) ? $rule['get_orderby'] : 'default';
					$get_order         = isset( $rule['get_order'] ) ? $rule['get_order'] : 'default';
					?>
                    <div class="<?php echo esc_attr( $active ? 'woocr_rule active' : 'woocr_rule' ); ?>" data-key="<?php echo esc_attr( $key ); ?>">
                        <div class="woocr_rule_heading">
                            <span class="woocr_rule_move"></span>
                            <span class="woocr_rule_label"><?php echo esc_html( $apply . ' / ' . $get ); ?></span>
                            <a href="#" class="woocr_rule_duplicate" data-name="<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'duplicate', 'wpc-custom-related-products' ); ?></a>
                            <a href="#" class="woocr_rule_remove"><?php esc_html_e( 'remove', 'wpc-custom-related-products' ); ?></a>
                        </div>
                        <div class="woocr_rule_content">
                            <div class="woocr_tr">
                                <div class="woocr_th woocr_th_full">
									<?php esc_html_e( 'Add linked products to which?', 'wpc-custom-related-products' ); ?>
                                </div>
                            </div>
							<?php self::source( $name, $key, $apply, $apply_products, $apply_terms, $apply_combination, 'apply' ); ?>
                            <div class="woocr_tr">
                                <div class="woocr_th woocr_th_full">
									<?php esc_html_e( 'Define applicable linked products:', 'wpc-custom-related-products' ); ?>
                                </div>
                            </div>
							<?php self::source( $name, $key, $get, $get_products, $get_terms, $get_combination, 'get', $get_limit, $get_orderby, $get_order ); ?>
                        </div>
                    </div>
					<?php
				}

				function source( $name, $key, $apply, $products, $terms, $combination, $type = 'apply', $get_limit = null, $get_orderby = null, $get_order = null ) {
					?>
                    <div class="woocr_tr">
                        <div class="woocr_th"><?php esc_html_e( 'Source', 'wpc-custom-related-products' ); ?></div>
                        <div class="woocr_td woocr_rule_td">
                            <select class="woocr_source_selector woocr_source_selector_<?php echo esc_attr( $type ); ?>" data-type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $type ); ?>]">
                                <option value="all"><?php esc_html_e( 'All products', 'wpc-custom-related-products' ); ?></option>
                                <option value="products" <?php selected( $apply, 'products' ); ?>><?php esc_html_e( 'Products', 'wpc-custom-related-products' ); ?></option>
                                <option value="combination" <?php selected( $apply, 'combination' ); ?> disabled><?php esc_html_e( 'Combined (Premium)', 'wpc-custom-related-products' ); ?></option>
								<?php
								$taxonomies = get_object_taxonomies( 'product', 'objects' );

								foreach ( $taxonomies as $taxonomy ) {
									echo '<option value="' . esc_attr( $taxonomy->name ) . '" ' . ( $apply === $taxonomy->name ? 'selected' : '' ) . '>' . esc_html( $taxonomy->label ) . '</option>';
								}
								?>
                            </select>
							<?php if ( $type === 'get' ) { ?>
                                <span class="show_get hide_if_get_products">
										<span><?php esc_html_e( 'Limit', 'wpc-custom-related-products' ); ?> <input type="number" min="1" max="50" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $key ); ?>][get_limit]" value="<?php echo esc_attr( $get_limit ); ?>"/></span>
										<span>
										<?php esc_html_e( 'Order by', 'wpc-custom-related-products' ); ?> <select name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $key ); ?>][get_orderby]">
                                                        <option value="default" <?php selected( $get_orderby, 'default' ); ?>><?php esc_html_e( 'Default', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="none" <?php selected( $get_orderby, 'none' ); ?>><?php esc_html_e( 'None', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="ID" <?php selected( $get_orderby, 'ID' ); ?>><?php esc_html_e( 'ID', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="name" <?php selected( $get_orderby, 'name' ); ?>><?php esc_html_e( 'Name', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="type" <?php selected( $get_orderby, 'type' ); ?>><?php esc_html_e( 'Type', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="rand" <?php selected( $get_orderby, 'rand' ); ?>><?php esc_html_e( 'Rand', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="date" <?php selected( $get_orderby, 'date' ); ?>><?php esc_html_e( 'Date', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="price" <?php selected( $get_orderby, 'price' ); ?>><?php esc_html_e( 'Price', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="modified" <?php selected( $get_orderby, 'modified' ); ?>><?php esc_html_e( 'Modified', 'wpc-custom-related-products' ); ?></option>
                                                    </select>
									</span>
										<span><?php esc_html_e( 'Order', 'wpc-custom-related-products' ); ?> <select name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $key ); ?>][get_order]">
                                                        <option value="default" <?php selected( $get_order, 'default' ); ?>><?php esc_html_e( 'Default', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="DESC" <?php selected( $get_order, 'DESC' ); ?>><?php esc_html_e( 'DESC', 'wpc-custom-related-products' ); ?></option>
                                                        <option value="ASC" <?php selected( $get_order, 'ASC' ); ?>><?php esc_html_e( 'ASC', 'wpc-custom-related-products' ); ?></option>
                                                        </select></span>
									</span>
							<?php } ?>
                        </div>
                    </div>
                    <div class="woocr_tr hide_<?php echo esc_attr( $type ); ?> show_if_<?php echo esc_attr( $type ); ?>_products">
                        <div class="woocr_th"><?php esc_html_e( 'Products', 'wpc-custom-related-products' ); ?></div>
                        <div class="woocr_td woocr_rule_td">
                            <select class="wc-product-search woocr-product-search" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $type . '_products' ); ?>][]" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'wpc-custom-related-products' ); ?>" data-action="woocommerce_json_search_products_and_variations">
								<?php
								if ( ! empty( $products ) ) {
									foreach ( $products as $_product_id ) {
										if ( $_product = wc_get_product( $_product_id ) ) {
											echo '<option value="' . esc_attr( $_product_id ) . '" selected>' . wp_kses_post( $_product->get_formatted_name() ) . '</option>';
										}
									}
								}
								?>
                            </select>
                        </div>
                    </div>
                    <div class="woocr_tr show_<?php echo esc_attr( $type ); ?> hide_if_<?php echo esc_attr( $type ); ?>_all hide_if_<?php echo esc_attr( $type ); ?>_products hide_if_<?php echo esc_attr( $type ); ?>_combination">
                        <div class="woocr_th woocr_<?php echo esc_attr( $type ); ?>_text"><?php esc_html_e( 'Terms', 'wpc-custom-related-products' ); ?></div>
                        <div class="woocr_td woocr_rule_td">
                            <select class="woocr_terms" data-type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $type . '_terms' ); ?>][]" multiple="multiple" data-<?php echo esc_attr( $apply ); ?>="<?php echo esc_attr( implode( ',', $terms ) ); ?>">
								<?php
								if ( ! empty( $terms ) ) {
									foreach ( $terms as $at ) {
										if ( $term = get_term_by( 'slug', $at, $apply ) ) {
											echo '<option value="' . esc_attr( $at ) . '" selected>' . esc_html( $term->name ) . '</option>';
										}
									}
								}
								?>
                            </select>
                        </div>
                    </div>
					<?php
				}

				function ajax_add_rule() {
					if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'woocr_nonce' ) ) {
						die( 'Permissions check failed!' );
					}

					$rule      = [];
					$name      = isset( $_POST['name'] ) ? sanitize_key( $_POST['name'] ) : 'woocr_rules';
					$rule_data = isset( $_POST['rule_data'] ) ? $_POST['rule_data'] : '';

					if ( ! empty( $rule_data ) ) {
						$form_rule = [];
						parse_str( $rule_data, $form_rule );

						if ( isset( $form_rule[ $name ] ) && is_array( $form_rule[ $name ] ) ) {
							$rule = reset( $form_rule[ $name ] );
						}
					}

					self::rule( '', $name, $rule, true );
					wp_die();
				}

				function ajax_search_term() {
					$return = [];

					$args = [
						'taxonomy'   => sanitize_text_field( $_REQUEST['taxonomy'] ),
						'orderby'    => 'id',
						'order'      => 'ASC',
						'hide_empty' => false,
						'fields'     => 'all',
						'name__like' => sanitize_text_field( $_REQUEST['q'] ),
					];

					$terms = get_terms( $args );

					if ( count( $terms ) ) {
						foreach ( $terms as $term ) {
							$return[] = [ $term->slug, $term->name ];
						}
					}

					wp_send_json( $return );
				}

				function admin_enqueue_scripts( $hook ) {
					if ( apply_filters( 'woocr_ignore_backend_scripts', false, $hook ) ) {
						return null;
					}

					wp_enqueue_style( 'hint', WOOCR_URI . 'assets/css/hint.css' );
					wp_enqueue_style( 'woocr-backend', WOOCR_URI . 'assets/css/backend.css', [ 'woocommerce_admin_styles' ], WOOCR_VERSION );
					wp_enqueue_script( 'woocr-backend', WOOCR_URI . 'assets/js/backend.js', [
						'jquery',
						'jquery-ui-sortable',
						'wc-enhanced-select',
						'selectWoo',
					], WOOCR_VERSION, true );
					wp_localize_script( 'woocr-backend', 'woocr_vars', [
						'woocr_nonce' => wp_create_nonce( 'woocr_nonce' )
					] );
				}

				function action_links( $links, $file ) {
					static $plugin;

					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}

					if ( $plugin === $file ) {
						$how                  = '<a href="' . esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=how' ) ) . '">' . esc_html__( 'How to use?', 'wpc-custom-related-products' ) . '</a>';
						$settings             = '<a href="' . esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=settings' ) ) . '">' . esc_html__( 'Settings', 'wpc-custom-related-products' ) . '</a>';
						$smart                = '<a href="' . esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=smart' ) ) . '">' . esc_html__( 'Smart Related', 'wpc-custom-related-products' ) . '</a>';
						$links['wpc-premium'] = '<a href="' . esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=premium' ) ) . '">' . esc_html__( 'Premium Version', 'wpc-custom-related-products' ) . '</a>';
						array_unshift( $links, $how, $settings, $smart );
					}

					return (array) $links;
				}

				function row_meta( $links, $file ) {
					static $plugin;

					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}

					if ( $plugin === $file ) {
						$row_meta = [
							'support' => '<a href="' . esc_url( WOOCR_DISCUSSION ) . '" target="_blank">' . esc_html__( 'Community support', 'wpc-custom-related-products' ) . '</a>',
						];

						return array_merge( $links, $row_meta );
					}

					return (array) $links;
				}

				function ajax_get_search_results() {
					$keyword = sanitize_text_field( $_POST['keyword'] );
					$ids     = ! empty( $_POST['ids'] ) ? (array) $_POST['ids'] : [];

					if ( ( self::get_setting( 'search_id', 'no' ) === 'yes' ) && is_numeric( $keyword ) ) {
						// search by id
						$query_args = [
							'p'         => absint( $keyword ),
							'post_type' => 'product'
						];
					} else {
						$query_args = [
							'is_woocr'       => true,
							'post_type'      => 'product',
							'post_status'    => [ 'publish', 'private' ],
							's'              => $keyword,
							'posts_per_page' => self::get_setting( 'search_limit', '5' ),
							'post__not_in'   => $ids
						];
					}

					$query = new WP_Query( $query_args );

					if ( $query->have_posts() ) {
						echo '<ul>';

						while ( $query->have_posts() ) {
							$query->the_post();
							$product = wc_get_product( get_the_ID() );

							if ( ! $product ) {
								continue;
							}

							self::product_data_li( $product, true );
						}

						echo '</ul>';

						wp_reset_postdata();
					} else {
						echo '<ul><span>' . sprintf( /* translators: keyword */ esc_html__( 'No results found for "%s"', 'wpc-custom-related-products' ), $keyword ) . '</span></ul>';
					}

					wp_die();
				}

				function product_data_li( $product, $search = false ) {
					$product_id = $product->get_id();

					if ( $search ) {
						$remove_btn = '<span class="woocr-remove hint--left" aria-label="' . esc_html__( 'Add', 'wpc-custom-related-products' ) . '">+</span>';
					} else {
						$remove_btn = '<span class="woocr-remove hint--left" aria-label="' . esc_html__( 'Remove', 'wpc-custom-related-products' ) . '">×</span>';
					}

					if ( $product->is_type( 'variation' ) ) {
						$edit_link = get_edit_post_link( $product->get_parent_id() );
					} else {
						$edit_link = get_edit_post_link( $product_id );
					}

					echo '<li ' . ( ! $product->is_in_stock() ? 'class="out-of-stock"' : '' ) . '><span class="move"></span><span class="name"><input type="hidden" ' . ( ! $search ? 'name="woocr_ids[]"' : '' ) . ' value="' . esc_attr( $product_id ) . '"/>' . $product->get_name() . ' <span class="price">' . $product->get_price_html() . '</span></span><span class="type"><a target="_blank" href="' . esc_url( $edit_link ) . '">' . $product->get_type() . '<br/>#' . $product->get_id() . '</a></span>' . $remove_btn . '</li>';
				}

				function product_data_tabs( $tabs ) {
					$tabs['woocr'] = [
						'label'  => esc_html__( 'Related Products', 'wpc-custom-related-products' ),
						'target' => 'woocr_settings'
					];

					return $tabs;
				}

				function product_data_panels() {
					global $post, $thepostid, $product_object;

					if ( $product_object instanceof WC_Product ) {
						$product_id = $product_object->get_id();
					} elseif ( is_numeric( $thepostid ) ) {
						$product_id = $thepostid;
					} elseif ( $post instanceof WP_Post ) {
						$product_id = $post->ID;
					} else {
						$product_id = 0;
					}

					if ( ! $product_id ) {
						?>
                        <div id='woocr_settings' class='panel woocommerce_options_panel woocr_table'>
                            <p style="padding: 0 12px; color: #c9356e"><?php esc_html_e( 'Product wasn\'t returned.', 'wpc-custom-related-products' ); ?></p>
                        </div>
						<?php
						return;
					}

					$orderby = get_post_meta( $product_id, 'woocr_orderby', true ) ?: 'none';
					$order   = get_post_meta( $product_id, 'woocr_order', true ) ?: 'asc';
					?>
                    <div id='woocr_settings' class='panel woocommerce_options_panel woocr_table'>
                        <table>
                            <tr>
                                <th><?php esc_html_e( 'Search', 'wpc-custom-related-products' ); ?> (<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=settings#search' ) ); ?>" target="_blank"><?php esc_html_e( 'settings', 'wpc-custom-related-products' ); ?></a>)
                                </th>
                                <td>
                                    <div class="w100">
                                        <span class="loading" id="woocr_loading"><?php esc_html_e( 'searching...', 'wpc-custom-related-products' ); ?></span>
                                        <input type="search" id="woocr_keyword" placeholder="<?php esc_attr_e( 'Type any keyword to search', 'wpc-custom-related-products' ); ?>"/>
                                        <div id="woocr_results" class="woocr_results"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woocr_tr_space">
                                <th><?php esc_html_e( 'Selected', 'wpc-custom-related-products' ); ?></th>
                                <td>
                                    <div class="w100">
                                        <div id="woocr_selected" class="woocr_selected">
                                            <ul>
												<?php
												echo '<li class="woocr_default">' . sprintf( /* translators: default */ esc_html__( '* If don\'t choose any products, it can shows the default products %s.', 'wpc-custom-related-products' ), '<a
                                                    href="' . esc_url( admin_url( 'admin.php?page=wpclever-woocr&tab=settings' ) ) . '" target="_blank">' . esc_html__( 'here', 'wpc-custom-related-products' ) . '</a>' ) . '</li>';

												if ( ( $items = get_post_meta( $product_id, 'woocr_ids', true ) ) && is_array( $items ) && count( $items ) > 0 ) {
													foreach ( array_unique( $items ) as $item ) {
														$product = wc_get_product( $item );

														if ( ! $product ) {
															continue;
														}

														self::product_data_li( $product );
													}
												}
												?>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woocr_tr_space">
                                <th><?php esc_html_e( 'Order by', 'wpc-custom-related-products' ); ?></th>
                                <td>
                                    <select name="woocr_orderby">
                                        <option value="none" <?php selected( $orderby, 'none' ); ?>><?php esc_html_e( 'Selected', 'wpc-custom-related-products' ); ?></option>
                                        <option value="title" <?php selected( $orderby, 'title' ); ?>><?php esc_html_e( 'Title', 'wpc-custom-related-products' ); ?></option>
                                        <option value="id" <?php selected( $orderby, 'id' ); ?>><?php esc_html_e( 'ID', 'wpc-custom-related-products' ); ?></option>
                                        <option value="date" <?php selected( $orderby, 'date' ); ?>><?php esc_html_e( 'Date', 'wpc-custom-related-products' ); ?></option>
                                        <option value="modified" <?php selected( $orderby, 'modified' ); ?>><?php esc_html_e( 'Modified', 'wpc-custom-related-products' ); ?></option>
                                        <option value="price" <?php selected( $orderby, 'price' ); ?>><?php esc_html_e( 'Price', 'wpc-custom-related-products' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="woocr_tr_space">
                                <th><?php esc_html_e( 'Order', 'wpc-custom-related-products' ); ?></th>
                                <td>
                                    <select name="woocr_order">
                                        <option value="asc" <?php selected( $order, 'asc' ); ?>><?php esc_html_e( 'ASC', 'wpc-custom-related-products' ); ?></option>
                                        <option value="desc" <?php selected( $order, 'desc' ); ?>><?php esc_html_e( 'DESC', 'wpc-custom-related-products' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
					<?php
				}

				function process_product_meta( $post_id ) {
					if ( isset( $_POST['woocr_ids'] ) ) {
						update_post_meta( $post_id, 'woocr_ids', array_map( 'sanitize_key', $_POST['woocr_ids'] ) );
					}

					if ( isset( $_POST['woocr_orderby'] ) ) {
						update_post_meta( $post_id, 'woocr_orderby', sanitize_key( $_POST['woocr_orderby'] ) );
					}

					if ( isset( $_POST['woocr_order'] ) ) {
						update_post_meta( $post_id, 'woocr_order', sanitize_key( $_POST['woocr_order'] ) );
					}
				}

				function related_products_args( $args ) {
					global $product;

					if ( $product && is_a( $product, 'WC_Product' ) && ( $product_id = $product->get_id() ) ) {
						$args['orderby'] = get_post_meta( $product_id, 'woocr_orderby', true ) ?: 'none';
						$args['order']   = get_post_meta( $product_id, 'woocr_order', true ) ?: 'asc';
					}

					return $args;
				}

				function related_products( $related, $product_id ) {
					if ( ( $_ids = get_post_meta( $product_id, 'woocr_ids', true ) ) && is_array( $_ids ) && ! empty( $_ids ) ) {
						$ids = array_unique( $_ids );
					} else {
						$_ids          = $related;
						$default       = self::get_setting( 'default', 'related' );
						$default_limit = absint( self::get_setting( 'default_limit', 5 ) );

						switch ( $default ) {
							case 'none':
								$_ids = [];
								break;
							case 'up_sells':
								$_product = wc_get_product( $product_id );
								$_ids     = $_product->get_upsell_ids();
								break;
							case 'cross_sells':
								$_product = wc_get_product( $product_id );
								$_ids     = $_product->get_cross_sell_ids();
								break;
							case 'up_cross_sells':
								$_product   = wc_get_product( $product_id );
								$upsell     = $_product->get_upsell_ids();
								$cross_sell = $_product->get_cross_sell_ids();
								$_ids       = array_unique( array_merge( $upsell, $cross_sell ) );
								break;
							case 'smart_related':
								if ( ! empty( self::$rules ) ) {
									foreach ( self::$rules as $rule ) {
										if ( self::check_apply( $product_id, $rule ) ) {
											$_ids = self::get_products( $rule, $product_id );
											break;
										}
									}
								}
						}

						if ( $default_limit && ! empty( $_ids ) ) {
							$ids = array_slice( $_ids, 0, $default_limit );
						} else {
							$ids = $_ids;
						}
					}

					return apply_filters( 'woocr_related_products', $ids, $product_id );
				}

				public static function check_apply( $product, $rule ) {
					if ( is_a( $product, 'WC_Product' ) ) {
						$product_id = $product->get_id();
					} elseif ( is_int( $product ) ) {
						$product_id = $product;
					} else {
						$product_id = 0;
					}

					if ( empty( $rule['apply'] ) ) {
						return false;
					}

					switch ( $rule['apply'] ) {
						case 'all':
							return true;
						case 'products':
							if ( ! empty( $rule['apply_products'] ) && is_array( $rule['apply_products'] ) ) {
								if ( in_array( $product_id, $rule['apply_products'] ) ) {
									return true;
								}
							}

							return false;
						case 'combination':
							if ( ! empty( $rule['apply_combination'] ) && is_array( $rule['apply_combination'] ) ) {
								$match_all = true;

								foreach ( $rule['apply_combination'] as $combination ) {
									$match = true;

									if ( ! empty( $combination['apply'] ) && ! empty( $combination['compare'] ) && ! empty( $combination['terms'] ) && is_array( $combination['terms'] ) ) {
										if ( ( $combination['compare'] === 'is' ) && ! has_term( $combination['terms'], $combination['apply'], $product_id ) ) {
											$match = false;
										}

										if ( ( $combination['compare'] === 'is_not' ) && has_term( $combination['terms'], $combination['apply'], $product_id ) ) {
											$match = false;
										}
									}

									$match_all &= $match;
								}

								return $match_all;
							}

							return false;
						default:
							if ( ! empty( $rule['apply_terms'] ) && is_array( $rule['apply_terms'] ) ) {
								if ( has_term( $rule['apply_terms'], $rule['apply'], $product_id ) ) {
									return true;
								}
							}

							return false;
					}
				}

				public static function generate_key() {
					$key         = '';
					$key_str     = apply_filters( 'woocr_key_characters', 'abcdefghijklmnopqrstuvwxyz0123456789' );
					$key_str_len = strlen( $key_str );

					for ( $i = 0; $i < apply_filters( 'woocr_key_length', 4 ); $i ++ ) {
						$key .= $key_str[ random_int( 0, $key_str_len - 1 ) ];
					}

					if ( is_numeric( $key ) ) {
						$key = self::generate_key();
					}

					return apply_filters( 'woocr_generate_key', $key );
				}

				function get_products( $rule, $exclude = null ) {
					if ( ! empty( $rule['get'] ) ) {
						if ( is_a( $exclude, 'WC_Product' ) ) {
							$exclude_id = $exclude->get_id();
						} elseif ( is_int( $exclude ) ) {
							$exclude_id = $exclude;
						} else {
							$exclude_id = 0;
						}

						$limit   = isset( $rule['get_limit'] ) ? absint( $rule['get_limit'] ) : 3;
						$orderby = isset( $rule['get_orderby'] ) ? $rule['get_orderby'] : 'default';
						$order   = isset( $rule['get_order'] ) ? $rule['get_order'] : 'default';

						switch ( $rule['get'] ) {
							case 'all':
								return wc_get_products( [
									'status'  => 'publish',
									'limit'   => $limit,
									'orderby' => $orderby,
									'order'   => $order,
									'exclude' => [ $exclude_id ],
									'return'  => 'ids',
								] );
							case 'products':
								if ( ! empty( $rule['get_products'] ) && is_array( $rule['get_products'] ) ) {
									return array_diff( $rule['get_products'], [ $exclude_id ] );
								} else {
									return [];
								}
							case 'combination':
								if ( ! empty( $rule['get_combination'] ) && is_array( $rule['get_combination'] ) ) {
									$tax_query = [];

									foreach ( $rule['get_combination'] as $combination ) {
										if ( ! empty( $combination['apply'] ) && ! empty( $combination['compare'] ) && ! empty( $combination['terms'] ) && is_array( $combination['terms'] ) ) {
											$tax_query[] = [
												'taxonomy' => $combination['apply'],
												'field'    => 'slug',
												'terms'    => $combination['terms'],
												'operator' => $combination['compare'] === 'is' ? 'IN' : 'NOT IN'
											];
										}
									}

									$args = [
										'post_type'      => 'product',
										'post_status'    => 'publish',
										'posts_per_page' => $limit,
										'orderby'        => $orderby,
										'order'          => $order,
										'tax_query'      => $tax_query,
										'post__not_in'   => [ $exclude_id ],
										'fields'         => 'ids'
									];

									$ids = new WP_Query( $args );

									return $ids->posts;
								} else {
									return [];
								}
							default:
								if ( ! empty( $rule['get_terms'] ) && is_array( $rule['get_terms'] ) ) {
									$args = [
										'post_type'      => 'product',
										'post_status'    => 'publish',
										'posts_per_page' => $limit,
										'orderby'        => $orderby,
										'order'          => $order,
										'tax_query'      => [
											[
												'taxonomy' => $rule['get'],
												'field'    => 'slug',
												'terms'    => $rule['get_terms'],
											],
										],
										'post__not_in'   => [ $exclude_id ],
										'fields'         => 'ids'
									];

									$ids = new WP_Query( $args );

									return $ids->posts;
								} else {
									return [];
								}
						}
					}

					return [];
				}

				function search_sku( $query ) {
					if ( $query->is_search && isset( $query->query['is_woocr'] ) ) {
						global $wpdb;

						$sku = $query->query['s'];
						$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value = %s;", $sku ) );

						if ( ! $ids ) {
							return;
						}

						$posts = [];

						foreach ( $ids as $id ) {
							$post = get_post( $id );

							if ( $post->post_type === 'product_variation' ) {
								$posts[] = $post->post_parent;
							} else {
								$posts[] = $post->ID;
							}
						}

						unset( $query->query['s'], $query->query_vars['s'] );
						$query->set( 'post__in', $posts );
					}
				}

				function search_exact( $query ) {
					if ( $query->is_search && isset( $query->query['is_woocr'] ) ) {
						$query->set( 'exact', true );
					}
				}

				function search_sentence( $query ) {
					if ( $query->is_search && isset( $query->query['is_woocr'] ) ) {
						$query->set( 'sentence', true );
					}
				}
			}

			return WPCleverWoocr::instance();
		}

		return null;
	}
}

if ( ! function_exists( 'woocr_notice_wc' ) ) {
	function woocr_notice_wc() {
		?>
        <div class="error">
            <p><strong>WPC Custom Related Products</strong> requires WooCommerce version 3.0 or greater.</p>
        </div>
		<?php
	}
}
