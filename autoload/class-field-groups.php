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
                array (
                    'key' => self::prefix($key, '002'),
                    'label' => 'General',
                    'name' => 'tab_g',
                    'type' => 'tab',
                    'required' => 0,
                    'placement' => 'top'
                ),
                array(
                    'key' => self::prefix($key, '003'),
                    'label' => __('Account Key', TEXT_DOMAIN),
                    'name' => 'account_key',
                    'type' => 'text',
                    'placeholder' => 'e.g. 5ece74efc75cbf1769efe811',
                ),
                array(
                    'key' => self::prefix($key, '004'),
                    'label' => __('Show the widget on all pages', TEXT_DOMAIN),
                    'name' => 'show_on_all_pages',
                    'type' => 'true_false',
                    'ui' => 1,
                    'wrapper' => [
                        'width' => 25
                    ],
                ),
                array(
                    'key' => self::prefix($key, '005'),
                    'label' => __('Choose pages where to show', TEXT_DOMAIN),
                    'name' => 'show_on_pages',
                    'type' => 'post_object',
                    'post_type' => array (
                        'post',
                        'page',
                        'product',
                    ),
                    'return_format' => 'id',
                    'multiple' => 1,
                    'wrapper' => [
                        'width' => 75
                    ],
                    'conditional_logic' => [
                        [
                            'field' => self::prefix($key, '004'),
                            'operator' => '==',
                            'value' => false,
                        ]
                    ]
                ),
                array (
                    'key' => self::prefix($key, '015'),
                    'label' => 'Extra widgets',
                    'name' => 'tab_e',
                    'type' => 'tab',
                    'required' => 0,
                    'placement' => 'top'
                ),
                array(
                    'key' => self::prefix($key, '006'),
                    'label' => __('Extra widgets', TEXT_DOMAIN),
                    'name' => 'extra_widgets',
                    'type' => 'repeater',
                    'button_label' => 'Add widget',
                    'instructions' => 'Use for widgets with a specific ID apart from default.',
                    'layout' => 'block',
                    'sub_fields' => [
                        array(
                            'key' => self::prefix($key, '007'),
                            'label' => __('Widget ID', TEXT_DOMAIN),
                            'name' => 'widget_id',
                            'type' => 'text',
                            'placeholder' => 'e.g. 1esj6cls4',
                        ),
                        array(
                            'key' => self::prefix($key, '008'),
                            'label' => __('Show the widget on all pages', TEXT_DOMAIN),
                            'name' => 'show_on_all_pages',
                            'type' => 'true_false',
                            'ui' => 1,
                            'wrapper' => [
                                'width' => 25
                            ],
                        ),
                        array(
                            'key' => self::prefix($key, '009'),
                            'label' => __('Choose pages where to show', TEXT_DOMAIN),
                            'name' => 'show_on_pages',
                            'type' => 'post_object',
                            'post_type' => array (
                                'post',
                                'page',
                                'product',
                            ),
                            'return_format' => 'id',
                            'multiple' => 1,
                            'wrapper' => [
                                'width' => 75
                            ],
                            'conditional_logic' => [
                                [
                                    'field' => self::prefix($key, '008'),
                                    'operator' => '==',
                                    'value' => false,
                                ]
                            ]
                        ),
                    ]
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
