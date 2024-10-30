<?php

/**
 * @link              http://wpboxr.com
 * @since             1.0.0
 * @package           Cbxauthorglossary
 *
 * @wordpress-plugin
 * Plugin Name:       CBX Author Glossary
 * Plugin URI:        https://codeboxr.com/product/cbx-author-glossary
 * Description:       Author Glossary List
 * Version:           1.0.5
 * Author:            Codeboxr
 * Author URI:        https://codeboxr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cbxauthorglossary
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

defined('CBXAUTHORGLOSSARY_PLUGIN_NAME') or define('CBXAUTHORGLOSSARY_PLUGIN_NAME', 'cbxauthorglossary');
defined('CBXAUTHORGLOSSARY_PLUGIN_VERSION') or define('CBXAUTHORGLOSSARY_PLUGIN_VERSION', '1.0.5');
defined('CBXAUTHORGLOSSARY_BASE_NAME') or define('CBXAUTHORGLOSSARY_BASE_NAME', plugin_basename(__FILE__));
defined('CBXAUTHORGLOSSARY_ROOT_PATH') or define('CBXAUTHORGLOSSARY_ROOT_PATH', plugin_dir_path(__FILE__));
defined('CBXAUTHORGLOSSARY_ROOT_URL') or define('CBXAUTHORGLOSSARY_ROOT_URL', plugin_dir_url(__FILE__));

/**
 * CBX Author Glossary List.
 *
 * Defines the shortcode of listing the author list.
 *
 * @package    Cbxauthorglossary
 * @author     Codeboxr <info@codeboxr.com>
 */
class CBXAuthorGlossary {

    /**
     * Initialize the class and set its properties.
     *
     */
    public function __construct() {
	    //load text domain
	    load_plugin_textdomain('cbxauthorglossary', false, dirname(plugin_basename(__FILE__)) . '/languages/');

	    add_shortcode('cbxauthorglossary', array($this, 'cbxauthor_shortcodedisplay'));
    }

    /**
     * Load the view of author list
     *
     * @param $atts, 
     * @return html
     */
    public function cbxauthor_shortcodedisplay($atts) {

        $atts = shortcode_atts(array(
            'role' => '',
            'orderby'=> 'display_name',
            'order'=> 'DESC',
            'include'=> '',
            'exclude'=> '',
            'blogid' => '0',
            'number' => '0'
                ), $atts, 'cbxauthorglossary');

        if ($atts['role'] == '')
            return '';

        wp_register_style('cbxauthorglossary-style', plugin_dir_url(__FILE__) . 'includes/css/cbxauthorglossary.css');
        wp_enqueue_style('cbxauthorglossary-style');
        
        global $wpdb;
        $include = array();
        $exclude = array();
        $active_letters = array();
        $role_arr = array();
        $data = array();
        
        $glossary_letter = '';
        $letter_box_htmnl = '';
         
        $orderby = esc_attr($atts['orderby']);
        $order =   esc_attr($atts['order']);
        $include = (!empty($atts['include'])) ? explode(",", $atts['include']) : array_filter($include);
        $exclude = (!empty($atts['exclude'])) ? explode(",", $atts['exclude']) : array_filter($exclude);
        $blogid =  abs($atts['blogid']);
        $number =  abs($atts['number']);
        $roles = explode(",", $atts['role']);
      

        $role_arr['relation'] = 'OR';
        foreach ($roles as $role) {
            $role_arr[] = array(
                'key' => $wpdb->prefix . 'capabilities',
                'value' => strtolower($role),
                'compare' => 'like'
            );
        }

        
        $users = new WP_User_Query(     
                 array(
                 'include' => $include,
                 'exclude' => $exclude,
                 'orderby' => $orderby,
                 'order' => $order,
                 'blog_id' => $blogid,
                 'number' => $number,
                 'meta_query' => $role_arr
                )
                );
        

        foreach ($users->get_results() as $key => $value) {

            $user_first_letter = strtolower(substr($value->data->display_name, 0, 1));

            $active_letters[] = $user_first_letter;
            $data[$user_first_letter][] = '<li class="cbxauthglossary_listitem"><a href="' . get_author_posts_url($value->data->ID) . '">' . $value->data->display_name . '</a></li>';
        }


        $cbxletter = '<ul class="cbxauthglossary_letters">';

        foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z') as $letter) {
            $cbxletter .= '<li class="cbxauthglossary_letter">' . (in_array(strtolower($letter), $active_letters) ? '<a  href="#cbxauthglossary_item' . $letter . '">' . $letter . '</a>' : $letter) . '</li>';

            if (isset($data[strtolower($letter)]) && sizeof($letter_box_items = $data[strtolower($letter)]) > 0) {

                $letter_box_htmnl .= '<div class="cbxauthglossary_items">';
                $letter_box_htmnl .= '<p class="cbxauthglossary_itemletter" id="cbxauthglossary_item' . $letter . '">' . $letter . '</p>';
                $letter_box_htmnl .= '<ul class="cbxauthglossary_list">' . implode('', $letter_box_items) . '</ul>';
                $letter_box_htmnl .= '</div>';
            }
        }

        $cbxletter .= '</ul>';

        return '<div class="cbxauthglossary_wrap"><div class="cbxletter clear">' . $cbxletter . '</div><div class="cbxauthglossary_boxitems">' . $letter_box_htmnl . '</div></div>';
    }

}

new CBXAuthorGlossary();
