<?php

/*
 * Init necessary things for site
 */
$GLOBALS['used_h1'] = false;
$GLOBALS['generated_style'] = is_admin_bar_showing() ? "html{margin-top:0!important;}" : "";

$GLOBALS['fetched_rse_id'] = false;
$GLOBALS['fetched_rse_idex'] = false;

$GLOBALS['requested_popups'] = [];

function tylershae_site_init() {
  register_nav_menus( array(
    'main'  => 'Main Menu',
    'footer'  => 'Footer Menu',
  ));

  add_image_size('open-graph', 600, 600, true);
  add_image_size('image-pair-img', 478, 318, true);
  add_image_size('image-pair-img-2x', 956, 636, true);
  add_image_size('image-collection-single', 972, 329, true);
  add_image_size('image-collection-single-2x', 1944, 658, true);
  add_image_size('image-collection-pair', 480, 498, true);
  add_image_size('image-collection-pair-2x', 960, 996, true);
  add_image_size('image-collection-triad', 316, 211, true);
  add_image_size('image-collection-triad-2x', 632, 422, true);
  add_image_size('image-collection-infinite', 972, 0, false);
  add_image_size('image-collection-infinite-2x', 1944, 0, false);
  add_image_size('about-profile', 290, 374, true);
  add_image_size('about-profile-2x', 580, 748, true);
  add_image_size('previewed-image', 32, 0, false);
  add_image_size('original-format', 0, 0, false);

  remove_image_size('1536x1536');
  remove_image_size('2048x2048');
  remove_image_size('medium');
  remove_image_size('medium_large');
  remove_image_size('large');

  add_theme_support('post-thumbnails');
}
add_action('init', 'tylershae_site_init');

if (!isset($content_width)) {
  $content_width = 1920;
}


/* in case of custom Theme in Functions.php */
add_action('after_theme_switch', 'mytheme_setup');
function mytheme_setup () {
  flush_rewrite_rules();
}

function my_on_switch_theme($new_theme) {
  // Grab the global var for accessing the database
  global $wpdb;
  // Get the current theme
  $current_theme = wp_get_theme();
  // Get the current theme name
  $current_theme_name = $current_theme->get('Name');
  // Log the current theme name
  if ($current_theme_name == 'TylerShae 2023 Theme') {
    // Check and see if the banned submission IP's table exists
    $table_name = 'tsp_banned_submission_ips';
    // Get the query results
    $results = $wpdb->get_results("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$table_name';");
    if (!count($results)) {
      error_log("Table creation started: $table_name");
      // Create the table
      $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ip varchar(255) NOT NULL,
        attempts int(11) NOT NULL,
        time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        UNIQUE KEY id (id)
      );";
      $wpdb->query($sql);
    }
    // Check and see if the rse secrets table exists
    $table_name = 'tsp_rse_secrets';
    // Get the query results
    $results = $wpdb->get_results("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$table_name';");
    if (!count($results)) {
      error_log("Table creation started: $table_name");
      // Create the table
      $sql = "CREATE TABLE $table_name (
        `id` int(6) NOT NULL,
        `idex` int(6) NOT NULL,
        `secret` varchar(255) NOT NULL,
        UNIQUE KEY id (id)
      );";
      $wpdb->query($sql);
      // Generate 12 rows using random numbers
      // The id is a number of that is 6 digits long
      // the secret is four random integers separated by a comma
      for ($i = 0; $i < 12; $i++) {
        $id = rand(100000, 999999);
        $idex = rand(10000000, 99999999);
        $secret = rand(0, 2147483647) . ',' . rand(0, 2147483647) . ',' . rand(0, 2147483647) . ',' . rand(0, 2147483647);
        $sql = "INSERT INTO $table_name (id, idex, secret) VALUES ($id, $idex, '$secret');";
        $wpdb->query($sql);
      }
    }
  }
}
add_action('init', 'my_on_switch_theme');

/*
 * Queue up stylesheet and scripts. Everything bundled into two files via webpack.
 */
function webpack_scripts() {
  if (!is_admin()) {
    wp_deregister_script('wp-embed');
  }
  wp_enqueue_style( 'webpack-css', get_stylesheet_directory_uri() . '/css/main.css', array(), null);
  wp_enqueue_script( 'webpack-js', get_template_directory_uri() . '/main.js', array(), null, true );
  // If we are in dev mode, auto insert a script that allows us to generate sizes string for images
  $SIZES_MOD = false;
  if ($SIZES_MOD) {
    wp_add_inline_script( 'webpack-js', $SIZES_MOD );
  }
  wp_localize_script( 'webpack-js', 'fcWp', array(
    'templateUrl' => get_template_directory_uri(),
    'sitePopupData' => load_popup_data()
	) );
}
add_action('wp_enqueue_scripts', 'webpack_scripts');

