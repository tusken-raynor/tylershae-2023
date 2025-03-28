const JUMP_SPEED_FAC = 1769.275;
const JUMP_MARGIN_FAC = 0.35897435;
const TEMPLATE_URL: string = (window as any).fcWp.templateUrl;
import utils from "../utils";

export default (context: Document | Element = document) => {
  const reducedMotion =
    (window as any).matchMedia(`(prefers-reduced-motion: reduce)`) === true ||
    window.matchMedia(`(prefers-reduced-motion: reduce)`).matches === true;
  if (reducedMotion) {
    // If the user prefers reduced motion, don't do the link row animation
    return;
  }
  context
    .querySelectorAll<HTMLElement>(".link-row-block[data-sv]")
    .forEach((block) => {
      const wrap = block.querySelector<HTMLElement>(".link-wrap")!;
      const jumperPlatform =
        block.querySelector<HTMLElement>(".jumper-platform")!;
      const jumperEl = block.querySelector<HTMLElement>(".jumper")!;
      const jumpFrames = [1, 2, 3, 4, 5, 6, 7, 8, 9].map(
        (x) => `${TEMPLATE_URL}/img/stickman-jumping-f${x}.jpg`
      );
      const jumpPlayer = imageSequence(jumpFrames);
      const vector = parseInt(block.dataset.sv!);
      const speed = Math.abs(vector);
      const jumper = block.hasAttribute("data-jumper");
      let frameCount = 0;
      let fullRevWidth = getFullRevWidth(wrap);
      let widthEnough = wrapWidthEnough(wrap);
      let currLinkGap = getCurrLinkGap(wrap);
      let jump = false;
      let jumping = false;
      let moved = 0;
      onFrameUpdate((secs) => {
        if ((frameCount & 15) == 0) {
          // Update these numbers every 16 frames
          fullRevWidth = getFullRevWidth(wrap);
          widthEnough = wrapWidthEnough(wrap);
          if (jumper) {
            currLinkGap = getCurrLinkGap(wrap);
          }
        }
        if (jumper && (frameCount & 3) == 0) {
          // Calculate the if we are at the edge of a gap every 4 frames
          // For jumping
          jump = isNearGap(
            wrap,
            vector > 0,
            currLinkGap * JUMP_MARGIN_FAC + 10
          );
          if (jumperPlatform) {
            jumperPlatform.style.animationDuration = `${
              (currLinkGap / speed) * JUMP_SPEED_FAC
            }ms`;
          }
        }
        if (widthEnough) {
          // If the wrap is wider than double the screen, move it
          moved = (moved + secs * vector) % fullRevWidth;
          // If the vector is positive, move the wrap one full
          // revolution to the left
          if (vector > 0) {
            wrap.style.transform = `translateX(${moved - fullRevWidth}px)`;
          } else {
            wrap.style.transform = `translateX(${moved}px)`;
          }
          if (jumper) {
            if (!jumping && jump) {
              jumping = true;
              block.classList.add("jump");
              jumpPlayer.play((img) => {
                if (jumperEl.children.length) {
                  jumperEl.firstChild!.replaceWith(img);
                } else {
                  jumperEl.appendChild(img);
                }
              });
              setTimeout(() => {
                block.classList.remove("jump");
                jumperEl.removeChild(jumperEl.firstChild!);
              }, (currLinkGap / speed) * JUMP_SPEED_FAC);
            } else if (jumping && !jump) {
              jumping = false;
            }
          }
          if (block.hasAttribute("data-idfsv")) {
            block.removeAttribute("data-idfsv");
          }
        } else {
          // If the wrap is not wide enough, reset the transform
          // And do the default display
          if (!block.hasAttribute("data-idfsv")) {
            wrap.style.transform = "";
            block.setAttribute("data-idfsv", "");
          }
        }
        frameCount++;
      });
    });
};

function onFrameUpdate(callback: (elapsed: number) => void, since = 0) {
  requestAnimationFrame((elapsed) => {
    // Return the elapsed time in seconds since the last frame
    callback((elapsed - since) / 1000);
    onFrameUpdate(callback, elapsed);
  });
}

function wrapWidthEnough(wrap: HTMLElement) {
  // If the wrap with is less than double the screen, return false
  if (wrap.offsetWidth < window.innerWidth * 2) {
    return false;
  }
  return true;
}

function getFullRevWidth(wrap: HTMLElement) {
  // Get the distance between the start of the first link and
  // the next link that has the same sig data attribute to
  // calculate the full revolution width
  const first = wrap.querySelector<HTMLElement>(".link[data-sig]");
  if (!first) return 0;
  const next = wrap.querySelectorAll<HTMLElement>(
    `.link[data-sig="${first.dataset.sig}"]`
  )[1];
  if (!next) return 0;
  return next.offsetLeft - first.offsetLeft;
}

function getCurrLinkGap(wrap: HTMLElement) {
  // Get this distance in pixels between the end of the first
  // link and the start of the second link
  const first = wrap.querySelector<HTMLElement>(".link");
  if (!first) return 0;
  const second = wrap.querySelectorAll<HTMLElement>(".link")[1];
  if (!second) return 0;
  return second.offsetLeft - first.offsetLeft - first.offsetWidth;
}

function isNearGap(wrap: HTMLElement, positive = true, error = 0) {
  // Check each link and see if the end of the link is within
  // the error range of the middle of the viewport
  const links = wrap.querySelectorAll<HTMLElement>(".link");
  const mid = window.innerWidth / 2;
  for (let i = 0; i < links.length; i++) {
    const link = links[i];
    const rect = link.getBoundingClientRect();
    const end = positive ? rect.left : rect.right;
    if (end > mid - error && end < mid + error) {
      return true;
    }
  }
  return false;
}

// Download an image using a promise
function downloadImage(url: string) {
  return new Promise<HTMLImageElement>((resolve, reject) => {
    const img = new Image();
    img.onload = () => {
      resolve(img);
    };
    img.onerror = (err) => {
      reject(err);
    };
    img.src = url;
    img.setAttribute("crossOrigin", "anonymous");
  });
}

function imageSequence(urls: string[]) {
  // download all the images in the sequence
  // and return a promise that resolves to
  // an array of the images
  let imgs: HTMLImageElement[] = [];
  let done = false;
  const promise = Promise.all(urls.map((url) => downloadImage(url))).then(
    (images) => {
      done = true;
      imgs = images;
    }
  );
  let playing = false;
  return {
    play(
      callback: (img: HTMLImageElement, done: boolean) => void,
      frameDelay = 40
    ) {
      if (playing) return;
      playing = true;
      const doit = () => {
        if (!imgs.length) {
          playing = false;
          return;
        }
        callback(imgs[0], urls.length == 1);
        if (urls.length > 1) {
          utils.asyncLoop(
            (i) => {
              const done = i == urls.length - 1;
              callback(imgs[i], done);
              if (done) {
                playing = false;
              }
            },
            frameDelay,
            urls.length,
            1
          );
        } else {
          playing = false;
        }
      };
      if (done) {
        doit();
      } else {
        promise.then(doit);
      }
    },
  };
}
