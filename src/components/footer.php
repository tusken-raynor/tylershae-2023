<footer>
  <div class="content">
    <a href="/" class="big-icon"></a>
    <div class="social-icons"><?php
      $icons = get_field("social_icons", "global_options")?:[];
      foreach ($icons as $icon) { ?>
        <a href="<?= $icon["url"] ?>" target="_blank" class="social-icon <?= $icon['acf_fc_layout'] ?>"><?= $icon['acf_fc_layout'] ?></a><?php
      } ?>
    </div>
    <div id="footer-menu"><?php
      wp_nav_menu(array(
        'depth' => 1,
        'sort_column'	=> 'menu_order',
        'menu' => 'Footer Menu'
      )); ?>
    </div>
  </div>
  <div class="copyright">&copy;TylerShaeDesigns <?= date("Y") ?></div>
</footer>