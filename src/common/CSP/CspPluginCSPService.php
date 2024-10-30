<?php

use model\CspPluginKeyMetaEntity;

class CspPluginCSPService {
	protected $cspBaseUrl;

	protected $currentEnv = "prod";

	public function __construct() {
		$this->cspBaseUrl = 'https://media.contentside.io';
		if (
			// The environment variable is set
			( ( $currentEnv = getenv( "CSP_PLUGIN_ENVIRONMENT" ) ) !== false )
			// The environment variable is not set to prod (if we want to force it locally)
			&& $currentEnv !== 'prod'
		) {
			$this->currentEnv = $currentEnv;
			$this->cspBaseUrl = 'https://media.staging.gateway.contentside.io';
		}
	}

	/**
	 * @param array $options
	 * @param string $attributeName
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	protected function get_attribute_from_options_or_default( $options, $attributeName, $default ) {
		if ( isset( $options[ $attributeName ] ) && $options[ $attributeName ] !== '' ) {
			return $options[ $attributeName ];
		}

		return $default;
	}

	/**
	 * Do a request to the CSP
	 *
	 * @return   mixed The HTTP response body
	 * @throws Exception
	 * @var      array $data The data for the request.
	 * @var      string $endpoint The endpoint on which to do the request.
	 * @var      string $method The HTTP method to use.
	 * @var      string $apiKey The API key to use.
	 * @since    1.0.0
	 * @access   private
	 */
	protected function do_request( $endpoint, $method, $data, $apiKey = null ) {
		$args = [
			'timeout'     => '180',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking'    => true,
			'cookies'     => [],
			'headers'     => [
				"Content-Type" => "application/json",
				"x-api-key"    => $apiKey !== null ? $apiKey : $this->get_api_key(),
			],
		];
		switch ( $method ) {
			case "GET":
				$endpoint .= '?' . http_build_query( $data );
				$response = wp_remote_get( $endpoint, $args );
				break;
			case "POST":
				$args['body'] = json_encode( $data );

				$response = wp_remote_post( $endpoint, $args );
				break;
			case "PUT":
				$args['body']   = json_encode( $data );
				$args['method'] = 'PUT';

				$response = wp_remote_request( $endpoint, $args );
				break;
			default:
				throw new Exception( "Unsupported HTTP method $method" );
		}

		if ( $response instanceof WP_Error ) {
			$errors = json_encode( $response->errors );
			throw new Exception( "An exception occurred while sending the request to the CSP : $errors" );
		}

		if ( $response['response']['code'] >= 400 ) {
			throw new Exception( "An exception occurred while sending the request to the CSP : {$response['response']['code']}" );
		}

		if ( $this->currentEnv !== 'prod' ) {
			error_log( "Successful HTTP $method to $endpoint with data " . json_encode( $data ) );
		}

		try {
			return json_decode( wp_remote_retrieve_body( $response ), true );
		} catch ( Exception $e ) {
			throw new Exception( "An exception occurred while decoding the response from the CSP" );
		}
	}

	/**
	 * @return CspPluginKeyMetaEntity|null
	 * @throws Exception
	 * @since 2.0.0
	 * @access protected
	 */
	public function get_meta( $apiKey = null ) {
		$endpoint = $this->cspBaseUrl . '/api/beta/meta';
		$method   = 'GET';

		$meta = $this->do_request( $endpoint, $method, [], $apiKey );

		if ( ! is_array( $meta ) ) {
			return null;
		}

		return CspPluginKeyMetaEntity::fromArray( $meta );
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function get_api_key() {
		$options = get_option( CspPluginConstants::CSP_PLUGIN_OPTIONS_KEY );
		if ( ! isset( $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ] ) || null === $apiKey = $options[ CspPluginConstants::CSP_PLUGIN_SETTINGS_API_KEY_KEY ] ) {
			throw new Exception( "No API key found" );
		}

		return $apiKey;
	}
}