<?php

namespace CatFolders\Rest\Controllers;

use CatFolders\Classes\Helpers;

class PublicAPIController {

	const CATF_ROUTE_PUBLIC_NAMESPACE = CATF_ROUTE_NAMESPACE . '/public';

	private $folderController;

	public function __construct() {
		$this->folderController = new FolderController();
	}

	public function register_routes() {
		register_rest_route(
			CATF_ROUTE_NAMESPACE,
			'/generate-api-key',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'generate_api_key' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::CATF_ROUTE_PUBLIC_NAMESPACE,
			'/folders',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this->folderController, 'get_folders' ),
					'permission_callback' => array( $this, 'public_permission_callback' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this->folderController, 'new_folder' ),
					'permission_callback' => array( $this, 'public_permission_callback' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this->folderController, 'delete_folder' ),
					'permission_callback' => array( $this, 'public_permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::CATF_ROUTE_PUBLIC_NAMESPACE,
			'/attachment-to-folder',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this->folderController, 'set_attachment_to_folder' ),
					'permission_callback' => array( $this, 'public_permission_callback' ),
				),
			)
		);
	}

	public function generate_api_key() {
		$key = Helpers::generateRandomString( 40 );
		update_option( 'catf_rest_api_key', $key );
		return new \WP_REST_Response( $key );
	}

	public function permission_callback() {
		 return current_user_can( 'upload_files' ) && current_user_can( 'manage_options' );
	}

	public function public_permission_callback() {
		$key = get_option( 'catf_rest_api_key', '' );
		if ( \strlen( $key ) == 40 ) {
			return $key === $this->getBearerToken();
		}
		return false;
	}

	private function getAuthorizationHeader() {
		$headers = null;
		if ( isset( $_SERVER['Authorization'] ) ) {
			$headers = trim( $_SERVER['Authorization'] );
		} elseif ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) { //Nginx or fast CGI
			$headers = trim( $_SERVER['HTTP_AUTHORIZATION'] );
		} elseif ( function_exists( 'apache_request_headers' ) ) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine( array_map( 'ucwords', array_keys( $requestHeaders ) ), array_values( $requestHeaders ) );
			//print_r($requestHeaders);
			if ( isset( $requestHeaders['Authorization'] ) ) {
				$headers = trim( $requestHeaders['Authorization'] );
			}
		}
		return $headers;
	}

	private function getBearerToken() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$token   = null;
		$headers = $this->getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if ( ! empty( $headers ) ) {
			if ( preg_match( '/Bearer\s(\S+)/', $headers, $matches ) ) {
				$token = $matches[1];
			}
		}
		if ( is_null( $token ) && isset( $_REQUEST['token'] ) ) {
			$token = $_REQUEST['token'];
		}
		return $token;
	}
}
