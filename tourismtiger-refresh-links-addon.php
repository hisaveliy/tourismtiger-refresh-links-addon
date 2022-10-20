<?php

/*
Plugin Name: TourismTiger Refresh Links Add-on
Plugin URI: https://www.tourismtiger.com
Description: Replaces all the http links related to its domain to https, if the domain is based on https.
Version: 1.1.0
Author: TourismTiger
Author URI: https://www.tourismtiger.com
Text Domain: https-links
Domain Path: /lang
GitHub Plugin URI: https://github.com/hisaveliy/tourismtiger-refresh-links-addon.git
GitHub Plugin URI: hisaveliy/tourismtiger-refresh-links-addon.git
*/

namespace TourismTiger\Refresh_Links;

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('Https_Links') ) :

    define(__NAMESPACE__ . '\PREFIX', 'refresh_links');
    define(__NAMESPACE__ . '\TEXT_DOMAIN', 'refresh_links');
    define(__NAMESPACE__ . '\PLUGIN_SHORTNAME', 'Refresh Links');
    define(__NAMESPACE__ . '\PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
    define(__NAMESPACE__ . '\PLUGIN_SETTINGS_URL', admin_url('admin.php?page='.PREFIX));
    define(__NAMESPACE__ . '\PLUGIN_BASENAME', plugin_basename(PLUGIN_DIR) . '/tourismtiger-refresh-links-addon.php');

    class Https_Links
{
    protected static $instance = null;
    protected static $site_url = null;
    protected static $https_link = 0;
    protected static $needle = null;
    protected static $conditional = 0;
    protected static $http_replace = 0;

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

            if ( strpos(self::$site_url, 'https://' ) === 0) :
                self::$https_link = 1;
                self::$conditional = 1;
                self::$needle = str_replace('https://', 'http://', self::$site_url );
            endif;
        }

        return self::$instance;
    }

    /**
     * Fields constructor.
     */
    function __construct () {
        add_action('init', __CLASS__ . '::refresh_links');
        add_action('admin_bar_menu', __CLASS__ . '::admin_bar_menu', 500);

        self::init_plugin_action_links();

        spl_autoload_register( __CLASS__ . '::autoload' );

        new OptionsPageTT();
        new FieldGroups();
    }


    /**
     *
     */
    public static function refresh_links() {

        if ( isset($_GET['page']) && $_GET['page']==='refresh_links' ) :

            if ( get_field('remove-data-active', PREFIX)  ) :
                $shortcodes_str = get_field('shortcodes', PREFIX);
                $remove_images_with_dead_links = get_field('remove-images-with-dead-links', PREFIX);

                $shortcodes = $shortcodes_str ?  explode(',', str_replace(' ', '', $shortcodes_str)) : '';

                if ( $shortcodes && is_array($shortcodes) && count($shortcodes) )
                    $posts_with_shortcodes_processed = self::process_shortcodes_deletion($shortcodes);

                if ( $remove_images_with_dead_links ) :
                    $uploads_dir = wp_upload_dir();
                    $posts_with_images_removal_processed = self::process_dead_links_removal($uploads_dir['basedir'], $uploads_dir['baseurl']);
                endif;

                print_r_html([[[['$shortcodes'=>$shortcodes,
                    '$remove_images_with_dead_links'=>$remove_images_with_dead_links,
                    '$posts_with_shortcodes_processed'=>$posts_with_shortcodes_processed ?? 'no posts_with_shortcodes_removal_processed',
                    '$posts_with_images_removal_processed'=>$posts_with_images_removal_processed ?? 'no posts_with_images_removal_processed',
                    ]]]]);
            endif;


            if ( get_field('refresh-links-active', PREFIX)  ) :

                self::$http_replace = get_field('http-replace', PREFIX);

                if ( self::$http_replace ) :
                    self::$conditional = self::$https_link;
                else :
                    self::$needle = get_field('needle', PREFIX) ?? '';
                    self::$site_url = get_field('replace', PREFIX)?? '';

                    if ( self::$needle && self::$site_url )
                        self::$conditional = 1;
                endif;

                if ( self::$conditional ) :

                    $links_number = self::refresh_links_processing();

                    if ( $links_number )
                        show_notice( sprintf(__('%d links have been successfully refreshed!', 'tourismtiger-theme'), $links_number ), 'success' );
                    else
                        show_notice( __('All links are already updated!', 'tourismtiger-theme'), 'success' );

                elseif ( !self::$conditional ) :

                    if ( self::$http_replace && !self::$https_link )
                        show_notice( __('This site domain is not based on https!', 'tourismtiger-theme'), 'error' );

                    else
                        show_notice( __('Please fill out required fields!', 'tourismtiger-theme'), 'error' );

                endif;
            endif;

        endif;

    }


    private static function process_shortcodes_deletion( $shortcodes ){

        global $wpdb;
        $posts_ids = [];

        foreach ($shortcodes as $shortcode ) :
            $needle = '\\\[' . $shortcode . '.*\\\]';
            $regex = '/\[' . $shortcode . '(.*?)]/';

            $query = "SELECT * FROM " . $wpdb->posts . " WHERE post_content REGEXP '".$needle."'";
            $links = $wpdb->query( $query );

            if ( $links ) :
                $posts = get_posts(['numberposts'=> -1]);

                foreach ( $posts as $p ):
                    $post_content = $p->post_content;
                    $replace = preg_replace($regex, '', $post_content);
                    if ( $replace ) :
                        $posts_ids[] = $p->ID;
                        $p->post_content = $replace;
                        wp_update_post($p);
                    endif;
                endforeach;
            endif;
        endforeach;

        return ['$posts'=>count($posts_ids)];

    }



    public static function process_dead_links_removal($uploads_path, $uploads_url){
        $posts = get_posts(['numberposts'=> -1]);
        $updated = [];

        $regex = '/(<img)(.*?)(src=[\'\"])(.*)([\'\"])(.*?)(>)/';


        foreach ( $posts as $p ):
            $post_content = $p->post_content;

            preg_match_all($regex, $post_content, $matches);

            if ( $matches && is_array($matches) && count($matches) ) :
                $need_update = false;

                $images = $matches[0];
                $paths = [];

                foreach ( $matches[4] as $match ) :
                    $position = strpos( $match, ' ');
                    $paths[] = str_replace($uploads_url, $uploads_path, substr($match, 0, $position - 1));
                endforeach;

                foreach (  $paths as $key=>$path ) :
                    if ( !file_exists($path) ):
                        $need_update = true;
                        $post_content = str_replace($images[$key], '', $post_content);
                    endif;
                endforeach;

                $p->post_content = $post_content;

                if ($need_update)
                    $updated[$p->ID] = wp_update_post($p);

            endif;
        endforeach;

        return count($updated) ?: 'no posts_with_images_removal_processed';
    }


    public static function refresh_links_processing(){
        global $wpdb;
        $resp = 0;

        $query = "SELECT * FROM " . $wpdb->posts . " WHERE post_content LIKE '%".self::$needle."%'";
        $links = $wpdb->query( $query );

        $query = "SELECT * FROM " . $wpdb->postmeta . " WHERE meta_value LIKE '%".self::$needle."%'";
        $links_meta = $wpdb->query( $query );

        if ( $links ) :
            $query = "UPDATE {$wpdb->posts} 
                    SET 
                        post_content = REPLACE( post_content, '". self::$needle ."', '". self::$site_url ."' )
                    WHERE
                        post_content LIKE '%".self::$needle."%'";
            $resp += $wpdb->query( $query );
        endif;

        if ( $links_meta ) :
            $query = "UPDATE {$wpdb->postmeta} 
                    SET 
                        meta_value = REPLACE( meta_value, '". self::$needle ."', '". self::$site_url ."' )
                    WHERE
                        meta_value LIKE '%".self::$needle."%'";
            $resp += $wpdb->query( $query );
        endif;

        return $resp;
    }


    /**
     * @param $wp_admin_bar
     */
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


    public static function autoload($filename) {

        $dir = PLUGIN_DIR . '/autoload/class-*.php';
        $paths = glob($dir);

        if (defined('GLOB_BRACE')) {
            $paths = glob( '{' . $dir . '}', GLOB_BRACE );
        }

        if ( is_array($paths) && count($paths) > 0 ){
            foreach( $paths as $file ) {
                if ( file_exists( $file ) ) {
                    include_once $file;
                }
            }
        }
    }


    public static function init_plugin_action_links(){

        //add plugin action and meta links
        self::plugin_links(array(
            'actions' => array(
                PLUGIN_SETTINGS_URL => __('Settings', 'tourismtiger-theme'),
            ),
        ));
    }


    private static function plugin_links($sections = array()) {

            //actions
            if (isset($sections['actions'])){

                $actions = $sections['actions'];
                $links_hook = is_multisite() ? 'network_admin_plugin_action_links_' : 'plugin_action_links_';

                add_filter($links_hook.PLUGIN_BASENAME, function($links) use ($actions){

                    foreach(array_reverse($actions) as $url => $label){
                        $link = '<a href="'.$url.'">'.$label.'</a>';
                        array_unshift($links, $link);
                    }

                    return $links;

                });
            }

            //meta row
            if (isset($sections['meta'])){

                $meta = $sections['meta'];

                add_filter( 'plugin_row_meta', function($links, $file) use ($meta){

                    if (PLUGIN_BASENAME == $file){

                        foreach ($meta as $url => $label){
                            $link = '<a href="'.$url.'">'.$label.'</a>';
                            array_push($links, $link);
                        }
                    }

                    return $links;

                }, 10, 2 );
            }

        }
}

Https_Links::instance();

endif;
