<?php
// ToDo: 日本語MD追加

namespace Netspin;

require_once 'Cache/Lite.php';
require_once 'vendor/Netspin/Amazoness/Setting.php';

class Amazoness {
    const VERSION = '0.9.5';
    const DEFAULT_ASSOCIATE_ID = 'netspin-22';
    const DEFAULT_IMAGE_SIZE = 'LZZZZZZZ';
    const BASE_URL_PRODUCT =
        'http://www.amazon.co.jp/gp/product/%%ASIN%%?tag=%%ASSOCIATE_ID%%'
    ;
    const BASE_URL_PRODUCT_IMAGE =
        'http://images-jp.amazon.com/images/P/%%ASIN%%.09.%%SIZE%%.jpg'
    ;
    const HTML_TEMPLATE = <<< _EOF_
<div
    class="amazoness"
>
    <a
        href="%%PRODUCT_URL|url%%"
        title="%%PRODUCT_TITLE|html%%"
    >
        <dl class="amazoness-products">
            <dt>
                <img
                    src="%%PRODUCT_IMAGE_URL|url%%"
                    alt="%%PRODUCT_TITLE|html%%"
                >
            <dd>
                <ul>
                    <li class="amazoness-products-titles">
                        %%PRODUCT_TITLE|html%%
                    <li class="amazoness-products-descriptions">
                        %%PRODUCT_DESCRIPTION|html%%
                </ul>
        </dl>
        <footer class="amazoness-link-to-amazons">
            <span class="amazoness-leader-to-amazons">&#x25B6;</span>
            <span class="amazoness-link-to-amazon-texts">Check this at Amazon</span>
        </footer>
    </a>
</div>
<!-- IS_CACHED: %%IS_CACHED%% -->
_EOF_;
    const CSS_DEFINITION = <<< _EOF_
.amazoness {
    word-wrap: break-word;
}
.amazoness footer {
    margin: 0;
}
.amazoness a {
    display: block;
    border: 1px solid #cecece;
    border-bottom-color: #565656;
    border-right-color: #565656;
    margin-bottom: 1em;
    padding: 1em;
    border-radius: .3em;
        -webkit-border-radius: .3em;
        -moz-border-radius: .3em;
    box-shadow: none;
    text-decoration: none;
    overflow: hidden;
}
.amazoness dl {
    margin: 0;
    overflow: hidden;
}
.amazoness dt {
    margin: 0;
    width: 20%;
    display: block;
    float: left;
}
.amazoness dt img {
    text-decoration: none;
    box-shadow: .2em .2em .2em rgba(0,0,0,0.4);
        -moz-box-shadow: .2em .2em .2em rgba(0,0,0,0.4);
        -webkit-box-shadow: .2em .2em .2em rgba(0,0,0,0.4);
        -o-box-shadow: .2em .2em .2em rgba(0,0,0,0.4);
        -ms-box-shadow: .2em .2em .2em rgba(0,0,0,0.4);
    margin-bottom: .4em;
}
.amazoness dd {
    margin: 0;
    margin-left: 3%;
    width: 75%;
    display: block;
    float: left;
}
.amazoness dd ul {
    margin: 0;
}
.amazoness dd ul li {
    margin: 0;
    padding: 0;
    list-style: none;
}
.amazoness dd ul li.amazoness-products-titles {
    font-weight: bold;
    line-height: 1.2;
}
.amazoness dd ul li.amazoness-products-descriptions {
    font-size: 80%;
    line-height: 1.2;
    filter: saturate(0%);
        -webkit-filter: saturate(0%);
}
.amazoness dl:after {
    clear: both;
}
.amazoness .amazoness-link-to-amazons {
    font-size: 80%;
    text-align: right;
    padding-right: 1em;    
}
.amazoness:after {
    clear: both;
}
@media (max-width: 321px) {
    .amazoness dl {
        overflow: visible;
    }
    .amazoness dt,
    .amazoness dd {
        float: none;
        width: 100%;
    }
}
_EOF_;
    const CSS_ADMIN_PAGE = <<< _EOF_
.markdown-body {
    font-size: 1em !important;
    margin-left: 2em;
}
.markdown-body h1,
.markdown-body h2 {
    margin-left: -1em;
}
.markdown-body h3,
.markdown-body h4,
.markdown-body h5,
.markdown-body h6 {
}
.markdown-body pre {
    border-right: 1px solid #999999;
    border-bottom: 1px solid #999999;
}
.markdown-body li {
    list-style-type: circle;
}
_EOF_;
    /*
        Image URL pattern:
            http://images-jp.amazon.com/images/P/%%ASIN%%.09.%%SIZE%%.jpg
        SIZE:
            THUMBZZZ    thumbnail
            TZZZZZZZ    tiny
            MZZZZZZZ    middle
            LZZZZZZZ    large
    */
    const IMAGE_SIZES = array(
        'THUMBZZZ',
        'TZZZZZZZ',
        'MZZZZZZZ',
        'LZZZZZZZ'
    );

