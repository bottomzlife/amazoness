<?php

namespace Netspin\Amazoness;

require 'vendor/erusev/parsedown/Parsedown.php';

class Setting {
    private $fields;
    private $options;
    private $setting_name = \Netspin\Amazoness::PLUGIN_SETTING_NAME;
    private $section_core_id_suffix = '_section_core';

    private function get_this_plugin_root() {
        return preg_replace( '/\/lib\/.*?$/', '/', __FILE__ );
    }

    public function __construct() {
        $this->fields = [
            [
                'field_id' => 'associate_id',
                'field_name' => gg('Amazon Associate ID'),
                'field_type' => 'input',
                'default_value' => \Netspin\Amazoness::DEFAULT_ASSOCIATE_ID,
                'validate_rule' => '/^[a-z0-9]{1,}-[0-9]{1,}$/'
            ],
            [
                'field_id' => 'image_size',
                'field_name' => gg('Image Size Descriptor'),
                'field_type' => 'select',
                'default_value' => \Netspin\Amazoness::DEFAULT_IMAGE_SIZE,
                'options' => \Netspin\Amazoness::IMAGE_SIZES,
                'validate_rule' =>
                    '/^(' . join('|',  \Netspin\Amazoness::IMAGE_SIZES ) . ')$/'
            ],
            [
                'field_id' => 'css_definition',
                'field_name' => gg('CSS Definition'),
                'field_type' => 'textarea',
                'default_value' => \Netspin\Amazoness::CSS_DEFINITION,
                'validate_rule' => '//'
            ],
            [
                'field_id' => 'html_template',
                'field_name' => gg('Template HTML'),
                'field_type' => 'textarea',
                'default_value' => \Netspin\Amazoness::HTML_TEMPLATE,
                'validate_rule' => '//'
            ]
        ];
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }
 
    public function add_plugin_page() {
        if ( \Netspin\Amazoness::MENU_LOCATION == 'root' ) {
            // as a root item of WordPress administration page
            add_menu_page(
                \Netspin\Amazoness::PLUGIN_NAME,
                \Netspin\Amazoness::PLUGIN_NAME,
                'manage_options',
                $this->setting_name,
                array( $this, 'create_admin_page' ),
                ''
            );
        } else {
            // as a sub menu of 'Options-General'
            add_options_page(
                \Netspin\Amazoness::PLUGIN_NAME,
                \Netspin\Amazoness::PLUGIN_NAME,
                'manage_options',
                $this->setting_name,
                array( $this, 'create_admin_page' )
            );
        }
    }
 
    public function page_init() {
        register_setting(
            $this->setting_name,
            $this->setting_name,
            array( $this, 'validate' )
        );
 
        add_settings_section(
            $this->setting_name . $this->section_core_id_suffix,
            '',
            '',
            $this->setting_name
        );
 
        $add_settings_field_typical = function ( $attr ) {
            $id = $attr['field_id'];
            $title = $attr['field_name'];
            $default = $attr['default_value'];
            $field_type = $attr['field_type'];
            $options = $attr['options'];
            add_settings_field(
                $id,
                $title,
                function () use ( &$id, &$default, &$field_type, &$options ) {
                    call_user_func(
                        array( $this, 'put_' . $field_type . '_field' ),
                        $id,
                        $default,
                        $options
                    );
                },
                $this->setting_name,
                $this->setting_name . $this->section_core_id_suffix
                    , $id, $default
            );
        };

        for ( $i=0; $i<count($this->fields); $i++ ) {
            $attr = $this->fields[$i];
            $add_settings_field_typical( $attr );
        }

    }
 
    private function put_input_field( $name, $default ) {
        $default = isset( $default ) ? $default : '';
        $$name =
            isset( $this->options[$name] )
            ? $this->options[$name]
            : $default
        ;
        ?>
<input
    type="text"
    id="<?php echo $name; ?>"
    name="<?php echo $this->setting_name; ?>[<?php echo $name; ?>]"
    value="<?php echo esc_attr( $$name ) ?>"
>
[
<a 
    href="#setting_<?php echo $name; ?>"
>?</a>
]
        <?php
    }

