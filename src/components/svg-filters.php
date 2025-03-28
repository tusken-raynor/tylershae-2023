<svg style="display:none">
  <filter id="button_glitch" width="128" height="128" x="0" y="0" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
    <feImage width="128" height="128" href="<?= get_template_directory_uri() ?>/img/glitch-displace-1.jpg" result="DIMG" />
    <feDisplacementMap
      in="SourceGraphic"
      in2="DIMG"
      scale="90"
      xChannelSelector="R"
      yChannelSelector="G" />
  </filter>
  <filter id="button_glitch_neg" width="128" height="128" x="0" y="0" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
    <feImage width="128" height="128" href="<?= get_template_directory_uri() ?>/img/glitch-displace-1.jpg" result="DIMG" />
    <feDisplacementMap
      in="SourceGraphic"
      in2="DIMG"
      scale="-90"
      xChannelSelector="R"
      yChannelSelector="G" />
  </filter>
  <filter id="button_glitch_sml" width="128" height="128" x="0" y="0" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
    <feImage width="128" height="128" href="<?= get_template_directory_uri() ?>/img/glitch-displace-1.jpg" result="DIMG" />
    <feDisplacementMap
      in="SourceGraphic"
      in2="DIMG"
      scale="5"
      xChannelSelector="R"
      yChannelSelector="G" />
  </filter>
  <filter id="std_glitch" width="64" height="64" x="0" y="0" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
    <feImage width="64" height="64" href="<?= get_template_directory_uri() ?>/img/glitch-displace-1.jpg" result="DIMG" />
    <feDisplacementMap
      in="SourceGraphic"
      in2="DIMG"
      scale="12"
      xChannelSelector="R"
      yChannelSelector="G" />
  </filter>
  <filter id="std_glitch_neg" width="64" height="64" x="0" y="0" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
    <feImage width="64" height="64" href="<?= get_template_directory_uri() ?>/img/glitch-displace-1.jpg" result="DIMG" />
    <feDisplacementMap
      in="SourceGraphic"
      in2="DIMG"
      scale="-12"
      xChannelSelector="R"
      yChannelSelector="G" />
  </filter>
</svg>