/*
 * Queue up block editor stylesheet and scripts for editors. Everything bundled into two files via webpack.
 */
function editor_webpack_scripts() {
  wp_enqueue_style( 'editor-css', get_stylesheet_directory_uri() . '/css/editor.css', array(), null);
  wp_enqueue_script( 'editor-js', get_template_directory_uri() . '/editor.js', array(), null, true );
}
add_action('admin_init', 'editor_webpack_scripts');


// these next actions/filters are for customizing the login page
function tylershae_login_stylesheet() {
  wp_enqueue_style( 'custom-login', get_template_directory_uri() . '/css/login/login.css' );
}
add_action( 'login_enqueue_scripts', 'tylershae_login_stylesheet' );

function tylershae_login_logo_url() {
  return home_url();
}
add_filter( 'login_headerurl', 'tylershae_login_logo_url' );

function tylershae_login_logo_url_title() {
  return 'tylershae';
}
add_filter( 'login_headertitle', 'tylershae_login_logo_url_title' );


/**
 * Used for saving the ACF settings to json files. This will work in the build system
 * And will be replaced with the appropriate folder based on gulp's settings.
 **/
function webpack_acf_json_save_point( $orig_path ) {
  $path = $orig_path; // ACF PATH SETTING
  return $path;
}
add_filter('acf/settings/save_json', 'webpack_acf_json_save_point');

// determines whether ACF menu should show on the Wordpress admin screens.
// Defaults to true but gulp will replace it to return false so it will be
// hidden on the production build
add_filter("acf/settings/show_admin", "__return_true");

if (function_exists('acf_add_options_page') && current_user_can('manage_options')) {
  acf_add_options_page(array(
    'page_title' => 'Site Settings',
    'post_id' => 'global_options',
    'icon_url' => 'dashicons-admin-site-alt3'
  ));
}

function remove_wp_nav_menu_ul_litags($menu){
  return $menu = strip_tags($menu, "<a>");
}
// Uncomment the following line to change menu to just hyper links
// add_filter( 'wp_nav_menu', 'remove_wp_nav_menu_ul_litags' );

/**
 * Disable the emoji's
 */
function disable_emojis() {
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
  add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action( 'init', 'disable_emojis' );

/**
 * Filter function used to remove the tinymce emoji plugin.
 * 
 * @param array $plugins 
 * @return array Difference betwen the two arrays
 */
function disable_emojis_tinymce( $plugins ) {
  if ( is_array( $plugins ) ) {
    return array_diff( $plugins, array('wpemoji') );
  } else {
    return array();
  }
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param array $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array Difference betwen the two arrays.
 */
function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
  if ( 'dns-prefetch' == $relation_type ) {
    /** This filter is documented in wp-includes/formatting.php */
    $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
    $urls = array_diff( $urls, array($emoji_svg_url) );
  }
  return $urls;
}

// used to get the best fit file size based on size passed in
function get_image_url($img, $size) {
  if (is_numeric($img)) {
    $img = wp_get_attachment_image_src($img, $size);
    if ($img) {
      return $img[0];
    }
  }

  if ($img) {
    if (array_key_exists('sizes', $img) && array_key_exists($size, $img['sizes'])) {
      return $img['sizes'][$size];
    } else {
      return $img['url'];
    }
  }
  return '';
}

// build the open graph meta tags for the header
function open_graph_meta() {
  $image_id = get_post_thumbnail_id()?:get_theme_mod('open_graph_media');
  $title = is_front_page() ? get_bloginfo('name') : get_the_title(); ?>
  <meta property="og:title" content="<?= $title ?>" /><?php
  if ($image_id) { ?>
    <meta property="og:image" content="<?= get_image_url($image_id, "open-graph") ?>" /><?php
  }
}

function tylershae_register_open_graph_customizer_setting($wp_customize) {
  $wp_customize->add_setting('open_graph_media', array(
    'default' => 0, 
  ));

  $wp_customize->add_control(new WP_Customize_Media_Control(
    $wp_customize,
    'open_graph_media_control',
    array(
      'label'      => 'Open Graph Media',
      'description' => 'Set the default image which renders when a link to this website is shared. Image should be 600 pixels wide.',
      'priority'    => 10,
      'mime_type'   => 'image',
      'settings'    => 'open_graph_media',
      'section'     => 'title_tagline',
      'button_labels' => array( 'select' => 'Select Image' )
    )
  ));
}
add_action('customize_register', 'tylershae_register_open_graph_customizer_setting');

// When access is requested check the email for .gov or .mil and auto create a user if so
add_action('frm_after_create_entry', 'request_access_process_email', 30, 2);
function request_access_process_email($entry_id, $form_id){
  if ($form_id == 2){ 
    $email = $_POST['item_meta'][8];
    if (str_ends_with($email, ".mil") || str_ends_with($email, '.gov')) {
      // It ends with the correct extension, so create a new user
      $password = wp_generate_password(14, true, true);
      $user_id = wp_create_user($email, $password, $email);
      if (is_int($user_id)) {
        wp_new_user_notification($user_id);
      }
    }
  }
}

function vimeo_link($url) {
  if (preg_match('/vimeo\.com(\/video)?\/([0-9]{1,10})/i', $url, $match)) {
    return 'https://player.vimeo.com/video/' . $match[2] . '?autoplay=1';
  }
  return FALSE;
}

function youtube_link($url) {
  if (preg_match('%(?:youtube\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
    return 'https://www.youtube.com/embed/' . $match[1] . '?rel=0&wmode=transparent&modestbranding=1&autoplay=1';
  }
  return FALSE;
}

/** Massages Youtube and Vimeo links so they work for embedding. */
function video_link($url) {
  $vimeo = vimeo_link($url);
  if ($vimeo) {
    return $vimeo;
  }
  return youtube_link($url);
}

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        $length = strlen($needle);
        return substr($haystack, 0, $length) === $needle;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }
}

