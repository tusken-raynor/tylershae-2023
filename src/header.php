<!DOCTYPE html><?php
$GLOBALS['block_count'] = 0;
$stylestring = ""; ?>
<html>
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" /><?php
    open_graph_meta();
    $site_title = get_bloginfo('name');
    $title = is_front_page() ? $site_title . ' &raquo; ' . get_bloginfo('description') : get_the_title() . ' - ' . $site_title; ?>
    <title><?= $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="<?= get_the_excerpt()?:get_field('meta_content', 'global_options') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
    <script type="text/javascript">
      var templateUrl = '<?= site_url(); ?>';
    </script>
    <?php wp_head(); ?>
  </head>
  <body <?php body_class('no-menu-touch bg-style-'.get_field('page_bg_style')); ?>>
    <?php use_component('header'); ?>
