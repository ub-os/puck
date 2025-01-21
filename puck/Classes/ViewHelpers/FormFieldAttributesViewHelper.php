<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class FormFieldAttributesViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('record', 'object', '', true);
		$this->registerArgument('attributes', 'array', '', false, []);
	}

	public function render(): array
	{
		$record = $this->arguments['record'];
		$attributes = [
			'data-field-id' => $record->get('identifier'),
		];
		$attributes['onchange'] = 'window.__tx_formal.evalConditions()';
		if ($record->has('js_display_condition') && $record->get('js_display_condition')) {
			//
		}
		foreach (['required', 'readonly', 'disabled', 'multiple'] as $attribute) {
			$val = $record->get($attribute);
			if ($val) {
				$attributes[$attribute] = '';
			}
		}
		foreach (['step', 'pattern', 'maxlength', 'placeholder'] as $attribute) {
			$val = $record->get($attribute);
			if ($val) {
				$attributes[$attribute] = (string)$val;
			}
		}
		foreach (['min', 'max'] as $attribute) {
			$val = $record->get($attribute);
			if ($val || $val === 0) {
				$attributes[$attribute] = $this->stringFromValue($val);
			}
		}
		if ($record->get('default_value')) {
			//$attributes['value'] = $this->stringFromValue($record->get('default_value'));
		}
		if ($record->get('validation_message')) {
			$attributes['oninvalid'] = 'this.setCustomValidity("' . $record->get('validation_message') . '")';
			$attributes['oninput'] = 'this.setCustomValidity("")';
		}
		return array_merge($attributes, $this->arguments['attributes']);
	}

	protected function stringFromValue($val): string
	{
		if (gettype($val) === 'object' && (get_class($val) === 'DateTime' || get_class($val) === 'DateTimeImmutable')) {
			$val = $val->format('Y-m-d');
		}
		return (string)$val;
	}

}
