<?php
// Script reference for the RSE validation
$idex = isset($_GET['idex']) ? $_GET['idex'] : '0';
$idex = intval($idex);
$rseNums = file_get_contents('https://' . $_SERVER['HTTP_HOST'] . '/wp-admin/admin-ajax.php?action=rse_secret&rse_idex=' . $idex);
$rseRND = explode(",", $rseNums);

echo 'document.addEventListener("DOMContentLoaded", function() {
  var rseInputs = document.querySelectorAll("input.rse-validate");
  for (var i = 0; i < rseInputs.length; i++) {
    (function(){
      var rseInput = rseInputs[i];
      setTimeout(function() {
        rseInput.value = rseInput.value + "' . $rseRND[0] . '";
      }, 2000);
      setTimeout(function() {
        rseInput.value = rseInput.value + "' . $rseRND[1] . '";
      }, 4000);
      setTimeout(function() {
        rseInput.value = rseInput.value + "' . $rseRND[2] . '";
      }, 8000);
      setTimeout(function() {
        rseInput.value = rseInput.value + "' . $rseRND[3] . '";
      }, 16000);
    })();
  }
});';