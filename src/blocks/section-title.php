<?php
if (tylershae_support_example($block)) return;
$block_id = get_field('block_id') ?: 'section-title-' . $block['id'];
$title = get_field('title');
$tag = get_field('tag');
$url = get_field('url');
block_styles($block_id);
?>
<section 
  id="<?= $block_id; ?>" 
  class="tylershae-block section-title-block container" 
><?php
  if ($url) { ?>
    <a href="<?= $url ?>" class="section-title-link"><?php
  }
  $el = new FlexibleElement($tag, [ 'class' => 'section-title' ]);
  $el->out($title);
  if ($url) { ?>
    </a><?php
  } ?>
</section>