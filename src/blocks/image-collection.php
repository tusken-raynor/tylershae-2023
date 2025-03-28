<?php
if (tylershae_support_example($block)) return;
$block_id = get_field('block_id') ?: 'image-collection-' . $block['id'];
$image_rows = get_field('image_rows')?:[];
$mb = get_field('mobile_break');
$mnp = get_field('mobile_no_pad');
block_styles($block_id);

$sizes = [
  'pair' => '(min-width:1005px)480px,(max-width:1004px)47.8088vw,(max-width:600px)49vw'.($mb ? ',(max-width:400px)200vw' : '' ),
  'triad' => '(min-width:1005px)316px,(max-width:1004px)31.4741vw,(max-width:600px)32vw'.($mb ? ',(max-width:400px)200vw' : '' ),
];
?>
<section 
  id="<?= $block_id; ?>" 
  class="tylershae-block image-collection-block container<?= ($mb ? ' mb' : '').($mnp ? ' mnp' : '') ?>" 
><?php
  foreach ($image_rows as $row) { 
    if ($row['acf_fc_layout'] == 'single' || $row['acf_fc_layout'] == 'infinite') {
      // Create the element for the image wrapper and such
      $srcset = [
        'image-collection-' . $row['acf_fc_layout'],
        'image-collection-' . $row['acf_fc_layout'] . '-2x',
      ];
      $wrap = new FlexibleElement($row['url']?'a':'div', ['class' => ['row', 'wrapper', $row['acf_fc_layout']], 'href' => $row['url']?:null]);
      echo $wrap->opening_tag;
      use_component('previewed-image', [
        'img' => $row['image'],
        'size_names' => $srcset,
        'sizes' => '(max-width:972px)100vw,972px',
        'set_dimensions' => true,
        'attrs' => ['class' => 'cls'.$SCOPE_HASH]
      ]);
      if ($row['hover_image']) {
        use_component('previewed-image', [
          'img' => $row['hover_image'],
          'size_names' => $srcset,
          'sizes' => '(max-width:972px)100vw,972px',
          'set_dimensions' => true,
          'attrs' => ['class' => 'cls'.$SCOPE_HASH],
          'wrapper_classes' => ['hover']
        ]);
      }
      echo $wrap->closing_tag;
      continue;
    } ?>
    <div class="row <?= $row['acf_fc_layout'] ?>"><?php
      foreach ($row['images'] as $image) {
        $srcset = ['1x' => 'image-collection-'.$row['acf_fc_layout'], '1.5x' => 'image-collection-'.$row['acf_fc_layout'].'-2x'];
        $wrap = new FlexibleElement($image['url']?'a':'div', ['class' => 'wrapper', 'href' => $image['url']?:null]);
        echo $wrap->opening_tag;
        use_component('previewed-image', [
          'img' => $image['image'],
          'size_names' => $srcset,
          'sizes' => $sizes[$row['acf_fc_layout']],
          'set_dimensions' => true,
          'attrs' => ['class' => 'cls'.$SCOPE_HASH]
        ]);
        if ($image['hover_image']) {
          use_component('previewed-image', [
            'img' => $image['hover_image'],
            'size_names' => $srcset,
            'sizes' => $sizes[$row['acf_fc_layout']],
            'set_dimensions' => true,
            'attrs' => ['class' => 'cls'.$SCOPE_HASH],
            'wrapper_classes' => ['hover']
          ]);
        }
        echo $wrap->closing_tag;
      } ?>
    </div><?php
  } ?>
</section>