// wordpress now has a max image size, defaults to 2560, going to set it to 4000 instead
function tylershae_expand_image_threshold($size, $imagesize, $file, $attachment_id) {
  return 4000;
}
add_filter('big_image_size_threshold', 'tylershae_expand_image_threshold', 10, 4);
function tylershae_expand_srcset_threshold($size, $size_array) {
  return 4000;
}
add_filter('max_srcset_image_width', 'tylershae_expand_srcset_threshold', 10, 2);

function etd() {
  // shorthand echos the template directory to save my fingers
  echo get_template_directory_uri();
}

function console_log_string(...$arguments) {
  return '<script>console.log('.implode(",", array_map('json_encode', $arguments)).');</script>';
}

function console_log(...$arguments) {
  echo '<script>console.log('.implode(",", array_map('json_encode', $arguments)).');</script>';
}

// The following two filter hooks wrap groups of core blocks inside of divs
// This first filter hook wraps any block that is a core wordpress block
// inside a div. A comment is placed after the closing tag so that we can
// identify if one core block comes right after another
add_filter('render_block', 'wrap_core_blocks', 10, 2);
function wrap_core_blocks($block_content, $block) {
  if (substr($block['blockName'], 0, 5) == 'core/') {
    $block_content = '<section class="block-core">' . $block_content . '</section><!-- CBDJ -->';
  }
  return $block_content;
}

// This second filter finds every instance where a core block wrapper opens
// immedietely after another core block wrapper closes and joins the two
// wrappers into one. This makes sure that any consecutive core blocks will
// get grouped together in one wrapper
add_filter('the_content', 'consolidate_core_block_wrappers', 10, 2);
function consolidate_core_block_wrappers($content) {
  $content = preg_replace('/<\/section>\s*<!-- CBDJ -->\s*<section class="block-core">/', '', $content);
  return preg_replace('/<!-- CBDJ -->/', '', $content);
}

function high_dpi_media_query() {
  return "@media (-webkit-min-device-pixel-ratio: 1.2),
  (min--moz-device-pixel-ratio: 1.2),
  (-o-min-device-pixel-ratio: 1.2/1),
  (min-device-pixel-ratio: 1.2),
  (min-resolution: 115dpi),
  (min-resolution: 1.2dppx)";
}

function style_string($styles, $selector = '', $media_query = '') {
  if (!$styles) {
    return '';
  }
  $string = '';
  foreach ($styles as $property => $value) {
    $string .= $property.':'.$value.';';
  }
  if ($selector) {
    if ($media_query) {
      return $media_query.'{'.$selector.'{'.$string.'}}';
    }
    return $selector.'{'.$string.'}';
  }
  return $string;
}

function add_style($style_string) {
  $GLOBALS['generated_style'] .= trim($style_string);
}

