<?php
/**
 * Main class which sets all together
 *
 * @since      1.0.0
 */

namespace TourismTiger\Refresh_Links;


class OptionsPageTT extends  OptionsPage {

    /**
     * Fields constructor.
     */
    function __construct () {
        parent::__construct();

        add_action('tt2/option-pages', __CLASS__ . '::acf_add_options_page');
    }

    /**
     * Creating Options Page
     *
     * @link https://www.advancedcustomfields.com/resources/acf_add_options_page/
     */
    public static function acf_add_options_page() {

        if ( function_exists( 'acf_add_options_page' ) ) :

            acf_add_options_sub_page(array(
                'page_title' => sprintf( __('%s Settings', TEXT_DOMAIN), PLUGIN_SHORTNAME ),
                'menu_title' => PLUGIN_SHORTNAME,
                'parent_slug' => 'tourismtiger',
                'menu_slug'   => PREFIX, // this value used for adding the fields at /autoload/class-field-groups.php
                'post_id'     => PREFIX,
            ));

        endif;

    }
}
