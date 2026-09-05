<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Component\ComponentAdapter;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentRendererInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

/**
 * Generic tag builder registered under the `tag:` namespace.
 *
 * `<tag:div id="{itemId}" data-foo="bar">…</tag:div>` renders as
 * `<div id="…" data-foo="bar">…</div>` — the tag name is the ViewHelper
 * name itself, works for any tag including custom elements.
 *
 * Uses Fluid's component provider API instead of a template or a
 * TagBasedViewHelper subclass: resolveViewHelperClassName() always
 * returns ComponentAdapter::class so the parser routes every `<tag:x>`
 * through getComponentRenderer() below, and additionalArgumentsAllowed
 * means arbitrary attributes arrive as one flat array in renderComponent()
 * rather than being spread into named variables.
 *
 * Registered in ext_localconf.php:
 *   $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['tag'][] =
 *       \UBOS\Puck\ViewHelpers\TagComponentProvider::class;
 *
 * @see https://docs.typo3.org/other/typo3fluid/fluid/main/en-us/Extending/ComponentProviders.html
 */
final class TagComponentProvider implements
	ViewHelperResolverDelegateInterface,
	ComponentDefinitionProviderInterface,
	ComponentRendererInterface
{
	public function resolveViewHelperClassName(string $viewHelperName): string
	{
		return ComponentAdapter::class;
	}

	public function getNamespace(): string
	{
		return static::class;
	}

	public function getComponentDefinition(string $viewHelperName): ComponentDefinition
	{
		return new ComponentDefinition(
			name: $viewHelperName,
			argumentDefinitions: [],
			additionalArgumentsAllowed: true,
			availableSlots: ['default'],
		);
	}

	public function getComponentRenderer(): ComponentRendererInterface
	{
		return $this;
	}

	/**
	 * - `is`, but only on the reserved `<tag:dynamic>` name — sets the actual
	 *    tag when it isn't known until render time (e.g. a CMS-configurable
	 *    heading level). On any other tag name, `is` is passed through as a
	 *    normal attribute (e.g. `<tag:button is="fancy-button">` for a
	 *    customized built-in element).
 	*  - `additionalAttributes` — array of attribute => value pairs merged onto
	 *    the tag as a whole (e.g. attributes forwarded from a parent scope).
	 *    Explicit attributes on the tag itself take precedence on collision.
	 *  Builds `class` from three sources, in order, then drops it if empty:
	 * - `class` — plain string, used as-is
	 * - `classList` — map of className => bool, truthy keys included (as
	 *   sitegeist/fluid-tagbuilder does)
	 * - classModifiers — BEM modifiers as key => value; booleans render as `-key`,
	 *   other values as `key-value`, `false`/`'default'` are skipped
	 *
	 * All other attributes are dropped when empty/null so conditional ones
	 * (id, role, ...) need no f:if wrapping — except data-*, kept even when
	 * empty since e.g. `data-active` is valid HTML on its own.
	 *
	 * Slot content is a Closure (lazy, like ViewHelper child content) and
	 * must be invoked.
	 */
	public function renderComponent(
		string $viewHelperName,
		array $arguments,
		array $slots,
		RenderingContextInterface $parentRenderingContext,
	): string {
		$tagName = $viewHelperName;
		if ($viewHelperName === 'dynamic') {
			$tagName = $arguments['is'] ?? 'div';
			unset($arguments['is']);
		}

		if (isset($arguments['additionalAttributes']) && is_array($arguments['additionalAttributes'])) {
			$arguments += $arguments['additionalAttributes'];
		}
		unset($arguments['additionalAttributes']);

		$arguments['class'] = ClassViewHelper::build(
			(string) ($arguments['class'] ?? ''),
			is_array($arguments['classList'] ?? null) ? $arguments['classList'] : [],
			is_array($arguments['classModifiers'] ?? null) ? $arguments['classModifiers'] : [],
		);
		unset($arguments['classList'], $arguments['classModifiers']);

		$content = isset($slots['default']) ? $slots['default']() : '';
		$tag = new TagBuilder($tagName, $content);
		$tag->forceClosingTag(true);
		foreach ($arguments as $name => $value) {
			if (str_starts_with($name, 'data-') || ($value !== null && $value !== '')) {
				$tag->addAttribute($name, $value);
			}
		}

		return $tag->render();
	}
}