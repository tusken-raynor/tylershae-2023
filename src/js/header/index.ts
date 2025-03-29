import utils from "../utils";

export default (context: Document | Element = document) => {
  const menuBurger = context.querySelector<HTMLElement>("header .burger");
  if (menuBurger) {
    menuBurger.addEventListener("click", () => {
      menuBurger.classList.toggle("open");
    });
    context.querySelectorAll<HTMLElement>("header .exit-menu, header .mobile-shadow").forEach((btn) => {
      btn.addEventListener("click", () => {
        menuBurger.classList.remove("open");
      });
    });
    
  }
  // Make top level menu items expand children on first click
  // and go to the link on second click
  const allMenuItems = context.querySelectorAll<HTMLElement>(
    "#menu-main-menu li > a"
  );
  allMenuItems.forEach((item) => {
    if (!item.parentElement!.classList.contains("menu-item-has-children"))
      return;
    item.addEventListener("click", (e) => {
      if (item.classList.contains("active")) {
        return;
      } else {
        e.preventDefault();
        // Grab all the ancestors of the clicked item that also need the active class
        const itemsToActivate = utils.getMatchingAncestors(item, ".menu-item-has-children").map(x => x.firstElementChild! as HTMLElement);
        // Get all other menus that are open to close them
        const activeItems = Array.from(document.querySelectorAll<HTMLElement>("#menu-main-menu .menu-item-has-children > a.active"));
        // Make a collection of all the items that need to be
        // closed, which are the active items, excluding ancestors
        const itemsToClose = activeItems.filter((x) => !itemsToActivate.includes(x));
        // Close other menus, including doing the height transition
        itemsToClose.forEach((x) => {
          if (innerWidth <= 850) {
            const cont = x.nextElementSibling! as HTMLElement;
            cont.style.height = getChildrenHeight(cont) + "px";
            requestAnimationFrame(() => {
              cont.style.height = "";
            });
          }
          x.classList.remove("active");
        });
        // Open the menu, do the height transition
        itemsToActivate.forEach((x) => {
          if (!x.classList.contains("active")) {
            if (innerWidth <= 850) {
              const cont = x.nextElementSibling! as HTMLElement;
              cont.style.height = getChildrenHeight(cont) + "px";
              setTimeout(() => {
                cont.style.height = "";
              }, 200);
            }
            x.classList.add("active");
          }
        });
      }
    });
  });
  // Remove the init class from the header full-size-ref
  const headerFullSizeRef = context.querySelector<HTMLElement>("header .full-size-ref");
  if (headerFullSizeRef) {
    setTimeout(() => {
      headerFullSizeRef.classList.remove("init");
    }, 700);
  }
};

function getChildrenHeight(el: HTMLElement) {
  let height = 0;
  el.childNodes.forEach((node) => {
    if (node instanceof HTMLElement) {
      height += node.offsetHeight;
    }
  });
  return height;
}
