<?php
/**
 * Judge.me Product Service class
 *
 * @author   Judge.me
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Judge.me Product Service class
 */
class JGM_ProductService {

	const PER_PAGE = 200;

	public function __construct() {
		add_action( 'wp_ajax_jgm_sync_products', array( $this, 'jgm_sync_products' ) );
		add_action( 'wp_ajax_jgm_clear_syncs', array( $this, 'jgm_clear_syncs' ) );
		add_action( 'wp_ajax_jgm_clear_sync_each_product', array( $this, 'jgm_clear_sync_each_product' ) );
		add_action( 'wp_ajax_jgm_clean_single_product', array( $this, 'jgm_clean_single_product' ) );
		add_action( 'wp_ajax_jgm_clear_shop_token', array( $this, 'jgm_clear_shop_token' ) );
	}

	public function jgm_clean_single_product() {
		check_ajax_referer( 'jgm_export_reviews', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$id = $_REQUEST['product_id'];
		delete_post_meta( $id, '_judgeme_api_registered' );
		delete_post_meta( $id, '_judgeme_widget_preview_badge');
		delete_post_meta( $id, '_judgeme_widget_review_widget');

		wp_die( 'product cleaned.' );

	}

	public function jgm_clear_syncs() {
		check_ajax_referer( 'jgm_export_reviews', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		delete_option( 'jgm_full_products_synced' );
		wp_die( 'full product sync status cleared' );
	}

	public function jgm_clear_shop_token() {
		check_ajax_referer( 'jgm_export_reviews', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		delete_option( 'judgeme_shop_token' );
		delete_option( 'judgeme_is_installing' );
		wp_die( 'Shop token resetted' );
	}

	public function jgm_clear_sync_each_product() {
		check_ajax_referer( 'jgm_export_reviews', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		$count       = self::count_products_with_reviews();

		$per_page = $_REQUEST['per_page'] ?: self::PER_PAGE;
		$total_pages = $count / $per_page;
		$total_pages = (int) $total_pages;
		if ( $count % $per_page ) {
			$total_pages ++;
		}
		for ( $page = 1; $page <= $total_pages; $page ++ ) {
			$products = $this->jgm_get_products( $page, $per_page );
			foreach ( $products as $prod ) {
				$id = $prod['ID'];
				delete_post_meta( $id, '_judgeme_api_registered' );
			}
		}
		wp_die( 'each product sync status cleared' );
	}

	/**
	 * Check if product exists in core api and register it.
	 *
	 * @param int $id id of product.
	 *
	 * @return void
	 */

	public static function check_and_register_product( $id ) {

		$token    = get_option( 'judgeme_shop_token' );
		$domain   = constant( 'JGM_SHOP_DOMAIN' );
		$api_host = constant( 'JGM_API_HOST' );

		$registered = get_post_meta( $id, '_judgeme_api_registered', true );

		$p = get_post( $id );

		if ( ! $registered && $p->post_status === 'publish' ) {
			$url              = $api_host . '/products';
			$prod             = wc_get_product( $id );
			$image_url        = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'shop_single' );
			$small_image_url  = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail' );
			$medium_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'shop_catalog' );

			$terms      = get_the_terms( $id, 'product_tag' );
			$term_array = array();
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$term_array[] = $term->name;
				}
			}

			if ( ! $prod ) {
				return;
			}

			$variants = array();

			if ( $prod->is_type( 'variable' ) ) {
				$prod_variation = new WC_Product_Variable( $id );
				foreach ( $prod_variation->get_available_variations() as $variation ) {
					$variant = array();

					$gtin = get_post_meta( $variation['variation_id'], 'hwp_var_gtin', 1 ) ?: get_post_meta( $id, 'hwp_product_gtin', 1 ) ?: $prod->get_attribute('GTIN') ?: $prod->get_attribute('EAN') ?: $prod->get_attribute('ISBN');

					if ( ! empty( $gtin ) ) {
						$variant['barcode'] = $gtin;
					}

					if ( ! empty( $variation['sku'] ) ) {
						$variant['sku'] = $variation['sku'];
					}

					if ( ! empty( $variant) ) {
						$variants[] = $variant;
					}
				}
			} else {
				$pr_sku = $prod->get_sku();
				$variant = array();
				$gtin = get_post_meta( $id, 'hwp_product_gtin', 1 ) ?: $prod->get_attribute('GTIN') ?: $prod->get_attribute('EAN') ?: $prod->get_attribute('ISBN');


				if ( ! empty( $gtin ) ) {
					$variant['barcode'] = $gtin;
				}

				if ( ! empty( $pr_sku ) ) {
					$variant['sku'] = $pr_sku;
				}

				if ( ! empty($variant) ) {
					$variants[] = $variant;
				}
			}

			$vendor = get_bloginfo( 'name' );
			if ( empty( $vendor ) ) {
				$vendor = $domain;
			}

			$cats   = get_the_terms( $id, 'product_cat' );
			$cat = $vendor;
			if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
				$cat = $cats[0]->name;
			}

			$title = $prod->get_title();
			if ( empty( $title ) ) {
				$title = $cat;
			}

			$product = array(
				'api_token'        => $token,
				'shop_domain'      => $domain,
				'id'               => $id,
				'title'            => $title,
				'handle'           => $prod->get_slug(),
				'vendor'           => $vendor, //Should not require vendor!!
				'in_store'         => true, //Should not require in_store
				'image_url'        => $image_url[0],
				'small_image_url'  => $small_image_url[0],
				'medium_image_url' => $medium_image_url[0],
				'tags'             => implode( ', ', $term_array ),
				'path'             => str_replace(home_url(), '', get_permalink($id)),
			);

			if ( ! empty( $variants ) ) {
				$product['variants'] = $variants;
			}

			$data         = array(
				'method'   => 'POST',
				'blocking' => false,
				'headers'  => array( 'Content-Type' => 'application/json' ),
				'body'     => json_encode( $product ),
			);
			$product_slug = $prod->get_slug();
			if ( empty( $product_slug ) ) {
				exit();
			}

			$response = wp_safe_remote_post( $url, $data );
			if ( ! is_wp_error( $response ) ) {
				update_post_meta( $id, '_judgeme_api_registered', '1' );
			}

		}
	}

	/**
	 * Update the product in core API
	 *
	 * @param int $id id of products
	 * $in_store with value true from update hook and false from delete hook
	 * $from_restore from restore should not mark products ending with __trashed as oos
	 *
	 * @return void
	 */
	public static function update_product( $id, $in_store = true, $from_restore = false ) {
		$token    = get_option( 'judgeme_shop_token' );
		$domain   = constant( 'JGM_SHOP_DOMAIN' );
		$api_host = constant( 'JGM_API_HOST' );

		$registered = get_post_meta( $id, '_judgeme_api_registered', true );

		if ( ! $registered ) {
			self::check_and_register_product( $id );
		} else {
			$url  = $api_host . '/products/0';
			$prod = wc_get_product( $id );

			$image_url        = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'shop_single' );
			$small_image_url  = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail' );
			$medium_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'shop_catalog' );

			$terms      = get_the_terms( $id, 'product_tag' );
			$term_array = array();
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$term_array[] = $term->name;
				}
			}

			$variants = array();

			if ( $prod->is_type( 'variable' ) ) {
				$prod_variation = new WC_Product_Variable( $id );
				foreach ( $prod_variation->get_available_variations() as $variation ) {
					$variant = array();

					$gtin = get_post_meta( $variation['variation_id'], 'hwp_var_gtin', 1 ) ?: get_post_meta( $id, 'hwp_product_gtin', 1 ) ?: $prod->get_attribute('GTIN') ?: $prod->get_attribute('EAN') ?: $prod->get_attribute('ISBN');

					if ( ! empty( $gtin ) ) {
						$variant['barcode'] = $gtin;
					}

					if ( ! empty( $variation['sku'] ) ) {
						$variant['sku'] = $variation['sku'];
					}

					if ( ! empty( $variant) ) {
						$variants[] = $variant;
					}
				}
			} else {
				$pr_sku = $prod->get_sku();
				$variant = array();
				$gtin = get_post_meta( $id, 'hwp_product_gtin', 1 ) ?: $prod->get_attribute('GTIN') ?: $prod->get_attribute('EAN') ?: $prod->get_attribute('ISBN');


				if ( ! empty( $gtin ) ) {
					$variant['barcode'] = $gtin;
				}

				if ( ! empty( $pr_sku ) ) {
					$variant['sku'] = $pr_sku;
				}

				if ( ! empty($variant) ) {
					$variants[] = $variant;
				}
			}

			$vendor = get_bloginfo( 'name' );
			if ( empty( $vendor ) ) {
				$vendor = $domain;
			}

			$cats   = get_the_terms( $id, 'product_cat' );
			$cat = $vendor;
			if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
				$cat = $cats[0]->name;
			}

			$title = $prod->get_title();
			if ( empty( $title ) ) {
				$title = $cat;
			}

			$handle = $prod->get_slug();
			if ( strpos( $handle, '__trashed' ) !== false ) {
				if ( !$from_restore ) {
					$in_store = false;
				}
				$handle = str_replace('__trashed', '', $handle);
			}

			if( $prod->get_status() !== 'publish' ) {
				$in_store = false;
			}

			$product = array(
				'id'               => $id,
				'title'            => $title,
				'handle'           => $handle,
				'vendor'           => $vendor, //Should not require vendor!!
				'in_store'         => $in_store,
				'image_url'        => $image_url[0],
				'small_image_url'  => $small_image_url[0],
				'medium_image_url' => $medium_image_url[0],
				'tags'             => implode( ', ', $term_array ),
				'path'             => str_replace(home_url(), '', get_permalink($id)),
			);

			if ( ! empty( $variants ) ) {
				$product['variants'] = $variants;
			}

			$data     = array(
				'method'   => 'PUT',
				'blocking' => false,
				'headers'  => array( 'Content-Type' => 'application/json' ),
				'body'     => json_encode( array(
					'api_token'   => $token,
					'shop_domain' => $domain,
					'external_id' => $id,
					'product'     => $product

				) )
			);
			$response = wp_safe_remote_post( $url, $data );
		}
	}

	public function jgm_sync_products() {
		$token    = get_option( 'judgeme_shop_token' );
		$domain   = constant( 'JGM_SHOP_DOMAIN' );
		$api_host = constant( 'JGM_API_HOST' );

		$full_product_synced = get_option( 'jgm_full_products_synced' );

		$per_page = $_REQUEST['per_page'] ?: self::PER_PAGE;

		if ( ! empty( $full_product_synced ) ) {
			$result = array(
				'next_page'   => 2,
				'total_pages' => 1,
				'message'     => 'All products synced',
				'finished'    => 1,
			);

			echo wp_json_encode( $result );

			wp_die();
		}

		if ( isset( $_REQUEST['total_pages'] ) ) {
			$total_pages = (int) sanitize_text_field( $_REQUEST['total_pages'] ); // Input var okay.
		} else {
			$count       = self::count_products_with_reviews();
			$total_pages = $count / $per_page;
			$total_pages = (int) $total_pages;
			if ( $count % $per_page ) {
				$total_pages ++;
			}
		}

		$page     = (int) sanitize_text_field( $_REQUEST['page'] );
		$finished = 0;

		if ( $page <= $total_pages ) {
			$message = "Synced $page / $total_pages pages";

			$products      = $this->jgm_get_products( $page, $per_page );
			$products_json = array();

			foreach ( $products as $prod ) {

				$id = $prod['ID'];

				$image_url        = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'shop_single' );
				$small_image_url  = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail' );
				$medium_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'shop_catalog' );

				$terms      = get_the_terms( $id, 'product_tag' );
				$term_array = array();
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$term_array[] = $term->name;
					}
				}


				$pr       = wc_get_product( $id );
				$variants = array();

				if ( $pr->is_type( 'variable' ) ) {
					$prod_variation = new WC_Product_Variable( $id );
					foreach ( $prod_variation->get_available_variations() as $variation ) {
						$variant = array();

						$gtin = get_post_meta( $variation['variation_id'], 'hwp_var_gtin', 1 ) ?: get_post_meta( $id, 'hwp_product_gtin', 1 ) ?: $pr->get_attribute('GTIN') ?: $pr->get_attribute('EAN') ?: $pr->get_attribute('ISBN');

						if ( ! empty( $gtin ) ) {
							$variant['barcode'] = $gtin;
						}

						if ( ! empty( $variation['sku'] ) ) {
							$variant['sku'] = $variation['sku'];
						}

						if ( ! empty( $variant) ) {
							$variants[] = $variant;
						}
					}
				} else {
					$pr_sku = $pr->get_sku();
					$variant = array();
					$gtin = get_post_meta( $id, 'hwp_product_gtin', 1 ) ?: $pr->get_attribute('GTIN') ?: $pr->get_attribute('EAN') ?: $pr->get_attribute('ISBN');


					if ( ! empty( $gtin ) ) {
						$variant['barcode'] = $gtin;
					}

					if ( ! empty( $pr_sku ) ) {
						$variant['sku'] = $pr_sku;
					}

					if ( ! empty($variant) ) {
						$variants[] = $variant;
					}
				}

				$vendor = get_bloginfo( 'name' );
				if ( empty( $vendor ) ) {
					$vendor = $domain;
				}

				$cats   = get_the_terms( $id, 'product_cat' );
				$cat = $vendor;
				if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
					$cat = $cats[0]->name;
				}

				$title = $prod['title'];
				if ( empty($title) ) {
					$title = $cat;
				}

				$product_json = array(
					'id'               => $prod['ID'],
					'title'            => $title,
					'handle'           => $prod['slug'],
					'vendor'           => $vendor,
					'in_store'         => true,
					'image_url'        => $image_url[0],
					'small_image_url'  => $small_image_url[0],
					'medium_image_url' => $medium_image_url[0],
					'tags'             => implode( ', ', $term_array ),
					'path'             => str_replace(home_url(), '', get_permalink($id)),
				);


				if ( ! empty( $variants ) ) {
					$product_json['variants'] = $variants;
				}

				$products_json[] = $product_json;


				update_post_meta( $id, '_judgeme_api_registered', '1' );
			}

			$data = array(
				'method'   => 'POST',
				'blocking' => true,
				'headers'  => array( 'Content-Type' => 'application/json' ),
				'body'     => json_encode( array(
					'api_token'   => $token,
					'shop_domain' => $domain,
					'products'    => $products_json
				) )
			);

			$url = $api_host . '/products/bulk_create';

			$response = wp_safe_remote_post( $url, $data );
		} else {
			$message  = 'Finished';
			$finished = 1;
		}

		if ( $page == $total_pages ) {
			update_option( 'jgm_full_products_synced', 1 );
		}

		$page ++;
		$result = array(
			'next_page'   => $page,
			'total_pages' => $total_pages,
			'message'     => $message,
			'finished'    => $finished,
		);

		echo wp_json_encode( $result );
		wp_die();
	}

	private function jgm_get_products( $page, $per_page ) {
		global $wpdb;
		$offset = ( $page - 1 ) * $per_page;

		return $wpdb->get_results( $wpdb->prepare( "
		    SELECT ID,
		           post_title as title,
		           post_name as slug,
		           comment_count
		    FROM {$wpdb->posts}
		    WHERE post_type = 'product'
		    AND post_status = 'publish'
		    LIMIT %d, %d
		", $offset, $per_page ), ARRAY_A );
	}

	public static function count_products_with_reviews() {
		global $wpdb;

		return $wpdb->get_var( "
			    SELECT count(ID)
			    FROM {$wpdb->posts}
			    WHERE post_type = 'product'
			    AND post_status = 'publish'
		" );
	}

}
