<?php

$img = $props['img'] ?? 0;
if (!$img) return;

$size_names = $props['size_names'] ?? null; 
$sizes = $props['sizes'] ?? ""; 
$attrs = $props['attrs'] ?? null; 
$set_dimensions = $props['set_dimensions'] ?? false;
$classes = $props['wrapper_classes'] ?? '';
if (is_array($classes)) {
  $classes = implode(' ', $classes);
}
if ($classes) {
  $classes = ' ' . $classes;
}

$preview_url = get_image_url($img, 'previewed-image');

?>
<div class="previewed-image<?= $classes ?>" style="--preview: url(<?= $preview_url ?>)"><?php
  build_img($img, $size_names, $sizes, $attrs, $set_dimensions); ?>
</div>