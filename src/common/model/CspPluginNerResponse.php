<?php

namespace model;

class CspPluginNerResponse {
	private $entities;
	private $html_overlap_errors;
	private $article;

	public function __construct( $entities, $html_overlap_errors, $article ) {
		$this->entities            = $entities;
		$this->html_overlap_errors = $html_overlap_errors;
		$this->article             = $article;
	}

	/**
	 * @return mixed
	 */
	public function get_entities() {
		return $this->entities;
	}

	/**
	 * @param mixed $entities
	 */
	public function set_entities( $entities ) {
		$this->entities = $entities;
	}

	/**
	 * @return mixed
	 */
	public function get_html_overlap_errors() {
		return $this->html_overlap_errors;
	}

	/**
	 * @param mixed $html_overlap_errors
	 */
	public function set_html_overlap_errors( $html_overlap_errors ) {
		$this->html_overlap_errors = $html_overlap_errors;
	}

	/**
	 * @return mixed
	 */
	public function get_article() {
		return $this->article;
	}

	/**
	 * @param mixed $article
	 */
	public function set_article( $article ) {
		$this->article = $article;
	}
}