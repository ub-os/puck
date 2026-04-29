
/**
 * Minimize content element previews in backend
 */
(() => {
	const STORAGE_KEY = 'typo3-content-minimize-state';

	// Get minimize state from localStorage
	function getMinimizeState() {
		try {
			const stored = localStorage.getItem(STORAGE_KEY);
			return stored === 'true';
		} catch (e) {
			console.warn('Could not read minimize state:', e);
			return false;
		}
	}

	// Save minimize state to localStorage
	function saveMinimizeState(isMinimized) {
		try {
			localStorage.setItem(STORAGE_KEY, isMinimized ? 'true' : 'false');
		} catch (e) {
			console.warn('Could not save minimize state:', e);
		}
	}

	// Apply minimize state by toggling CSS class
	function applyMinimizeState(isMinimized) {
		if (isMinimized) {
			document.body.classList.add('content-elements-minimized');
		} else {
			document.body.classList.remove('content-elements-minimized');
		}
	}

	// Toggle minimize state
	function toggleMinimize() {
		const currentState = getMinimizeState();
		const newState = !currentState;
		saveMinimizeState(newState);
		applyMinimizeState(newState);
		updateMinimizeButton(newState);
	}

	// Update button state
	function updateMinimizeButton(isMinimized) {
		const button = document.getElementById('pageLayoutToggleMinimize');
		if (button) {
			button.setAttribute('data-dropdowntoggle-status', isMinimized ? 'active' : 'inactive');
		}
	}

	// Add minimize option to View dropdown
	function addMinimizeOption() {
		// Find the View dropdown menu
		const viewMenu = document.getElementById('pageLayoutToggleShowHidden').closest('ul');

		if (!viewMenu) {
			return false;
		}

		// Check if option already exists
		if (document.getElementById('minimize-content-previews')) {
			return false;
		}

		// Create the minimize option
		const minimizeOption = document.createElement('li');
		const isMinimized = getMinimizeState();
		minimizeOption.innerHTML = `
      <button id="pageLayoutToggleMinimize" type="button" data-dropdowntoggle-status="${isMinimized ? 'active' : 'inactive'}" class="dropdown-item dropdown-item-spaced" title="Minimize content previews">
        <span class="dropdown-item-status"></span>
        <span class="t3js-icon icon icon-size-small icon-state-default icon-actions-minus" data-identifier="actions-minus" aria-hidden="true">
          <span class="icon-markup">
            <svg class="icon-color"><use xlink:href="/_assets/1ee1d3e909b58d32e30dcea666dd3224/Icons/T3Icons/sprites/actions.svg#actions-minus"></use></svg>
          </span>
        </span>
        Minimize content previews
      </button>
    `;

		viewMenu.appendChild(minimizeOption);

		// Add event listener
		const button = document.getElementById('pageLayoutToggleMinimize');
		if (button) {
			button.addEventListener('click', toggleMinimize);
		}
		return true;
	}

	// Initialize
	function initialize() {
		// Apply saved state
		applyMinimizeState(getMinimizeState());

		if (!addMinimizeOption()) {
			const observer = new MutationObserver(() => {
				if (addMinimizeOption()) {
					observer.disconnect();
				}
			});
			const targetNode = document.querySelector('.module-docheader');
			if (targetNode) {
				observer.observe(targetNode, { childList: true, subtree: true });
			}
		}
	}

	// Wait for DOM
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialize);
	} else {
		initialize();
	}
})();