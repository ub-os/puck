/**
 * Persist container collapse state in backend preview
 */
(() => {
	const STORAGE_KEY = 'typo3-container-collapse-state';

	// Get collapse state from localStorage
	function getCollapseStates() {
		try {
			const stored = localStorage.getItem(STORAGE_KEY);
			return stored ? JSON.parse(stored) : {};
		} catch (e) {
			console.warn('Could not read collapse states:', e);
			return {};
		}
	}

	// Save collapse state to localStorage
	function saveCollapseState(uid, isOpen) {
		try {
			const states = getCollapseStates();
			states[uid] = isOpen;
			localStorage.setItem(STORAGE_KEY, JSON.stringify(states));
		} catch (e) {
			console.warn('Could not save collapse state:', e);
		}
	}

	// Update button appearance
	function updateButtonAppearance(button, isOpen) {
		const icon = button.querySelector('.icon-markup');

		if (icon) {
			icon.style.transform = isOpen ? 'rotate(90deg)' : 'none';
		}

		button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		button.classList.toggle('collapsed', !isOpen);
	}

	// Initialize on DOM ready
	function initialize() {
		const states = getCollapseStates();

		// Restore states for all containers
		document.querySelectorAll('.container-preview-toggle-btn').forEach((button) => {
			const uid = button.getAttribute('data-container-uid');
			const targetSelector = button.getAttribute('data-bs-target');
			const target = document.querySelector(targetSelector);

			if (!target) {
				return;
			}

			// biome-ignore lint/suspicious/noPrototypeBuiltins: <explanation>
			const isOpen = states.hasOwnProperty(uid) ? states[uid] : true;

			if (isOpen) {
				target.classList.add('show');
				updateButtonAppearance(button, true);
			} else {
				target.classList.remove('show');
				updateButtonAppearance(button, false);
			}
		});

		// Listen for collapse events
		document.addEventListener('show.bs.collapse', (event) => {
			if (event.target.id?.startsWith('container-')) {
				const uid = event.target.id.replace('container-', '');
				const button = document.querySelector(`[data-container-uid="${uid}"]`);

				if (button) {
					updateButtonAppearance(button, true);
				}

				saveCollapseState(uid, true);
			}
		});

		document.addEventListener('hide.bs.collapse', (event) => {
			if (event.target.id?.startsWith('container-')) {
				const uid = event.target.id.replace('container-', '');
				const button = document.querySelector(`[data-container-uid="${uid}"]`);

				if (button) {
					updateButtonAppearance(button, false);
				}

				saveCollapseState(uid, false);
			}
		});
	}

	// Wait for DOM
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialize);
	} else {
		initialize();
	}
})();