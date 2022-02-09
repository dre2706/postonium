<?php
/*
    Plugin Name: Postonium
    Description: Adds information about read time, word count and more to a post
    Version: 1.0
    Author: Andreas Walter
    Author URI: 
*/
class Postonium {

    function __construct() {
        add_action( 'admin_menu', array( $this, 'adminPage' ));
        add_action( 'admin_init', array( $this, 'settings' ));
        add_filter( 'the_content', array( $this, 'addPostonium'));
    }

    function addPostonium($content) {
        if (is_single() && is_main_query() && 
        (
            get_option('postonium_wordcount', '1') || 
            get_option('postonium_charcount', '1') ||
            get_option('postonium_readtime', '1')             
        )) {
            return $this->createHTML($content);   
        } 
        return $content;
    }

    function createHTML($content) {
        $html = '<h4>' . esc_html(get_option('postonium_headline', 'Word Stats')) . '</h4><p>';

        if (get_option('postonium_wordcount', '1') || get_option('postonium_charcount', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if (get_option('postonium_wordcount', '1')) {
            $html .= 'This post has ' . $wordCount . ' words. <br>';
        }

        if (get_option('postonium_charcount', '1')) {
            $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters. <br>';
        }

        if (get_option('postonium_readtime', '1')) {
            $readtime = round($wordCount/225);
            
            if ($readtime == '0') {
                $html .= 'This post will take less than a minute to read. <br>';
            } 
            else if ($readtime == '1') {
                $html .= 'This post will take about ' . $readtime . ' minute to read. <br>';
            } 
            else {
                $html .= 'This post will take about ' . $readtime . ' minutes to read. <br>';        
            }   
        }

        if (get_option('postonium_location', '0') == '0') {
            return $html . $content;
        }
        return $content . $html;
    }

    function settings() {
        add_settings_section( 'postonium_first_section', null, null, 'postonium-settings-page' );
        
        add_settings_field( 'postonium_location', 'Display Location', array( $this, 'locationHTML' ), 'postonium-settings-page', 'postonium_first_section' );
        register_setting( 'postoniumplugin', 'postonium_location', array( 'sanitize_callback' => array($this, 'sanitize_location'), 'default' => '0' ));

        add_settings_field( 'postonium_headline', 'Headline Text', array( $this, 'headlineHTML' ), 'postonium-settings-page', 'postonium_first_section' );
        register_setting( 'postoniumplugin', 'postonium_headline', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => __('Word Stats') ));

        add_settings_field( 'postonium_wordcount', 'Word Count', array( $this, 'checkboxHTML' ), 'postonium-settings-page', 'postonium_first_section', array('optionName' => 'postonium_wordcount') );
        register_setting( 'postoniumplugin', 'postonium_wordcount', array( 'sanitize_callback' => array($this, 'sanitize_checkbox_1'), 'default' => '1' ));

        add_settings_field( 'postonium_charcount', 'Character Count', array( $this, 'checkboxHTML' ), 'postonium-settings-page', 'postonium_first_section', array('optionName' => 'postonium_charcount') );
        register_setting( 'postoniumplugin', 'postonium_charcount', array( 'sanitize_callback' => array($this, 'sanitize_checkbox_2'), 'default' => '1' ));

        add_settings_field( 'postonium_readtime', 'Read Time', array( $this, 'checkboxHTML' ), 'postonium-settings-page', 'postonium_first_section', array('optionName' => 'postonium_readtime') );
        register_setting( 'postoniumplugin', 'postonium_readtime', array( 'sanitize_callback' => array($this, 'sanitize_checkbox_3'), 'default' => '1' ));
    }

    function sanitize_location($input){
        if ($input != '0' && $input != '1') {
            add_settings_error( 'postonium_location', 'postonium_location_error', __('Input Error - Please refresh your Browser Tab'));
            return get_option( 'postonium_location');
        }
        return $input;
    }

    function sanitize_checkbox_1($input){
        if ($input != '' && $input != '1') {
            add_settings_error( 'postonium_wordcount', 'postonium_location_error', __('Input Error - Please refresh your Browser Tab'));
            return get_option( 'postonium_wordcount');
        }
        return $input;
    }

    function sanitize_checkbox_2($input){
        if ($input != '' && $input != '1') {
            add_settings_error( 'postonium_charcount', 'postonium_location_error', __('Input Error - Please refresh your Browser Tab'));
            return get_option( 'postonium_charcount');
        }
        return $input;
    }

    function sanitize_checkbox_3($input){
        if ($input != '' && $input != '1') {
            add_settings_error( 'postonium_readtime', 'postonium_location_error', __('Input Error - Please refresh your Browser Tab'));
            return get_option( 'postonium_readtime');
        }
        return $input;
    }

    function locationHTML() { ?>
        <select name="postonium_location">
            <option value="0" <?php selected( get_option( 'postonium_location' ), '0') ?>>Beginning of post</option>
            <option value="1" <?php selected( get_option( 'postonium_location' ), '1') ?>>End of post</option>
        </select>    
    <?php 
    }

    function headlineHTML() { ?>
        <input type="text" name="postonium_headline" value="<?php echo esc_attr(get_option( 'postonium_headline' ))?>">
    <?php    
    }

    function checkboxHTML($args) { ?>
        <input type="checkbox" name="<?php echo $args['optionName'] ?>" value="1" <?php checked(get_option($args['optionName']), '1')?>>
    <?php
    }

    function adminPage() {
        add_options_page( 'Postonium', 'Postonium Settings', 'manage_options', 'postonium-settings-page', array( $this, 'SettingsPageHTML' ) );
    }

    function SettingsPageHTML() { ?>
        <div class="wrap">
            <h1>Postonium Settings</h1>
                <form action="options.php" method="POST">
                    <?php
                        settings_fields( 'postoniumplugin' );
                        do_settings_sections( 'postonium-settings-page' );
                        submit_button();
                    ?>
                </form>
        </div>
    <?php 
    }
}
$postonium = new Postonium();