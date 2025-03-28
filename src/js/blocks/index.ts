import contactUs from "./contact-us";
import linkRow from "./link-row";
import parallaxGraphic from "./parallax-graphic";

export default (context: Document | Element = document) => {
  parallaxGraphic(context);
  contactUs(context);
  linkRow(context);
};