    const PLUGIN_NAME = 'Amazoness';
    const PLUGIN_NAME_ASCII = 'amazoness';
    const PLUGIN_SETTING_NAME = 'amazoness_settings';

    const DIR_LANG = 'languages';

    const CACHE_DIR_NAME = '_cache';
    const CACHE_OPTS = array(
        'lifeTime' => 3600,
        'automaticCleaningFactor' => '20',
        'automaticSerialization' => true,
        'hashedDirectoryLevel' => 2
    );

    const MENU_LOCATION = ''; // '' or 'root';

    public function load_gettext_textdomain() {
        load_plugin_textdomain(
            \Netspin\Amazoness::PLUGIN_NAME_ASCII,
            false,
            \Netspin\Amazoness::PLUGIN_NAME_ASCII
                . '/' . \Netspin\Amazoness::DIR_LANG
        );
    }

    // Tiny template engine
    private static function render( $template, array $vars ) {
        return preg_replace_callback(
            '/%%([\w\|]+)%%/',
            function( $m ) use ( $vars ) {
                $name = $m[1];
                $value =
                    ( array_key_exists( $name, $vars ) )
                    ? $vars[ $name ]
                    : ''
                ;
                if ( preg_match( '/^(.*)\|(.*)$/', $name, $matches ) ) {
                    $name = $matches[1];
                    $filter = $matches[2];
                    $value = $vars[ $name ];
                    switch ( $filter ) {
                        case 'html' :
                            $value = htmlspecialchars( $value );
                            break;
                        case 'url' :
                            $value = htmlentities( $value );
                            break;
                    }
                }
                return $value;
            },
            $template
        );
    }

    // Wrapper for wp_remote_get or file_get_contents
    private static function get_HTTP_content( $url ) {
        if ( function_exists( 'wp_remote_get' ) ) {
            $result = wp_remote_get( $url );
            if ( is_wp_error( $result ) ) {
                throw new \Exception(
                    sprintf(
                        __( '%s: Fetching the product\'s page failed - %s' ),
                            basename(__FILE__),
                            $url
                    )
                );
            }
            $content = wp_remote_retrieve_body( $result );
        } else {
            if ( ! $content = file_get_contents( $url ) ) {
                throw new \Exception(
                    sprintf(
                        __( '%s: Fetching the product\'s page failed - %s' ),
                            basename(__FILE__),
                            $url
                    )
                );
            }
        }
        return $content;
    }

