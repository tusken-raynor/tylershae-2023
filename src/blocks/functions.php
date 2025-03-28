<?php

/**
 * This file includes the custom blocks that are used on the site.
 */

// register the Custom block category
function tylershae_plugin_block_categories($categories, $post) {
  $post_types = array('page', 'projects');
  if (!in_array($post->post_type, $post_types)) {
    return $categories;
  }
  return array_merge(
      $categories,
      array(
          array(
              'slug' => 'tylershae-category',
              'title' => 'tylershae BLOCKS',
          ),
      )
  );
}
add_filter('block_categories', 'tylershae_plugin_block_categories', 10, 2);

function tylershae_add_acf_custom_blocks() {
  acf_register_block_type(array(
    'name' => 'tylershae-block-section-title',
    'title' => 'Section Title',
    'description' => 'A simple section title block with vertical pipes at each end.',
    'render_template' => plugin_dir_path(__FILE__) . '/section-title.php',
    'category' => 'tylershae-category',
    'align' => 'full',
    'supports' => array(
      'align' => false,
      // only enable if it should appear once on a page (usually not what you want)
      // 'multiple' => false,
    ),
    'icon' => 'heading',
    'example' => [
      'attributes' => [
        'mode' => 'preview',
        'data' => ['example_image' => 'example-section-title.jpg'],
      ]
    ]
  ));
  acf_register_block_type(array(
    'name' => 'tylershae-block-image-pair',
    'title' => 'Image Pair',
    'description' => 'Pair of images in a row. Hover images can be added.',
    'render_template' => plugin_dir_path(__FILE__) . '/image-pair.php',
    'category' => 'tylershae-category',
    'align' => 'full',
    'supports' => array(
      'align' => false,
      // only enable if it should appear once on a page (usually not what you want)
      // 'multiple' => false,
    ),
    'icon' => 'format-gallery',
    'example' => [
      'attributes' => [
        'mode' => 'preview',
        'data' => ['example_image' => 'example-image-pair.jpg'],
      ]
    ]
  ));
  acf_register_block_type(array(
    'name' => 'tylershae-block-image-collection',
    'title' => 'Image Collection',
    'description' => 'A collection of images. Each row can contain one to three images. Any number of rows can be added. Hover images can be added.',
    'render_template' => plugin_dir_path(__FILE__) . '/image-collection.php',
    'category' => 'tylershae-category',
    'align' => 'full',
    'supports' => array(
      'align' => false,
      // only enable if it should appear once on a page (usually not what you want)
      // 'multiple' => false,
    ),
    'icon' => 'grid-view',
    'example' => [
      'attributes' => [
        'mode' => 'preview',
        'data' => ['example_image' => 'example-image-collection.jpg'],
      ]
    ]
  ));
  acf_register_block_type(array(
    'name' => 'tylershae-block-about-section',
    'title' => 'About Section',
    'description' => 'A section with a title, subtitle, and text on the right side, and an image on the left.',
    'render_template' => plugin_dir_path(__FILE__) . '/about-section.php',
    'category' => 'tylershae-category',
    'align' => 'full',
    'supports' => array(
      'align' => false,
    ),
    'icon' => 'id-alt',
    'example' => [
      'attributes' => [
        'mode' => 'preview',
        'data' => ['example_image' => 'example-about-section.jpg'],
      ]
    ]
  ));
  acf_register_block_type(array(
    'name' => 'tylershae-block-image-placer',
    'title' => 'Image Placer',
    'description' => 'Place an arbirary amount of images.',
    'render_template' => plugin_dir_path(__FILE__) . '/image-placer.php',
    'category' => 'tylershae-category',
    'align' => 'full',
    'supports' => array(
      'align' => false,
    ),
    'icon' => 'format-image',
  ));
  acf_register_block_type(array(
    'name' => 'tylershae-block-parallax-graphic',
    'title' => 'Parallax Graphic',
    'description' => 'Add an image that creates a parallax effect with duplicates of the image behind it.',
    'render_template' => plugin_dir_path(__FILE__) . '/parallax-graphic.php',
    'category' => 'tylershae-category',
    'align' => 'full',
    'supports' => array(
      'align' => false,
    ),
    'icon' => 'images-alt',
    'example' => [
      'attributes' => [
        'mode' => 'preview',
        'data' => ['example_image' => 'example-parallax-graphic.jpg'],
      ],
    ]
  ));
  acf_register_block_type(array(
    'name' => 'tylershae-block-contact-us',
    'title' => 'Contact Us',
    'description' => 'A contact us form.',
    'render_template' => plugin_dir_path(__FILE__) . '/contact-us.php',
    'category' => 'tylershae-category',
    'align' => 'full',
    'supports' => array(
      'align' => false,
      // only enable if it should appear once on a page (usually not what you want)
      'multiple' => false,
    ),
    'icon' => 'clipboard',
    'example' => [
      'attributes' => [
        'mode' => 'preview',
        'data' => ['example_image' => 'example-contact-us.jpg'],
      ],
    ]
  ));
  acf_register_block_type(array(
    'name' => 'tylershae-block-link-row',
    'title' => 'Link Row',
    'description' => 'A row of orange links, that are always centered.',
    'render_template' => plugin_dir_path(__FILE__) . '/link-row.php',
    'category' => 'tylershae-category',
    'align' => 'full',
    'supports' => array(
      'align' => false,
    ),
    'icon' => 'editor-aligncenter',
    'example' => [
      'attributes' => [
        'mode' => 'preview',
        'data' => ['example_image' => 'example-link-row.jpg'],
      ],
    ]
  ));
}

add_action('acf/init', 'tylershae_add_acf_custom_blocks');

function tylershae_support_example($block) {
  if ($block['mode'] != 'preview' || !isset($block['data']['example_image'])) {
    return false;
  } ?>
  <img 
    src="<?= get_template_directory_uri() . '/img/blocks/' . $block['data']['example_image'] ?>" 
    alt="Example Image" 
    style="margin: 0 auto; display: block; width: 100%; height: auto;" 
  /><?php
  return true;
}

function block_styles($block_id) {
  $margins = get_field('block_margins');
  $padding = get_field('block_padding');
  $styles = [];
  if ($margins) {
    foreach ($margins as $prop => $value) {
      if ($value['value']) {
        $styles[$prop] = $value['value'].$value['unit'];
      }
    }
  }
  if ($padding) {
    foreach ($padding as $prop => $value) {
      if ($value['value']) {
        $styles[$prop] = $value['value'].$value['unit'];
      }
    }
  }
  if (count($styles)) {
    add_style(style_string($styles, "#".$block_id));
  }
}
