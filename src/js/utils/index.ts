type HTMLQuery<T> = T extends Array<string>
  ? Array<HTMLElement>
  : T extends { ignoreNull: true }
  ? HTMLElement | null
  : HTMLElement;
type HTMLQueryStrict<T> = T extends Array<string>
  ? Array<HTMLElement>
  : HTMLElement | null;
type HTMLQueryMap<O> = { [Property in keyof O]: HTMLQuery<O[Property]> };
type HTMLQueryMapStrict<O> = {
  [Property in keyof O]: HTMLQueryStrict<O[Property]>;
};
type HTMLQueryOptions = {
  selector: string;
  ignoreNull?: boolean;
};
type ScrollPosition =
  | { left: number }
  | { top: number }
  | { left: number; top: number };

export default {
  getRGB(color: string): Array<number> {
    var el = document.createElement("span");
    el.style.color = color;
    document.body.append(el);
    var color = getComputedStyle(el).color;
    el.remove();
    var match = color.match(/rgba?\((.+)\)/);
    if (match) {
      return match[1]
        .split(", ")
        .map((n, i) => (i !== 3 ? parseInt(n) : Math.round(Number(n) * 255)));
    }
    return [];
  },

  mixColors(...colors: Array<Array<number>>) {
    const defaults = [0, 0, 0, 255];
    const result: Array<number> = [];
    for (let j = 0; j < defaults.length; j++) {
      const value = defaults[j];
      for (let i = 0; i < colors.length; i++) {
        const color = colors[i];
        const channelValue = color[j] || value;
        result[j] += channelValue;
      }
    }
    for (let i = 0; i < result.length; i++) {
      const value = result[i];
      result[i] = value / colors.length;
    }
    return result;
  },

  mixColorPair(
    color1: Array<number>,
    color2: Array<number>,
    mixFactor: number
  ) {
    const maxsize = Math.max(color2.length, color1.length, 3);
    if (color1.length < 4) {
      color1 = Object.assign([], [0, 0, 0, 255], color1);
    }
    if (color2.length < 4) {
      color2 = Object.assign([], [0, 0, 0, 255], color2);
    }
    const mix = [
      color1[0] * (1 - mixFactor) + color2[0] * mixFactor,
      color1[1] * (1 - mixFactor) + color2[1] * mixFactor,
      color1[2] * (1 - mixFactor) + color2[2] * mixFactor,
      color1[3] * (1 - mixFactor) + color2[3] * mixFactor,
    ];
    mix.length = maxsize;
    return mix;
  },
  smoothScroll(
    target: ScrollPosition | Element,
    duration: number,
    container: Element | typeof window = window
  ) {
    var pos =
      target instanceof Element
        ? getScrollPosition(target, container)
        : fillScrollPosition(target);

    pos.left = Math.round(pos.left);
    pos.top = Math.round(pos.top);

    duration = Math.round(duration);

    // Get the correct scroll property for the container type
    const scrollX = container instanceof Element ? "scrollLeft" : "scrollX";
    const scrollY = container instanceof Element ? "scrollTop" : "scrollY";

    if (duration < 0) {
      return Promise.reject("bad duration");
    }
    if (duration === 0) {
      container.scroll(pos.left, pos.top);
      return Promise.resolve();
    }

    var startTime = Date.now();
    var endTime = startTime + duration;

    var startLeft = container[scrollX];
    var startTop = container[scrollY];
    var distanceX = pos.left - startLeft;
    var distanceY = pos.top - startTop;

    // based on http://en.wikipedia.org/wiki/Smoothstep
    var smoothStep = function (start: number, end: number, point: number) {
      if (point <= start) {
        return 0;
      }
      if (point >= end) {
        return 1;
      }
      var x = (point - start) / (end - start); // bi-cubic interpolation
      return x * x * (3 - 2 * x);
    };

    return new Promise<void>(function (resolve, reject) {
      // This is to keep track of where the container's scrollTop is
      // supposed to be, based on what we're doing
      var previousLeft = container[scrollX];
      var previousTop = container[scrollY];

      // This is like a think function from a game loop
      var scrollFrame = function () {
        if (
          container[scrollX] != previousLeft &&
          (!(container instanceof Element) ||
            container.scrollWidth >
              container.scrollLeft + container.clientWidth)
        ) {
          reject("interrupted");
          return;
        }
        if (
          container[scrollY] != previousTop &&
          (!(container instanceof Element) ||
            container.scrollHeight >
              container.scrollTop + container.clientHeight)
        ) {
          reject("interrupted");
          return;
        }

        // set the scrollTop for this frame
        var now = Date.now();
        var point = smoothStep(startTime, endTime, now);

        var frameLeft = Math.round(startLeft + distanceX * point);
        var frameTop = Math.round(startTop + distanceY * point);
        // console.log(frameLeft, frameTop);
        container.scroll(frameLeft, frameTop);

        // check if we're done!
        if (now >= endTime) {
          resolve();
          return;
        }

        // If we were supposed to scroll but didn't, then we
        // probably hit the limit, so consider it done; not
        // interrupted.
        if (
          container[scrollX] === previousLeft &&
          container[scrollX] !== frameLeft &&
          container[scrollY] === previousTop &&
          container[scrollY] !== frameTop
        ) {
          resolve();
          return;
        }
        previousLeft = frameLeft;
        previousTop = frameTop;

        // schedule next frame for execution
        setTimeout(scrollFrame, 0);
      };

      // boostrap the animation process
      setTimeout(scrollFrame, 0);
    });
  },
  isTouchDevice4() {
    var prefixes = " -webkit- -moz- -o- -ms- ".split(" ");

    var mq = function (query: string) {
      return window.matchMedia(query).matches;
    };
    var DocumentTouch = (window as any).DocumentTouch;
    if (
      "ontouchstart" in window ||
      (DocumentTouch && document instanceof DocumentTouch)
    ) {
      return true;
    }

    // include the 'heartz' as a way to have a non matching MQ to help terminate the join
    // https://git.io/vznFH
    var query = ["(", prefixes.join("touch-enabled),("), "heartz", ")"].join(
      ""
    );
    return mq(query);
  },
  copyToClipboard(str: string) {
    const el = document.createElement("textarea");
    el.value = str;
    document.body.appendChild(el);
    el.select();
    document.execCommand("copy");
    document.body.removeChild(el);
  },
  getElements<
    T extends { [key: string]: string | string[] | HTMLQueryOptions }
  >(
    selectors: T,
    container: Document | Element = document
  ):
    | (HTMLQueryMapStrict<T> & { $nulls: Array<string> })
    | (HTMLQueryMap<T> & { $nulls: false }) {
    const elements: any = {
      $nulls: [],
    };
    for (const name in selectors as { [key: string]: any }) {
      if (Object.prototype.hasOwnProperty.call(selectors, name)) {
        const selector = selectors[name];
        if (selector instanceof Array) {
          elements[name] = Array.from(
            container.querySelectorAll(selector.join())
          );
        } else if (selector instanceof Object) {
          elements[name] = container.querySelector(selector.selector);
          if (!elements[name] && !selector.ignoreNull) {
            elements.$nulls.push(name);
          }
        } else {
          elements[name] = container.querySelector(selector);
          if (!elements[name]) {
            elements.$nulls.push(name);
          }
        }
      }
    }
    if (!elements.$nulls.length) {
      elements.$nulls = false;
    }
    return elements;
  },
  asyncLoop(
    callback: (index: number) => void,
    timeout: number,
    limit = Infinity,
    i = 0
  ): TimedLoopControls {
    if (!limit) {
      return {
        stop: () => {},
      };
    }
    const data = {
      stop: false,
    };
    TLRecursionCall(callback, timeout, limit, i, data);
    return {
      stop() {
        data.stop = true;
      },
    };
  },
};

function getScrollPosition(
  element: Element,
  container: Element | typeof window
) {
  const eRect = element.getBoundingClientRect();
  if (container instanceof Element) {
    const cRect = container.getBoundingClientRect();
    return {
      left: eRect.left - cRect.left + container.scrollLeft,
      top: eRect.top - cRect.top + container.scrollTop,
    };
  } else {
    return {
      left: eRect.left + container.scrollX,
      top: eRect.top + container.scrollY,
    };
  }
}
function fillScrollPosition(value: ScrollPosition) {
  if (!("left" in value)) {
    return Object.assign({ left: 0 }, value);
  } else if (!("top" in value)) {
    return Object.assign({ top: 0 }, value);
  } else {
    return value;
  }
}

type TimedLoopControls = {
  stop: Function;
};
function TLRecursionCall(
  callback: (index: number) => void,
  timeout: number,
  limit: number,
  i: number,
  data: any
) {
  if (data.stop) {
    return;
  }
  callback(i);
  if (i + 1 < limit) {
    setTimeout(
      () => TLRecursionCall(callback, timeout, limit, i + 1, data),
      timeout
    );
  }
}
