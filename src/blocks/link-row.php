<?php
if (tylershae_support_example($block)) return;
$block_id = get_field('block_id') ?: 'link-row-' . $block['id'];
$links = get_field('links')?:[];
$mobile_links = get_field('mobile_links')?:[];
$hide_desktop = get_field('hide_desktop');
$switch_width = get_field('switch_width')?:'600';
$scroll_vector = intval(get_field('scroll_vector')?:'0');
$add_jumper = $scroll_vector ? get_field('add_jumper') : false;
block_styles($block_id);
?>
<section 
  id="<?= $block_id; ?>" 
  class="tylershae-block link-row-block" 
  <?= $scroll_vector ? ' data-sv="'.$scroll_vector.'"'.($add_jumper ? ' data-jumper' : '') : '' ?>
><?php
  if ($add_jumper) { ?>
    <div class="jumper-platform"><div class="jumper"></div></div><?php
  } ?>
  <div class="link-wrap"><?php
    foreach ($links as $link) { ?>
      <a href="<?= $link['url'] ?>" class="link" data-sig="<?= sanitize_title($link['label']) ?>"><?= $link['label'] ?></a><?php
    }
    if (count($mobile_links) > 0) {
      foreach ($mobile_links as $link) { ?>
        <a href="<?= $link['url'] ?>" class="link mobile"><?= $link['label'] ?></a><?php
      }

      $style_string = '
        @media (max-width: '.$switch_width.'px) {
          #'.$block_id.' .mobile {
            display: block;
          }
        }
      ';
      add_style($style_string);
    }
    if ($hide_desktop) {
      $style_string = '
        @media (max-width: '.$switch_width.'px) {
          #'.$block_id.' .link:not(.mobile) {
            display: none;
          }
          #'.$block_id.' .link-wrap {
            padding-left: 16px;
            padding-right: 16px;
            flex-wrap: wrap;
            gap: 24px;
          }
        }
      ';
      add_style($style_string);
    } ?>
  </div>
</section>