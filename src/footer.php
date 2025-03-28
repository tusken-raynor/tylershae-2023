    <footer> <?php
      use_component('footer'); ?>
    </footer><?php 
    wp_footer();
    if ($GLOBALS['generated_style']) { ?>
      <style><?= $GLOBALS['generated_style'] ?></style> <?php
    }
    if ($GLOBALS['fetched_rse_idex']) { ?>
      <script 
        type="text/javascript"
        src="<?= get_template_directory_uri() ?>/scripts/script_refer-validate.php?idex=<?= $GLOBALS['fetched_rse_idex']?>"
      ></script><?php
    }
    use_component("svg-filters"); ?>
  </body>
</html>