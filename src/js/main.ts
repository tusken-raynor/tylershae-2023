import "../css/main.scss";
import blocks from "./blocks";
import header from "./header";
import imgFix from "./img-fix";
import intersections from "./intersections";
import utils from "./utils";
import containerQueries from "./utils/container-queries";

document.addEventListener("DOMContentLoaded", (e) => {
  // Run code for the front end
  imgFix();
  header();
  blocks();
  containerQueries();
  intersections();
});

// Determine if the device is a touch device
(window as any).isTouch = utils.isTouchDevice4();
if ((window as any).isTouch) {
  document.querySelector("html")!.classList.add("is-touch");
}
