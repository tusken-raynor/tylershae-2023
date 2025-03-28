<?php
if (tylershae_support_example($block)) return;
$block_id = get_field('block_id') ?: 'image-placer-' . $block['id'];
$images = get_field('images');
block_styles($block_id);

?>
<section 
  id="<?= $block_id; ?>" 
  class="tylershae-block image-placer-block" 
><?php
  foreach ($images as $image) { ?>
    <div class="image"><?php 
      $size_names = null;
      $sizes = $image['sizes'] ?: '';

      if ($image['size_names']) {
        $size_names = array_values(array_filter(explode(' ', $image['size_names'])));
      }

      build_img($image['image']['id'], $size_names, $sizes, null, true); ?>
    </div><?php
  } ?>
</section>