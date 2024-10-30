<?php

namespace model;

class CspPluginKeyMetaEntity {
	/**
	 * @var CspPluginContextMetaEntity
	 * @since 2.0.0
	 * @access private
	 */
	private $context;

	/**
	 * @var boolean
	 * @since 2.0.0
	 * @access private
	 */
	private $isValid;

	/**
	 * @return CspPluginContextMetaEntity
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * @param CspPluginContextMetaEntity $context
	 */
	public function set_context( $context ) {
		$this->context = $context;
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return $this->isValid;
	}

	/**
	 * @param bool $isValid
	 */
	public function set_is_valid( $isValid ) {
		$this->isValid = $isValid;
	}

	/**
	 * @param $data
	 *
	 * @return CspPluginKeyMetaEntity
	 * @since 2.0.0
	 * @access public
	 * @static
	 */
	public static function fromArray( $data ) {
		$keyMeta = new CspPluginKeyMetaEntity();
		if ( isset( $data['context'] ) ) {
			$keyMeta->set_context( CspPluginContextMetaEntity::fromArray( $data['context'] ) );
		}
		if ( isset( $data['isValid'] ) ) {
			$keyMeta->set_is_valid( boolval( $data['isValid'] ) );
		}

		return $keyMeta;
	}
}