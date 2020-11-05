<?php

/*
Plugin Name: TourismTiger Refresh Links Add-on
Plugin URI: https://www.tourismtiger.com
Description: Replaces all the http links related to its domain to https, if the domain is based on https.
Version: 1.0.0
Author: TourismTiger
Author URI: https://www.tourismtiger.com
Text Domain: https-links
Domain Path: /lang
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('Https_Links') ) :


class Https_Links
{
    protected static $instance = null;
    protected static $site_url = null;
    protected static $https_link = 0;
    protected static $needle = null;

    protected static $https_link_test = 0;
    protected static $replacement_test = null;

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
            self::$site_url = get_bloginfo('url');

            if (strpos(self::$site_url, 'https://')) :
                self::$https_link = 1;
                self::$needle = str_replace('https://', 'http://', self::$site_url );
            endif;

            if (strpos(self::$site_url, 'http://')) : // TODO: Test
                self::$https_link_test = 1;
                self::$replacement_test = str_replace('http://', 'https://', self::$site_url );
            endif;
        }

        return self::$instance;
    }

    /**
     * Fields constructor.
     */
    function __construct () {
        add_filter('the_content', __CLASS__.'::update_content');
        add_filter('tt2_editor_content', __CLASS__.'::update_content');
        add_action('admin_bar_menu', __CLASS__ . '::admin_bar_menu', 500);
    }


    public static function admin_bar_menu( $wp_admin_bar ) {
        $item = (object) array(
            'slug' => 'refresh_links',
            'name' => __('Refresh links', 'tourismtiger-theme'),
        );

        $title = sprintf(
            '<span class="ab-label"><span class="screen-reader-text">%1$s</span>%2$s</span>',
            $item->name,
            esc_html( $item->name )
        );

        $wp_admin_bar->add_menu(
            array(
                'id'    => 'refresh_links',
                'title' => $title,
                'href'  => esc_url( add_query_arg( 'refresh_links', 1, remove_query_arg( 'paged' ) ) ),
                'meta'  => array( 'title' => $item->name ),
            )
        );
    }


    public static function update_content($content){
        if (self::$https_link)
            $content = str_replace(self::$needle, self::$site_url, $content);

        print_r_html([self::$https_link_test, strpos(self::$site_url, 'http://'), strpos(self::$site_url, 'https://')]);

        if (self::$https_link_test)
            $content = str_replace(self::$site_url, self::$replacement_test, $content);

        return $content;
    }

}

Https_Links::instance();

endif;