    private function put_select_field( $name, $default, $choices ) {
        $default = isset( $default ) ? $default : '';
        $$name =
            isset( $this->options[$name] )
            ? $this->options[$name]
            : $default
        ;
        ?>
<select
    id="<?php echo $name; ?>"
    name="<?php echo $this->setting_name; ?>[<?php echo $name; ?>]"
    size="1"
>
        <?php
        for ( $i=0; $i<count($choices); $i++ ) {
            $choice = $choices[$i];
            $is_selected =
                ( $$name == $choice )
                ? 'selected'
                : ''
            ;
            ?>
    <option
        value="<?php echo esc_attr( $choice ); ?>"
        <?php echo $is_selected; ?>
    >
        <?php echo esc_html( $choice ); ?>
            <?php
        }
        ?>
</select>
[
<a 
    href="#setting_<?php echo $name; ?>"
>?</a>
]
        <?php
    }
 
    private function put_textarea_field( $name, $default ) {
        $default = isset( $default ) ? $default : '';
        $$name =
            isset( $this->options[$name] )
            ? $this->options[$name]
            : $default
        ;
        ?>
<textarea
    type="text"
    id="<?php echo $name; ?>"
    name="<?php echo $this->setting_name; ?>[<?php echo $name; ?>]"
    style="
        width: 80%;
        height: 8em;
        font-family: Consolas, Monaco, monospace;
    "
><?php echo esc_attr( $$name ) ?></textarea>
<br>
[
<a 
    href="#setting_<?php echo $name; ?>"
>?</a>
]
        <?php
    }
 
    public function create_admin_page() {
        $this->options = get_option( $this->setting_name );
        $css_path_markdown =
            preg_replace( '/^https?:[^\/]+/', '', WP_PLUGIN_URL )
            . preg_replace( '/^.*plugins/', '', __DIR__ )
            . '/github-markdown-css/github-markdown.css'
        ;
        global $locale;
        $lang = preg_replace( '/^([a-z0-9]{2}).{0,}$/', '$1', $locale );
        $markdown_file = 
            $this->get_this_plugin_root()
            . 'readme.' . $lang . '.md'
        ;
        if ( is_file( $markdown_file ) && is_readable( $markdown_file ) ) {
            ;
        } else {
            $markdown_file =
                preg_replace(
                    '/\.[^\.]+\.md$/',
                    '.md',
                    $markdown_file
                )
            ;
        }
        $markdown = file_get_contents( $markdown_file );
        $Parsedown = new \Parsedown();
        $html_markdown = $Parsedown->text( $markdown );
        ?>
<div class="wrap">
    <h2><?php echo \Netspin\Amazoness::PLUGIN_NAME . gg(' Configuration'); ?></h2>
    <?php
        global $parent_file;
        if ( $parent_file != 'options-general.php' ) {
            require(ABSPATH . 'wp-admin/options-head.php');
        }
    ?>
    <form method="post" action="options.php">
    <?php
        settings_fields( $this->setting_name );
        do_settings_sections( $this->setting_name );
        submit_button( gg('Save Configuration') );
        submit_button(
            gg('Reset Configuration'),
            'reset secondary',
            $this->setting_name . '[submit_reset]'
        );
    ?>
    </form>
</div>
<link rel="stylesheet"" type="text/css" href="<?php echo $css_path_markdown ?>">
<style>
    <?php echo \Netspin\Amazoness::CSS_ADMIN_PAGE; ?>
</style>
<hr>
<p>
    <?php echo gg('Version') . ': ' . \Netspin\Amazoness::VERSION; ?>
</p>
<section class="markdown-body">
    <?php echo $html_markdown; ?>
</section>
<hr>
        <?php
    }
 
    public function validate( $input ) {
        $this->options = get_option( $this->setting_name );
        $new_input = array();

        for ( $i=0; $i<count($this->fields); $i++ ) {
            $attr = $this->fields[$i];
            $id = $attr['field_id'];
            $value = trim( $input[$id] );
            if ( $input['submit_reset'] != '' ) {
                $new_input[$id] = $attr['default_value'];
                continue;
            }
            if (
                isset( $input[$id] )
                && $value !== ''
                && preg_match( $attr['validate_rule'], $value )
            ) {
                $new_input[$id] = $value;
                continue;
            }
            add_settings_error(
                $this->setting_name,
                $id,
                sprintf(
                    'Invalid value for "%s"',
                        $attr['field_name']
                )
            );
            $new_input[$id] =
                isset( $this->options[$id] )
                ? $this->options[$id]
                : ''
            ;
        }

        return $new_input;
    }
 
}

call_user_func(function () {
    \Netspin\Amazoness::load_gettext_textdomain();
    function gg( $message ) {
        return __( $message, \Netspin\Amazoness::PLUGIN_NAME_ASCII );
    }
    if( is_admin() ) {
        $settings = new Setting();
    }
});