function custom_generate_webp_versions($metadata, $attachment_id) {
  $upload_dir = wp_upload_dir();
  $file_path = get_attached_file($attachment_id);
  
  if (!file_exists($file_path)) {
    return $metadata;
  }
  
  $mime_type = wp_check_filetype($file_path)['type'];
  if (!in_array($mime_type, ['image/jpeg', 'image/png'])) {
    return $metadata; // Skip non-JPG/PNG images
  }
  
  $webp_file_path = preg_replace('/\.(jpe?g|png)$/', '.webp', $file_path);
  if (!file_exists($webp_file_path)) {
    $image = wp_get_image_editor($file_path);
    if (!is_wp_error($image)) {
      $image->set_quality(80);
      $image->save($webp_file_path, 'image/webp');
    }
  }

  if (file_exists($webp_file_path)) {
    // Update the post mime_type to image/webp
    wp_update_post([
      'ID' => $attachment_id,
      'post_mime_type' => 'image/webp'
    ]);
  }

  $size_names = get_intermediate_image_sizes();
  $size_names[] = 'full';
  $size_names = array_diff($size_names, ['original-format']);
  
  // Convert resized versions to WebP only
  foreach ($metadata['sizes'] as $size => $size_data) {
    // Remove this size name from the names array
    $size_names = array_diff($size_names, [$size]);

    $resized_file = $upload_dir['path'] . '/' . $size_data['file'];
    $webp_resized_file = preg_replace('/\.(jpe?g|png)$/', '.webp', $resized_file);
    
    if (!file_exists($webp_resized_file)) {
      $image = wp_get_image_editor($resized_file);
      if (!is_wp_error($image)) {
        $image->set_quality(80);
        $image->save($webp_resized_file, 'image/webp');
      }
    }
    
    // Replace metadata entry with WebP version
    if (file_exists($webp_resized_file)) {
      $metadata['sizes'][$size]['file'] = basename($webp_resized_file);
      $metadata['sizes'][$size]['mime-type'] = 'image/webp';

      // Delete the non-WebP version
      @unlink($resized_file);
    }
  }
  
  // Use the remaining size names to set the WebP version of the full size
  foreach ($size_names as $name) {
    $metadata['sizes'][$name] = [
      'file' => basename($webp_file_path),
      'width' => $metadata['width'],
      'height' => $metadata['height'],
      'mime-type' => 'image/webp'
    ];
  }
  // Set the original format to the original (Non-WebP) file
  $metadata['sizes']['original-format'] = [
    'file' => basename($file_path),
    'width' => $metadata['width'],
    'height' => $metadata['height'],
    'mime-type' => $mime_type
  ];

  // Update the file path to the WebP version
  $metadata['file'] = str_replace(basename($file_path), basename($webp_file_path), $metadata['file']);
  
  return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'custom_generate_webp_versions', 10, 2);

// Prevent WordPress from saving JPG/PNG resized versions
function remove_unnecessary_image_formats($sizes) {
    return $sizes; // Resized images will be handled manually in metadata function
}
add_filter('intermediate_image_sizes_advanced', 'remove_unnecessary_image_formats');

class FlexibleElement {
  public $opening_tag = "";
  public $closing_tag = "";

  public function __construct($tag, $attributes = null, $styles = null) {
    // Keep track of h1 tag usage internally, change to h2 if an h1 is known of
    if ($tag == 'h1') {
      if ($GLOBALS['used_h1']) {
        $tag = 'h2';
      } else {
        $GLOBALS['used_h1'] = true;
      }
    }
    $string = '<'.$tag.' ';
    // Set the style attribute using the styles argument
    // Styles argument will overwrite any styles property in the attributes argument
    if ($styles) {
      if (!$attributes) {
        $attributes = array();
      }
      $attributes['style'] = style_string($styles);
    }
    // Create the element attributes now
    if ($attributes) {
      foreach ($attributes as $name => $value) {
        // The class attribute might be an array so turn it to a string
        if ($name == 'class' && is_array($value)) {
          $value = implode(' ', $value);
        }
        if ($value !== null) {
          $string .= $name.'="'.$value.'" ';
        }
      }
    }
    // Build the correct closing tab depeneding on what type of element
    $closing_tag = '</'.$tag.'>';
    if (in_array($tag, array('img','object','br','input','hr','link','meta'))) {
      $closing_tag = "";
    }
    $string .= $closing_tag ? '>' : '/>';

    $this->opening_tag = $string;
    $this->closing_tag = $closing_tag;
  }

  public function out($innerHTML = "") {
    echo $this->opening_tag.$innerHTML.$this->closing_tag;
  }
}

class FlexibleElementScoped extends FlexibleElement {

  public function __construct($scope, $tag, $attributes = null, $styles = null) {
    if (isset($scope['hash']) && $scope['mode'] == 'class') {
      if (!$attributes) {
        $attributes = array();
      }
      if (!isset($attributes['class']) || !$attributes['class']) {
        $attributes['class'] = array();
      } else if (gettype($attributes['class']) == 'string') {
        $attributes['class'] = array($attributes['class']);
      }
      $attributes['class'][] = "cls".$scope['hash'];
    } else {
      if (!$attributes) {
        $attributes = array();
      }
      $hash = isset($scope['hash']) ? $scope['hash'] : $scope;
      $attributes['data-'.$hash] = "";
    }
    
    parent::__construct($tag, $attributes, $styles);
  }
}

