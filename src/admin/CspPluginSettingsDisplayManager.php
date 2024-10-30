<?php

namespace admin;

class CspPluginSettingsDisplayManager {

	/**
	 * @param string $page
	 * @param string $section
	 */
	public static function do_settings_fields( $page, $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			if ( isset( $field['args']['hidden'] ) && $field['args']['hidden'] === true ) {
				call_user_func( $field['callback'], $field['args'] );
				continue;
			}

			$class = '';

			if ( isset( $field['args']['before']['content'] ) ) {
				$before_class = '';
				if ( ! empty( $field['args']['before']['class'] ) ) {
					$before_class = ' class="' . esc_attr( $field['args']['before']['class'] ) . '"';
				}

				$before_colspan = '';
				if ( ! empty( $field['args']['after']['colspan'] ) ) {
					$before_colspan = ' colspan="' . esc_attr( $field['args']['after']['colspan'] ) . '"';
				}

				echo "<tr {$before_class}>";
				echo "<th scope='row' $before_colspan>";
				echo $field['args']['before']['content'];
				echo "</th>";
				echo "</tr>";
			}

			if ( ! empty( $field['args']['class'] ) ) {
				$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
			}

			echo "<tr{$class}>";

			if ( ! empty( $field['args']['label_for'] ) ) {
				echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
			} else {
				echo '<th scope="row">' . $field['title'] . '</th>';
			}

			echo '<td>';
			call_user_func( $field['callback'], $field['args'] );
			echo '</td>';
			echo '</tr>';

			if ( isset( $field['args']['after']['content'] ) ) {
				$after_class = '';
				if ( ! empty( $field['args']['after']['class'] ) ) {
					$after_class = ' class="' . esc_attr( $field['args']['after']['class'] ) . '"';
				}

				$after_colspan = '';
				if ( ! empty( $field['args']['after']['colspan'] ) ) {
					$after_colspan = ' colspan="' . esc_attr( $field['args']['after']['colspan'] ) . '"';
				}

				echo "<tr {$after_class}>";
				echo "<th scope='row' $after_colspan>";
				echo $field['args']['after']['content'];
				echo "</th>";
				echo "</tr>";
			}
		}
	}
}
