<?php
/**
 * Post types
 *
 * Created by PhpStorm.
 * User: apple
 * Date: 2019-06-29
 * Time: 17:59
 *
 * @since      1.0.0
 */

namespace TourismTiger\Refresh_Links;


class FieldGroups {

    /**
     * Fields constructor.
     */
    function __construct () {

        add_action('acf/init', __CLASS__ . '::register_option_page', 20);

    }

    protected static function prefix($group_key = '', $field_key = '') {
        return PREFIX . '_' . $group_key . '_' . $field_key;
    }

    /**
     * @link https://admin.tourcms.com/admin/setup/api/setup_api.php
     */
    public static function register_option_page() {

        $key = 'optp_key';

        acf_add_local_field_group(array(
            'key' => self::prefix($key, '001'),
            'title' => sprintf( __('%s Settings', TEXT_DOMAIN), PLUGIN_SHORTNAME ),
            'fields' => array(
                array(
                    'key' => self::prefix($key, '002'),
                    'label' => __('Refresh links', TEXT_DOMAIN),
                    'instructions' => '',
                    'name' => 'refresh-links-tab',
                    'type' => 'tab',
                    'default'=>0,
                    'ui'=>1
                ),
                array(
                    'key' => self::prefix($key, '002a'),
                    'label' => __('Activate', TEXT_DOMAIN),
                    'instructions' => '',
                    'name' => 'refresh-links-active',
                    'type' => 'true_false',
                    'ui'=>1
                ),
                array(
                    'key' => self::prefix($key, '003'),
                    'label' => __('Needle', TEXT_DOMAIN),
                    'instructions' => 'The value being searched for (in posts content).',
                    'name' => 'needle',
                    'type' => 'text',
                ),
                array(
                    'key' => self::prefix($key, '004'),
                    'label' => __('Replace', TEXT_DOMAIN),
                    'instructions' => 'The replacement value that replaces found search values (in posts content).',
                    'name' => 'replace',
                    'type' => 'text',
                ),
                array(
                    'key' => self::prefix($key, '005'),
                    'label' => __('Use previous logic (replace http:// with https:// in links)', TEXT_DOMAIN),
                    'instructions' => '',
                    'name' => 'http-replace',
                    'type' => 'true_false',
                    'default'=>0,
                    'ui'=>1
                ),
                array(
                    'key' => self::prefix($key, '006'),
                    'label' => __('Remove data from blog posts', TEXT_DOMAIN),
                    'instructions' => '',
                    'name' => 'remove-data-tab',
                    'type' => 'tab',
                    'default'=>0,
                    'ui'=>1
                ),
                array(
                    'key' => self::prefix($key, '007'),
                    'label' => __('Activate', TEXT_DOMAIN),
                    'instructions' => '',
                    'name' => 'remove-data-active',
                    'type' => 'true_false',
                    'ui'=>1
                ),
                array(
                    'key' => self::prefix($key, '008'),
                    'label' => __('Remove shortcodes', TEXT_DOMAIN),
                    'instructions' => 'Coma separated values to activate and proceed.',
                    'name' => 'shortcodes',
                    'type' => 'text',
                ),
                array(
                    'key' => self::prefix($key, '009'),
                    'label' => __('Remove images with dead links', TEXT_DOMAIN),
                    'instructions' => '',
                    'name' => 'remove-images-with-dead-links',
                    'type' => 'true_false',
                    'ui'=>1
                ),
                array(
                    'key' => self::prefix($key, '010'),
                    'label' => __('Generate posts content', TEXT_DOMAIN),
                    'instructions' => '',
                    'name' => 'generate-post-content-tab',
                    'type' => 'tab',
                    'default'=>0,
                    'ui'=>1
                ),
                array(
                    'key' => self::prefix($key, '011'),
                    'label' => __('Activate for blog posts', TEXT_DOMAIN),
                    'instructions' => '',
                    'name' => 'generate-post-content-for-blog-posts-active',
                    'type' => 'true_false',
                    'ui'=>1
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => PREFIX,
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ));
    }
}
