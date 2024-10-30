<?php

namespace model;

class CspPluginNamedEntity {
	private $id;
	private $entity;
	private $type;
	private $score;
	private $url;

	private $start_char;
	private $end_char;
	private $from;

	private $selected = false;
	private $is_candidate = true;

	/**
	 * @return mixed
	 */
	public function get_entity() {
		return $this->entity;
	}

	/**
	 * @param mixed $entity
	 */
	public function set_entity( $entity ) {
		$this->entity = $entity;
	}

	/**
	 * @return mixed
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @param mixed $type
	 */
	public function set_type( $type ) {
		$this->type = $type;
	}

	/**
	 * @return string|float
	 */
	public function get_score( $formatted = false ) {
		if ( $formatted ) {
			return sprintf( "%.2f%%", $this->score * 100 );
		}

		return $this->score;
	}

	/**
	 * @param float $score
	 */
	public function set_score( $score ) {
		$this->score = $score;
	}

	/**
	 * @return mixed
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * @param mixed $url
	 */
	public function set_url( $url ) {
		$this->url = $url;
	}

	/**
	 * @return bool
	 */
	public function is_selected() {
		return $this->selected;
	}

	/**
	 * @param bool $selected
	 */
	public function set_selected( $selected ) {
		$this->selected = $selected;
	}

	/**
	 * @return mixed
	 */
	public function get_start_char() {
		return $this->start_char;
	}

	/**
	 * @param mixed $start_char
	 */
	public function set_start_char( $start_char ) {
		$this->start_char = $start_char;
	}

	/**
	 * @return mixed
	 */
	public function get_end_char() {
		return $this->end_char;
	}

	/**
	 * @param mixed $end_char
	 */
	public function set_end_char( $end_char ) {
		$this->end_char = $end_char;
	}

	/**
	 * @return false|string
	 */
	public function to_json() {
		return json_encode( $this->to_array() );
	}

	/**
	 * @return mixed
	 */
	public function get_from() {
		return $this->from;
	}

	/**
	 * @param mixed $from
	 */
	public function set_from( $from ) {
		$this->from = $from;
	}

	/**
	 * @return mixed
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * @return bool
	 */
	public function is_candidate() {
		return $this->is_candidate;
	}

	/**
	 * @param bool $is_candidate
	 */
	public function set_is_candidate( $is_candidate ) {
		$this->is_candidate = $is_candidate;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		return [
			"id"           => $this->get_id(),
			"is_candidate" => $this->is_candidate(),
			"entity"       => $this->get_entity(),
			"type"         => $this->get_type(),
			"score"        => $this->get_score( true ),
			"url"          => $this->get_url(),
			"selected"     => $this->is_selected(),
			"start_char"   => $this->get_start_char(),
			"end_char"     => $this->get_end_char(),
			"from"         => $this->get_from(),
		];
	}

	/**
	 * @param $data
	 *
	 * @return CspPluginNamedEntity
	 * @throws Exception
	 */
	public static function from_array( $data ) {
		CspPluginNamedEntity::validate_input_array( $data );

		$entity = new CspPluginNamedEntity();

		$entity->set_entity( $data["entity"] );
		$entity->set_type( $data["type"] );
		$entity->set_score( $data["score"] );
		$entity->set_start_char( $data["start_char"] );
		$entity->set_end_char( $data["end_char"] );
		$entity->set_from( $data["from"] );

		if ( isset( $data["url"] ) ) {
			$entity->set_url( $data["url"] );
		}

		if ( ! empty( $data["id"] ) ) {
			$entity->set_id( $data["id"] );
			$entity->set_is_candidate( false );
		}

		return $entity;
	}

	/**
	 * @param $json
	 *
	 * @return CspPluginNamedEntity
	 * @throws Exception
	 */
	public static function from_json( $json ) {
		$entityAsArray = json_decode( $json );

		return CspPluginNamedEntity::from_array( $entityAsArray );
	}

	/**
	 * @param $data
	 *
	 * @return void
	 * @throws Exception
	 */
	private static function validate_input_array( $data ) {
		$mandatoryKeys = [ "entity", "type", "score", "start_char", "end_char", "from" ];

		foreach ( $mandatoryKeys as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				throw new Exception( "Missing key: " . $key );
			}
		}
	}
}