function build_img($img, $size_names = null, $sizes = "", $attrs = null, $set_dimension_attributes = false) {
  if (!$img) {
    return;
  }
  $src = "";
  $alt = "";
  $width = 0;
  $height = 0;
  $srcset = array();
  $name_vals = array_values($size_names?:[]);
  if (is_numeric($img)) {
    $alt = get_post_meta($img, '_wp_attachment_image_alt', true);
    if ($size_names != null && !str_contains(get_post_mime_type($img), 'image/svg')) {
      $srcdata = wp_get_attachment_image_src($img, $name_vals[0]);
      if ($srcdata) {
        $src = $srcdata[0];
        $width = $srcdata[1];
        $height = $srcdata[2];
      }
      foreach ($size_names as $key => $size) {
        $srcdata = wp_get_attachment_image_src($img, $size);
        $met = is_int($key) ? $srcdata[1].'w' : $key;
        $srcset[] = $srcdata[0].' '.$met;
      }
      if (!$src && !$srcset) {
        return;
      }
    } else {
      $srcdata = wp_get_attachment_image_src($img, 'full');
      if (!$srcdata) {
        return;
      }
      $src = $srcdata[0];
      $width = $srcdata[1];
      $height = $srcdata[2];
    }
  } else {
    $alt = $img['alt'];
    if ($size_names != null && !str_contains($img['mime_type'], 'image/svg')) {
      if (array_key_exists($name_vals[0], $img['sizes'])) {
        $src = $img['sizes'][$name_vals[0]];
        $width = $img['sizes'][$name_vals[0].'-width'];
        $height = $img['sizes'][$name_vals[0].'-height'];
      } else {
        $src = $img['url'];
        $width = $img['width'];
        $height = $img['height'];
      }
      foreach ($size_names as $key => $size) {
        $url = array_key_exists($size, $img['sizes']) ? $img['sizes'][$size] : $img['url'];
        $met = is_int($key) ? $img['sizes'][$size.'-width'].'w' : $key;
        $srcset[] = get_webp_image_url($url).' '.$met;
      }
    } else {
      $src = $img['url'];
      $width = $img['width'];
      $height = $img['height'];
    }
  }
  $attrs = $attrs?:array();
  if ($src) {
    $attrs['src'] = get_webp_image_url($src);
  }
  // Make sure there is always an alt attribute even if empty
  if (!isset($attrs['alt'])) {
    $attrs['alt'] = $alt?:"";
  }
  if ($srcset) {
    $attrs['srcset'] = implode(",",$srcset);
  }
  if ($sizes) {
    $attrs['sizes'] = $sizes;
  }
  if ($set_dimension_attributes && $width) {
    $attrs['width'] = $width;
  }
  if ($set_dimension_attributes && $height) {
    $attrs['height'] = $height;
  }
  if (!array_key_exists('loading', $attrs)) {
    $attrs['loading'] = 'lazy';
  }
  // Check if the image has a webp version if its a png
  // if (
  //   function_exists('webp_uploads_filter_image_copy') && 
  //   $size_names && 
  //   !array_diff($size_names, $GLOBALS['webp_sizes']) && 
  //   str_ends_with($src, ".png")
  // ) {
  //   // Use a picture element when calling on webp so that safari users can still see their precious png
  //   echo '<picture>';
  //   // Rework the image attribute data so that it follows picture tag rules and has webp version
  //   $trimmed_attrs = array_filter($attrs, function($k) { return in_array($k, ['srcset', 'sizes']); }, ARRAY_FILTER_USE_KEY);
  //   unset($attrs['srcset']);
  //   unset($attrs['sizes']);
  //   $png = new FlexibleElement('source', array_merge($trimmed_attrs, ['type' => 'image/png']));
  //   $trimmed_attrs['srcset'] = str_replace('.png', '.webp', $trimmed_attrs['srcset']);
  //   $webp = new FlexibleElement('source', array_merge($trimmed_attrs, ['type' => 'image/webp']));
  //   // Now spit out the elements
  //   echo $webp->opening_tag;
  //   echo $png->opening_tag;
  //   echo (new FlexibleElement('img', $attrs))->opening_tag;
  //   echo '</picture>';
  // } else {
  //   echo (new FlexibleElement('img', $attrs))->opening_tag;
  // }
  echo (new FlexibleElement('img', $attrs))->opening_tag;
}

function get_webp_image_url($image_url) {
  // Get the image path on the server
  $webp_url = $image_url . '.webp';
  $upload_dir = wp_get_upload_dir(); // Get the uploads directory
  $image_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $webp_url);
  
  // Check if the WebP file exists
  if (file_exists($image_path)) {
      // Replace the extension in the URL
      return $webp_url;
  }

  // Return original image URL if no WebP version exists
  return $image_url;
}

function build_query_string($array) {
  if (!$array) {
    return '';
  }
  $qstring = http_build_query($array);
  if ($qstring) {
    return '?'.$qstring;
  }
  return '';
}

function load_popup_data() {
  if (isset($_GET['popup']) && $_GET['popup']) {
    $GLOBALS['requested_popups'][] = $_GET['popup'];
  }
  $popups = get_field('site_popups', 'global_options')?:[];
  $data = [];
  foreach ($popups as $popup) {
    $do_popup = (($popup['always_available'] || in_array($popup['name'], $GLOBALS['requested_popups'])) && $popup['title']);
    if (!$do_popup) continue;
    $name = $popup['name'];
    $popup['_type'] = $popup['acf_fc_layout'];
    unset($popup['name']);
    unset($popup['always_available']);
    unset($popup['acf_fc_layout']);
    $data[$name] = $popup;
  }
  return $data;
}

