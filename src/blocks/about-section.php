<?php
if (tylershae_support_example($block)) return;
$block_id = get_field('block_id') ?: 'about-section-' . $block['id'];
$name = get_field('name');
$title = get_field('title');
$content = get_field('content');
$image = get_field('profile_image');
$social_links = get_field('social_links');
block_styles($block_id);

$sizes = '(max-width:1060px) and (min-width:751px)calc((100vw - 751px) / 3.269 + 195px),(max-width:750px)290px,(max-width:402px) and (min-width:300px)calc((100vw - 300px) / 1 + 188px),290px';
?>
<section 
  id="<?= $block_id; ?>" 
  class="tylershae-block about-section-block" 
>
  <div class="inner-wrapper">
    <div class="about-section-image qc"><?php
    use_component('previewed-image', [
      'img' => $image,
      'size_names' => ['about-profile', 'about-profile-2x'],
      'sizes' => $sizes,
      'set_dimensions' => true,
    ]) ?>
    </div>
    <div class="about-section-content">
      <h2><strong><?= $name; ?></strong>&nbsp;&nbsp;|&nbsp;&nbsp;<?= $title; ?></h2>
      <div class="about-section-text">
        <?= $content; ?>
      </div>
    </div>
    <div class="social-links"><?php
      foreach ($social_links as $link) { ?>
        <a href="<?= $link['url']; ?>" class="social-icon <?= $link['acf_fc_layout'] ?>" target="_blank" rel="noopener noreferrer">
          <?= $link['acf_fc_layout'] ?>
        </a><?php
      } ?>
    </div>
  </div>
</section>