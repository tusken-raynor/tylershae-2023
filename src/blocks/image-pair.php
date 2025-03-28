<?php
if (tylershae_support_example($block)) return;
$block_id = get_field('block_id') ?: 'image-pair-' . $block['id'];
$images = get_field('images');
block_styles($block_id);
?>
<section 
  id="<?= $block_id; ?>" 
  class="tylershae-block image-pair-block container" 
><?php
  foreach ($images as $image) { 
    $sizes = "(max-width:1004px) and (min-width:601px)calc((100vw - 600px) / 2 + 276px),(max-width:600px)478px,(max-width:510px) and (min-width:301px)calc((100vw - 301px) / 1 + 269px),478px";

    $wrap = new FlexibleElement($image['url']?'a':'div', ['class' => 'image-pair-image', 'href' => $image['url']?:null]);
    echo $wrap->opening_tag;
    use_component('previewed-image', [
      'img' => $image['image'],
      'size_names' => ['image-pair-img', 'image-pair-img-2x'],
      'sizes' => $sizes,
      'set_dimensions' => true,
    ]);
    if ($image['hover_image']) {
      use_component('previewed-image', [
        'img' => $image['hover_image'],
        'size_names' => ['image-pair-img', 'image-pair-img-2x'],
        'sizes' => $sizes,
        'set_dimensions' => true,
        'wrapper_classes' => 'hover',
      ]);
    }
    echo $wrap->closing_tag;
  } ?>
</section>