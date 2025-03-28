export default () => {
  const FULLRATIO = 0.99;

  const observables = Array.from(
    document.querySelectorAll(".observe-intersection,.observe-intersection-once")
  );

  const customRatios = observables
    .map((el) => Number(el.getAttribute("data-ratio")) || 0)
    .filter((r) => r);

  const observer = new IntersectionObserver(
    (entries) => {
      for (let i = 0; i < entries.length; i++) {
        const entry = entries[i];
        if (entry.isIntersecting) {
          if (!entry.target.classList.contains("viewed")) {
            entry.target.classList.add("viewed");
            // If the element is set to only be observed once, disconnect the observer
            if (entry.target.classList.contains("observe-intersection-once")) {
              observer.unobserve(entry.target);
              continue;
            }
          }
          entry.target.classList.add("in-view");
          if (entry.intersectionRatio >= FULLRATIO) {
            if (!entry.target.classList.contains("fully-viewed")) {
              entry.target.classList.add("fully-viewed");
            }
            entry.target.classList.add("fully-in-view");
          } else {
            entry.target.classList.remove("fully-in-view");
          }
        } else {
          entry.target.classList.remove("in-view");
          entry.target.classList.remove("fully-in-view");
        }
        // Now apply any custom ratio classes to their proper target
        const customRatio = entry.target.getAttribute("data-ratio");
        if (customRatio) {
          const numCustomRatio = Number(customRatio);
          const customRatioFormat = customRatio.replace(".", "-");
          if (
            entry.isIntersecting &&
            entry.intersectionRatio >= numCustomRatio
          ) {
            entry.target.classList.add("in-view-" + customRatioFormat);
            if (
              !entry.target.classList.contains("viewed-" + customRatioFormat)
            ) {
              entry.target.classList.add("viewed-" + customRatioFormat);
            }
          } else if (entry.intersectionRatio < numCustomRatio) {
            entry.target.classList.remove("in-view-" + customRatioFormat);
          }
        }
      }
    },
    {
      threshold: [0, FULLRATIO].concat(customRatios),
    }
  );
  observables.forEach((el) => {
    observer.observe(el);
  });
};
