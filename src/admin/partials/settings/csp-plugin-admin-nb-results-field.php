<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @link       https://github.com/tschaeller
 * @since      1.0.0
 *
 * @package    Csp_Plugin
 * @subpackage Csp_Plugin/admin/partials
 */
$options = get_option($this->plugin_name.'_options');
if (!isset($options['nb_results'])) {
    $options['nb_results'] = '';
}
?>

<input
	id='<?php echo esc_attr($this->plugin_name) ?>_setting_nb_results'
	name='<?php echo esc_attr($this->plugin_name) ?>_options[nb_results]'
	type='text'
	value='<?php echo esc_attr($options['nb_results']); ?>'
/>