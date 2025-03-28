export default (context: Document | Element = document) => {
  context.querySelectorAll<HTMLImageElement>('img:is([sizes="auto" i], [sizes^="auto," i]):not([data-auto])').forEach((img) => {
    // Remove the auto value from the sizes attribute that Chrome automatically adds
    const sizesString = img.getAttribute('sizes')!.split(',').filter((size) => !size.trim().startsWith('auto')).join(',').trim();
    img.setAttribute('sizes', sizesString);
  });
  
  // For images that slow-load, we've added previewed images with a blurred out smaller version that displays until the full image loads
  context.querySelectorAll<HTMLElement>('.previewed-image').forEach((previewedImage) => {
    const img = previewedImage.querySelector<HTMLImageElement>('img');
    if (img) {
      img.onload = () => {
        previewedImage.classList.add('loaded');
        setTimeout(() => {
          previewedImage.classList.add('remove-preview');
        }, 400);
      };
    }
  });
}