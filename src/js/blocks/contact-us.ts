export default (context: Document | Element = document) => {
  context.querySelectorAll(".contact-us-block").forEach((block) => {
    block
      .querySelectorAll<HTMLInputElement>(
        "input[type='text'], input[type='email'], textarea"
      )
      .forEach((input) => {
        const wrap = input.parentElement! as HTMLElement;
        input.addEventListener("input", () => {
          wrap.classList.toggle("has-value", !!input.value);
        });
        // Check if it started out with a value
        wrap.classList.toggle("has-value", !!input.value);
      });
  });
};
