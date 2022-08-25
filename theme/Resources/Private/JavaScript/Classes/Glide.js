import Glide from '@glidejs/glide';

// Custom Glide Frontend
function glideArrowDisabler(Glide, Components, Events) {
  return {
    mount() {
      // Only in effect when rewinding is disabled
      if (Glide.settings.rewind) {
        return;
      }
      Glide.on(['mount.after', 'run'], () => {
        // Filter out arrows_control
        for (let controlItem of Components.Controls.items) {
          // Set left arrow state
          const left = controlItem.querySelector('.glide__arrow--left');
          if (left) {
            if (Glide.index === 0) {
              left.setAttribute('disabled', ''); // Disable on first slide
            } else {
              left.removeAttribute('disabled'); // Enable on other slides
            }
          }
          // Set right arrow state
          const right = controlItem.querySelector('.glide__arrow--right');
          if (right) {
            if (Glide.index === Components.Sizes.length - Glide.settings.perView) {
              right.setAttribute('disabled', ''); // Disable on last slide
            } else {
              right.removeAttribute('disabled'); // Disable on other slides
            }
          }
        }
      });
    }
  };
}
export {Glide, glideArrowDisabler};