    public static function get_HTML (
        $asin,
        $associate_id = '',
        $image_size = ''
    ) {
        // get option values preserved by WordPress
        $options = get_option( self::PLUGIN_SETTING_NAME );
        $associate_id = 
            @$associate_id
            ?: (
                isset( $options['associate_id'] )
                ? $options['associate_id']
                : self::DEFAULT_ASSOCIATE_ID
            )
        ;
        $image_size =
            @$image_size
            ?: (
                isset( $options['image_size'] )
                ? $options['image_size']
                : self::DEFAULT_IMAGE_SIZE
            )
        ;
        if ( !$asin ) {
            throw new \Exception(
                sprintf(
                    __( '%s: ASIN not specified' ),
                        basename(__FILE__)
                )
            );
        }

        // Cache preparation
        $cache_dir = 
            dirname(__FILE__) 
            . '/' 
            . self::CACHE_DIR_NAME
            . '/'
        ;
        $cache_opts = self::CACHE_OPTS;
        $cache_opts['cacheDir'] = $cache_dir;
        // Cache directory availability check
        if ( ! file_exists( $cache_dir ) ) {
            if ( ! mkdir( $cache_dir ) ) {
                throw new \Exception(
                    sprintf(
                        __( '%s: Cannot make directory - %s' ),
                            basename(__FILE__),
                            $cache_dir
                    )
                );
            }
        }
        if ( ! is_dir( $cache_dir ) || ! is_writable( $cache_dir ) ) {
            throw new \Exception(
                sprintf(
                    __( '%s: Insane directory - %s' ),
                        basename(__FILE__),
                        $cache_dir
                )
            );
        }

        // Getting Amazon product page
        $url = self::render(
            self::BASE_URL_PRODUCT,
            array(
                'ASIN' => $asin,
                'ASSOCIATE_ID' => $associate_id
            )
        );
        $url_for_crawl =
            preg_replace(
                '/\?tag=.*$/',
                '',
                $url
            )
        ;
        // Using cache
        $cache = new \Cache_Lite( $cache_opts );
        $id = $asin;
        $S['IS_CACHED'] = 'false';
        $content = '';
        if ( $cached = $cache->get( $id ) ) {
            $gz_content = $cached;
            $content = gzuncompress( $gz_content );
            $S['IS_CACHED'] = 'true';
        } else {
            if ( ! $content = self::get_HTTP_content( $url_for_crawl ) ) {
                throw new \Exception(
                    sprintf(
                        __( '%s: Fetching the product\'s page failed - %s' ),
                            basename(__FILE__),
                            $url_for_crawl
                    )
                );
            }
            $gz_content = gzcompress( $content, 9 );
            $cache->save( $gz_content, $id );
        }

        // Get ready for parsing XPath
        $dom = new \DOMDocument( '1.0', 'UTF-8' );
        $content = mb_convert_encoding(
            $content,
            'HTML-ENTITIES',
            'auto'
        );
        @$dom->loadHTML( $content );
        $xpath = new \DOMXPath( $dom );
        $xpath->registerNamespace( 'php', 'http://php.net/xpath' );
        $xpath->registerPHPFunctions();

        // Parse some information
        /*
            Page Structure:
                #productTitle
                #productDescription
        */
        $S['PRODUCT_TITLE'] =
            html_entity_decode(
                $xpath
                    ->query( '//*[@id="productTitle"]' )
                    ->item(0)
                    ->nodeValue,
                ENT_QUOTES,
                'UTF-8'
            )
        ;
        $S['PRODUCT_DESCRIPTION'] =
            preg_replace(
                '/\s+/',
                ' ',
                strip_tags(
                    html_entity_decode(
                        $xpath
                            ->query( '//*[@id="productDescription"]' )
                            ->item(0)
                            ->nodeValue,
                        ENT_QUOTES,
                        'UTF-8'
                    )
                )
            )
        ;
        $S['PRODUCT_URL'] = $url;
        $S['PRODUCT_IMAGE_URL'] = self::render(
            self::BASE_URL_PRODUCT_IMAGE,
            array(
                'ASIN' => $asin,
                'SIZE' => $image_size
            )
        );

        $html_template =
            isset( $options['html_template'] )
            ? $options['html_template']
            : self::HTML_TEMPLATE
        ;
        // To avoid WordPress AutoWrap problem:
        // $html_template = preg_replace( '/\s+/', ' ', $html_template );
        // Get whole HTML
        $result = self::render(
            $html_template,
            $S
        );

        return $result;
    }

    private static function get_asin_from_url( $url ) {
        $asin = $url;
        $asin = preg_replace(
            '/^https?:\/\/.{1,}\.amazon\..{1,}\/.{0,}(?:dp\/|ASIN\/|ISBN=|ISBN%3D|detail\/-\/[^\/]+\/|detail\/-\/|product-description\/|product\/)([a-zA-Z0-9]+).{0,}$/',
            '$1',
            $url
        );
        if ( !preg_match( '/^[a-zA-Z0-9]+$/', $asin) ) {
            $asin = '';
        }
        return $asin;
    }

    private static function get_shortcode_result( $opts ) {
        $asin = 
            ! empty( $opts['asin'] )
            ? $opts['asin']
            : $opts['content']
        ;
        $argument = trim( $asin );
        if ( preg_match( '/^https?:\/\//', $argument ) ) {
            $asin = self::get_asin_from_url( $argument );
            if ( empty( $asin ) ) {
                return
                    sprintf(
                        __( '%s: Could not capture ASIN from URL - %s' ),
                            basename(__FILE__),
                            $argument
                    )
                ;
            }
        }
        if ( empty( $asin ) ) {
            return
                sprintf(
                    __( '%s: Please specify ASIN' ),
                        basename(__FILE__)
                )
            ;
        }
        return self::get_HTML(
            $asin
            // $associate_id = ''
            // $image_size = '' 
        );
    }

    public static function callback_shortcode( $attrs, $content = '' ) {
        $opts = $attrs;
        $opts['content'] = $content;
        return self::get_shortcode_result( $opts );
    }
    // Call like this in plugin.php:
    // add_shortcode( 'asin', array( 'Netspin\Amazoness', 'callback_shortcode' ) );

    private static function get_additive_to_wp_head() {
        $options = get_option( self::PLUGIN_SETTING_NAME );
        $style =
            isset( $options['css_definition'] )
            ? $options['css_definition']
            : \Netspin\Amazoness::CSS_DEFINITION
        ;
        $string = <<< _EOF_
<style>
{$style}
</style>
_EOF_;
        return $string;
    }

    public static function callback_wp_head() {
        echo self::get_additive_to_wp_head();
    }
    // Call like this in plugin.php:
    // add_action( 'wp_head', array( 'Netspin\Amazoness', 'callback_wp_head' ), 99 );
}
