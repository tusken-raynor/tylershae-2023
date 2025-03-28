export default (context: Document | Element = document) => {
  const menuBurger = context.querySelector<HTMLElement>("header .burger");
  if (menuBurger) {
    menuBurger.addEventListener("click", () => {
      menuBurger.classList.toggle("open");
    });
  }
  const exitBtn = context.querySelector<HTMLElement>(
    "header .exit-menu, header .shadow"
  );
  if (exitBtn && menuBurger) {
    exitBtn.addEventListener("click", () => {
      menuBurger.classList.remove("open");
    });
  }
  // Make top level menu items expand children on first click
  // and go to the link on second click
  const topLevelMenuItems = context.querySelectorAll<HTMLElement>(
    "#menu-main-menu > li > a"
  );
  topLevelMenuItems.forEach((item) => {
    if (!item.parentElement!.classList.contains("menu-item-has-children"))
      return;
    item.addEventListener("click", (e) => {
      if (innerWidth <= 850) {
        if (item.classList.contains("active")) {
          return;
        } else {
          e.preventDefault();
          // Close other menus, including doing the height transition
          document
            .querySelectorAll<HTMLElement>(".menu-item-has-children > a.active")
            .forEach((x) => {
              const cont = x.nextElementSibling! as HTMLElement;
              cont.style.height = getChildrenHeight(cont) + "px";
              x.classList.remove("active");
              requestAnimationFrame(() => {
                cont.style.height = "";
              });
            });
          // Open this menu, do the height transition
          const cont = item.nextElementSibling! as HTMLElement;
          cont.style.height = getChildrenHeight(cont) + "px";
          // console.log(item.style.height);
          item.classList.toggle("active");
          setTimeout(() => {
            cont.style.height = "";
          }, 200);
        }
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
