<header>
  <div class="full-size-ref observe-intersection init"></div>
  <div class="container-wrapper qc" data-offset="1">
    <div class="container-full">
      <div class="menu">
        <a class="logo" href="/"></a>
        <div class="burger">
          <div class="pattie"></div>
          <div class="pattie"></div>
          <div class="pattie"></div>
        </div>
        <nav id="main-menu">
          <a class="logo-mobile" href="/"></a><?php
          wp_nav_menu(array(
            'depth' => 4,
            'sort_column'	=> 'menu_order',
            'menu' => 'Main Menu'
          )); ?>
          <a 
            href="<?= get_field("contact_button_url", "global_options")?:'#' ?>" 
            class="contact mobile"
          ><?= get_field("contact_button_label", "global_options")?:'Contact' ?></a>
          <div class="exit-menu"></div>
        </nav>
        <div class="mobile-shadow"></div>
      </div>
      <a href="/" class="title-logo">Tyler Shae</a>
      <div class="contact-wrapper">
        <a 
          href="<?= get_field("contact_button_url", "global_options")?:'#' ?>" 
          class="contact"
        ><span><?= get_field("contact_button_label", "global_options")?:'Contact' ?></span></a>
      </div>
    </div>
  </div>
</header>