// Wrap each individual word in a span so that we can style them individually within the flexbox context
function remove_path_from_current_menu_links_with_hashes($items) {
  $items = preg_replace_callback('/(<a[^>]*>)(.+?)(<\/a>)/', fn ($matches) => $matches[1].'<span>'.implode('</span>&nbsp;<span>', explode(' ', $matches[2])).'</span>'.$matches[3], $items);
  return $items;
}
add_filter('wp_nav_menu_items', 'remove_path_from_current_menu_links_with_hashes', 10, 1);

function fox_form_hookup__contact() {
  error_log('SUBMIT IP\'s '.$_SERVER['REMOTE_ADDR']);
  check_ajax_referer('ffh_nonce', 'ffh_nonce');
  // Check if the honeypot field is filled out
  if (!isset($_POST['ffh_nonce']) || isset($_POST['frm_verify'.$_POST['ffh_nonce']]) && $_POST['frm_verify'.$_POST['ffh_nonce']] != '') {
    wp_die();
  }
	
  global $wpdb;
  // Check the ip address of the submitter. See if they passed the limit
  $ip = $_SERVER['REMOTE_ADDR'];
  $ip_limit = get_field('std_submit_limit', 'global_options') ?: 3;
  $ip_permanent_limit = get_field('perm_submit_limit', 'global_options') ?: 5;

  $table_name = 'tsp_banned_submission_ips';
  $row = $wpdb->get_row("SELECT `id`, `attempts`, `time` FROM $table_name WHERE ip = '$ip'", ARRAY_A);
  if ($row) {
    if ($row['attempts'] >= $ip_permanent_limit) {
      wp_die();
    }
    if ($row['attempts'] >= $ip_limit) {
      // Only increment the attempts if its the less than 24 hours since the timestamp
      // If more than 24 hours, reset the attempts to 1 and update the timestamp
      $time = new DateTime($row['time']);
      $now = new DateTime();
      $diff = $now->diff($time);
      if ($diff->days > 0) {
        $wpdb->get_var("UPDATE $table_name SET attempts = 1, time = NOW() WHERE id = ".$row['id']);
      } else {
        // Keep counting up towards the permanent limit
        $wpdb->get_var("UPDATE $table_name SET attempts = attempts + 1 WHERE id = ".$row['id']);
        wp_die();
      }
    } else {
      $wpdb->get_var("UPDATE $table_name SET attempts = attempts + 1 WHERE id = ".$row['id']);
    }
  } else {
    $wpdb->get_var("INSERT INTO $table_name (ip, attempts) VALUES ('$ip', 1)");
  }

  // Loop through the params of the $_POST and check if any of them are
  // the rse field. If so, check if the value is correct. If not, die.
  $rse_key = "";
  foreach ($_POST as $key => $value) {
    if (str_starts_with($key, 'rse-validate_')) {
      $rse_key = $key;
      break;
    }
  }
  // Do an early out if the key is not found or the value is empty
  if (!$rse_key || $_POST[$rse_key] == '') {
    error_log('SUBMIT IP\'s '.$_SERVER['REMOTE_ADDR'].'\n:::\n _RSE: '.(isset($_POST[$rse_key]) ? $_POST[$rse_key] : 'NoParam'));
    wp_die();
  }
  // Parse the id and compare the long number to the secret
  $rse_id = str_replace('rse-validate_', '', $rse_key);
  $rse_secret = $wpdb->get_var($wpdb->prepare("SELECT secret FROM tsp_rse_secrets WHERE id = %d", $rse_id));

  if (str_replace(',', '', $rse_secret) != $_POST[$rse_key]) {
    error_log('SUBMIT IP\'s '.$_SERVER['REMOTE_ADDR'].'\n:::\n RSE: '.$_POST[$rse_key]);
    wp_die();
  }

  // Get the form data
  $first_name = isset($_POST['first-name']) ? strip_tags($_POST['first-name']) : '';
  $last_name = isset($_POST['last-name']) ? strip_tags($_POST['last-name']) : '';
  $email = isset($_POST['email']) ? strip_tags($_POST['email']) : '';
  $subject = isset($_POST['subject']) ? strip_tags($_POST['subject']) : '';
  $message = isset($_POST['message']) ? strip_tags($_POST['message']) : '';
  // Verify it. All fields are required
  if (!$first_name || !$last_name || !$email || !$subject || !$message) {
    wp_die();
  }
  error_log('SUBMIT IP\'s '.$_SERVER['REMOTE_ADDR']);

  // Now create a new data object with extra fields to post
  // to the form on fox cottage
  $data = array(
    'item_meta[6]' => $first_name,
    'item_meta[7]' => $last_name,
    'item_meta[8]' => $email,
    'item_meta[9]' => $subject,
    'item_meta[10]' => $message,
    'frm_action' => 'create',
    'form_id' => '2',
    'frm_hide_fields_2' => '',
    'form_key' => 'contact-form2',
    'item_meta[0]' => '',
    'frm_submit_entry_2' => 'af1694fe51',
    '_wp_http_referer' => '/ffh-contact-tylershae-about/',
    'item_key' => '',
  );

  $url = 'https://foxcottagegoods.com/ffh-contact-tylershae-about/?ffh_priv_param';

  $options = array(
      'http' => array(
          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
          'method'  => 'POST',
          'content' => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) { 
    echo 'failed';
  } else {
    if (isset($_REQUEST['redirect'])) {
      wp_redirect($_REQUEST['redirect']);
      exit();
    } else {
      echo 'success';
    }
  }
  
  wp_die();
}
add_action('wp_ajax_ffh_contact', 'fox_form_hookup__contact');
add_action('wp_ajax_nopriv_ffh_contact', 'fox_form_hookup__contact');


function retrieve_rse_secret() {
  global $wpdb;
  $table_name = 'tsp_rse_secrets';

  $rse_idex = isset($_GET['rse_idex']) ? $_GET['rse_idex'] : '';
  if (!$rse_idex) {
    wp_die();
  }
  
  // Grab the secret from the database
  // Make sure to sanitize the $rse_id using prepare
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE idex = %d", intval($rse_idex)), ARRAY_A);
  if (!$row) {
    wp_die();
  }

  echo $row['secret'];
  
  wp_die();
}
add_action('wp_ajax_rse_secret', 'retrieve_rse_secret');
add_action('wp_ajax_nopriv_rse_secret', 'retrieve_rse_secret');

function get_rse_id() {
  if ($GLOBALS['fetched_rse_id']) {
    return $GLOBALS['fetched_rse_id'];
  }
  global $wpdb;
  $table_name = 'tsp_rse_secrets';
  // Grab the secret from the database
  $row = $wpdb->get_row("SELECT id, idex FROM $table_name ORDER BY RAND() LIMIT 1", ARRAY_A);
  if ($row) {
    $GLOBALS['fetched_rse_id'] = $row['id'];
    $GLOBALS['fetched_rse_idex'] = $row['idex'];
    return $row['id'];
  }
  return '0';
}

function tsp_regenerate_images_to_webp() {
  global $wpdb;

  // Query all image attachments that are JPEG or PNG
  $bare_statement = "
      SELECT ID, post_mime_type 
      FROM {$wpdb->posts} 
      WHERE post_type = 'attachment' 
      AND post_mime_type IN ('image/jpeg', 'image/png')
  ";
  $attachments = $wpdb->get_results($bare_statement . (isset($_REQUEST['limit']) ? ' LIMIT ' . intval($_REQUEST['limit']) : ''));

  if (!$attachments) {
      echo "No JPEG or PNG images found.";
      return;
  }

  require_once ABSPATH . 'wp-admin/includes/image.php';

  $count = 0;

  foreach ($attachments as $attachment) {
    echo '<pre>';
      $attachment_id = $attachment->ID;
      $file_path = get_attached_file($attachment_id);
      $metadata = wp_get_attachment_metadata($attachment_id);
      
      if (!$file_path || !file_exists($file_path) || empty($metadata)) {
        // If the file doesn't exist, remove the attachment post
        wp_delete_attachment($attachment_id, true);
        continue;
      }

      $upload_dir = [
        'path' => dirname($file_path),
      ];
      $original_ext = pathinfo($file_path, PATHINFO_EXTENSION);

      // Generate WebP version of the original image
      $webp_file_path = preg_replace('/\.' . $original_ext . '$/', '.webp', $file_path);
      if (!file_exists($webp_file_path)) {
          $image_editor = wp_get_image_editor($file_path);
          if (!is_wp_error($image_editor)) {
              $image_editor->set_quality(80);
              $image_editor->save($webp_file_path, 'image/webp');
          }
      }
      if (file_exists($webp_file_path)) {
        // Update the post mime_type to image/webp
        wp_update_post([
          'ID' => $attachment_id,
          'post_mime_type' => 'image/webp'
        ]);
      }

      $size_names = get_intermediate_image_sizes();
      $size_names[] = 'full';
      $size_names = array_diff($size_names, ['original-format']);
      
      // Convert resized versions to WebP only
      if (!empty($metadata['sizes'])) {
        foreach ($metadata['sizes'] as $size => $size_data) {
          // Remove this size name from the names array
          $size_names = array_diff($size_names, [$size]);
      
          $resized_file = $upload_dir['path'] . '/' . $size_data['file'];
          if ($resized_file == $file_path) {
            continue;
          }
          $webp_resized_file = preg_replace('/\.(jpe?g|png)$/', '.webp', $resized_file);
          
          if (!file_exists($webp_resized_file)) {
            $image = wp_get_image_editor($resized_file);
            if (!is_wp_error($image)) {
              $image->set_quality(80);
              $image->save($webp_resized_file, 'image/webp');
            } else {
              echo "Error: " . $image->get_error_message() . " : " . $resized_file . "\n";
            }
          }
          
          // Replace metadata entry with WebP version
          if (file_exists($webp_resized_file)) {
            $metadata['sizes'][$size]['file'] = basename($webp_resized_file);
            $metadata['sizes'][$size]['mime-type'] = 'image/webp';
      
            // Delete the non-WebP version
            @unlink($resized_file);
          }
        }
      }
      
      // Use the remaining size names to set the WebP version of the full size
      foreach ($size_names as $name) {
        $metadata['sizes'][$name] = [
          'file' => basename($webp_file_path),
          'width' => $metadata['width'],
          'height' => $metadata['height'],
          'mime-type' => 'image/webp'
        ];
      }
      // Set the original format to the original (Non-WebP) file
      $metadata['sizes']['original-format'] = [
        'file' => basename($file_path),
        'width' => $metadata['width'],
        'height' => $metadata['height'],
        'mime-type' => $attachment->post_mime_type
      ];

      // Update attachment metadata
      $metadata['file'] = str_replace(basename($metadata['file']), basename($webp_file_path), $metadata['file']);
      wp_update_attachment_metadata($attachment_id, $metadata);

      echo "Processed: " . $metadata['file'] . " with ID: " . $attachment->ID . " -> Converted to WebP\n";
      $count++;
  }
  // Get the total number of images left to convert
  $count_statement = str_replace('ID, post_mime_type', 'COUNT(ID)', $bare_statement);
  $attachment_count = $wpdb->get_var($count_statement);

  echo "</pre>";

  if (isset($_REQUEST['limit'])) {
    echo "Done converting " . $count . " images to WebP.";
    echo "<br>";
    echo "Remaining images to convert: " . $attachment_count;
    // Create a form to submit the next batch of images ?>
    <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="get">
      <input type="hidden" name="action" value="to_webp" />
      <input type="hidden" name="limit" value="<?= intval($_REQUEST['limit']) ?>" /><?php
      if (isset($_REQUEST['auto'])) { ?>
        <input type="hidden" name="auto" value="1" /><?php
      } ?>
      <input type="submit" value="Convert Next <?= intval($_REQUEST['limit']) ?> Images" />
    </form><?php
    if (isset($_REQUEST['auto'])) {
      // Automatically submit the form after 2 seconds
      echo '<script>setTimeout(function(){document.querySelector("form").submit();}, 2000);</script>';
    }
  }
}
add_action('wp_ajax_to_webp', 'tsp_regenerate_images_to_webp');

function tsp_realign_attachment_mime_types() {
  global $wpdb;

  $rec_statement = "
    SELECT p.ID, pm.meta_value AS file_path
    FROM {$wpdb->posts} p
    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE p.post_mime_type = 'image/webp'
    AND pm.meta_key = '_wp_attached_file'
    AND (pm.meta_value LIKE '%.jpeg' OR pm.meta_value LIKE '%.jpg' OR pm.meta_value LIKE '%.png')
    ";
  $attachments = $wpdb->get_results($rec_statement);
  // These are attatchments that need to be sent through the conversion process again
  // because they are not webp files. Loop through the results and set their mime_type
  // back to the original format so they can be caught by the conversion process
  foreach ($attachments as $attachment) {
    $ext = pathinfo($attachment->file_path, PATHINFO_EXTENSION);
    wp_update_post([
      'ID' => $attachment->ID,
      'post_mime_type' => 'image/' . ( $ext == 'png' ? 'png' : 'jpeg' )
    ]);
    // Get the metadata for the attachment
    $metadata = wp_get_attachment_metadata($attachment->ID);
    // Change the extensions of the file and sizes to match the new mime type
    $metadata['file'] = preg_replace('/\.webp$/', '.' . $ext, $metadata['file']);
    foreach ($metadata['sizes'] as $size => $size_data) {
      $metadata['sizes'][$size]['file'] = preg_replace('/\.webp$/', '.' . $ext, $size_data['file']);
    }
    // Update the metadata
    wp_update_attachment_metadata($attachment->ID, $metadata);
  }
  echo "Re-aligned " . count($attachments) . " attachment mime types.<br>";
}
add_action('wp_ajax_realign_attachment_mime_types', 'tsp_realign_attachment_mime_types');


require_once(dirname(__FILE__).'/blocks/functions.php');

require_once(dirname(__FILE__).'/components/functions.php');

