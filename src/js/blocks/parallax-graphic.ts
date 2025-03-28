import ScrollMagic from "scrollmagic";

const sceneMap = new Map<HTMLElement, ScrollMagic.Scene>();

export default (context: Document | Element = document) => {
  var controller = new ScrollMagic.Controller();
  const observer = new ResizeObserver((entries) => {
    for (let i = 0; i < entries.length; i++) {
      const entry = entries[i];
      const trigger = entry.target as HTMLElement;
      const scene = sceneMap.get(trigger)!;
      scene.duration(trigger.offsetHeight);
    }
  });
  context.querySelectorAll(".parallax-graphic-block").forEach((block) => {
    const ps = Number(block.getAttribute("data-parallax-scale") || "3");
    const trigger = block.querySelector<HTMLElement>(".trigger")!;
    const images = block.querySelectorAll<HTMLElement>(".floatie");
    const scene = new ScrollMagic.Scene({
      triggerElement: trigger,
      triggerHook: 1,
      duration: trigger.offsetHeight,
    })
      .on("progress", (e) => {
        const progress: number = e.progress * ps - (1 / 3) * ps;
        images.forEach((image) => {
          image.style.transform = `translate(-50%, ${progress * -100}%)`;
          image.style.top = `${progress * 100}%`;
        });
      })
      .addTo(controller);
    sceneMap.set(trigger, scene);
    observer.observe(trigger);
  });
};
