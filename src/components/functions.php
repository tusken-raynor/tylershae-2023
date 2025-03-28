<?php 

$emct_tracker = [
  'header' => 0,
  'footer' => 0,
  'svg-filters' => 0,
];

function use_component($name, $props = [], $slot = null) {
  global $emct_tracker;
  if (isset($emct_tracker[$name]) && $emct_tracker[$name] == 1) {
    $emct_tracker[$name] += 1;
    return;
  }
  $path = get_template_directory() . '/components/' . $name . '.php';
  if (file_exists($path)) {
    include($path);
  }
}
