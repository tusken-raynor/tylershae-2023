<?php
if (tylershae_support_example($block)) return;
$block_id = get_field('block_id') ?: 'parallax-graphic-' . $block['id'];
$image = get_field('image');
$alignment = get_field('alignment');
$count = intval(get_field('count')?:1);
$count = $count < 1 ? 1 : $count;
$scale = get_field('scale')?:3;
$scale = $scale < 1 ? 1 : $scale;
block_styles($block_id);
?>
<section 
  id="<?= $block_id; ?>" 
  class="tylershae-block parallax-graphic-block <?= $alignment ?>" 
  data-parallax-scale="<?= $scale ?>"
>
  <div class="images"><?php 
    for ($i = 0; $i < $count; $i++) { 
      $width = 100 - ($count - $i - 1) * 3;
      build_img($image, null, '', ['class' => $i == $count - 1 ? 'sizer' : 'floatie', 'style' => 'width: '.$width.'%;']); 
    } ?>
    <div class="trigger"></div>
  </div>
</section>