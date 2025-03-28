interface QueryNotation {
  width: number;
  direction: "min-width" | "max-width";
  name?: string;
}

const QUERY_NOTATION_MAP = new Map<Element, Array<QueryNotation>>();

export default (context: Document | Element = document) => {
  const resizeObserver = new ResizeObserver((entries) => {
    for (let i = 0; i < entries.length; i++) {
      const entry = entries[i];
      handleResize(entry as any);
    }
  });
  // Find all the containers to observe
  context
    .querySelectorAll<HTMLElement>(".is-query-container,.qc")
    .forEach((container) => {
      if (!QUERY_NOTATION_MAP.has(container)) {
        // Observe the size of the container
        resizeObserver.observe(container);
        const queries = parseQueries(container.getAttribute("data-queries"));
        if (queries) {
          // Map it's queries to the element
          QUERY_NOTATION_MAP.set(container, queries);
        }
      }
    });
};

function handleResize(entry: { target: HTMLElement; contentRect?: DOMRectReadOnly }) {
  if (!entry.contentRect) {
    entry.contentRect = entry.target.getBoundingClientRect();
  }
  const queryNotations = QUERY_NOTATION_MAP.get(entry.target)!;
  const width = entry.contentRect.width;
  const height = entry.contentRect.height;
  if (queryNotations) {
    for (let j = 0; j < queryNotations.length; j++) {
      const query = queryNotations[j];
      const className = query.name || query.direction + "-" + query.width;
      // Determine if the "query" applies and add/remove the class depending on the determination
      if (
        (query.direction == "min-width" && width >= query.width) ||
        (query.direction == "max-width" && width <= query.width)
      ) {
        entry.target.classList.add(className);
      } else {
        entry.target.classList.remove(className);
      }
    }
  }
  let el: HTMLElement = entry.target;
  if (el.dataset.offset) {
    let offset = parseInt(el.dataset.offset);
    if (!isNaN(offset)) {
      while (el.parentElement && offset > 0) {
        el = el.parentElement;
        offset--;
      }
    }
  }
  // Set the custom width property so the children always can pull the literal width
  el.style.setProperty("--qcw", width + "px");
  // Set the custom height property so the children always can pull the literal height
  el.style.setProperty("--qch", height + "px");
}

function parseQueries(string: string | null) {
  if (string) {
    const sets = string.split(",").map((set) => {
      const keyValuePair = set.split(":");
      const widthNotation = keyValuePair[1] || keyValuePair[0];
      const queryNotation: QueryNotation = {
        width: parseInt(widthNotation),
        direction: widthNotation.endsWith("+") ? "min-width" : "max-width",
      };
      if (keyValuePair.length > 1) {
        queryNotation.name = keyValuePair[0];
      }
      return queryNotation;
    });
    return sets;
  }
  return null;
}
