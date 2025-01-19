<?php

namespace UBOS\Puck\Domain;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;

class FieldRecord extends Record
{
	protected mixed $value = null;
	protected ?array $selectedOptions = null;

	public array $repeatableContainerPrevFields = [];

	public function createRepeatableContainerPrevFields(array $valueSets): void
	{
		$index = 0;
		foreach ($valueSets as $values) {
			foreach($this->get('fields') as $childField) {
				$newField = clone $childField;
				if (isset($values[$newField->get('identifier')])) {
					$newField->setValue($values[$newField->get('identifier')]);
				}
				$this->repeatableContainerPrevFields[$index][] = $newField;
			}
			$index++;
		}
	}

	public function __construct(
		protected readonly RawRecord         $rawRecord,
		protected array                      $properties,
		protected readonly ?SystemProperties $systemProperties = null,
	)
	{
		$this->computeDefaultValue();
	}

	protected function computeDefaultValue(): void
	{
		if (!$this->has('default_value')) {
			$this->properties['default_value'] = null;
		}
		$defaultValue = $this->get('default_value');
		if (gettype($defaultValue) === 'object' && (get_class($defaultValue) === 'DateTime' || get_class($defaultValue) === 'DateTimeImmutable')) {
			$this->properties['default_value'] = $defaultValue->format('Y-m-d');
		}
		if (in_array($this->get('type'), ['radio', 'select'])) {
			$value = null;
			foreach ($this->get('field_options') as $option) {
				if ($option->get('selected')) {
					$value = $option->get('value');
					break;
				}
			}
			$this->properties['default_value'] = $value;
		}
		if (in_array($this->get('type'), ['checkbox'])) {
			$value = [];
			foreach ($this->get('field_options') as $option) {
				if ($option->get('selected')) {
					$value[] = $option->get('value');
				}
			}
			$this->properties['default_value'] = $value;
		}
	}
	public function setValue(mixed $value): void
	{
		$this->value = $value;
	}
	public function getValue(): mixed
	{
		return $this->value ?? $this->get('default_value');
	}

	public function getCamelCaseType(): string
	{
		return ucFirst(str_replace('-', '', ucwords($this->get('type'), '-')));
	}
	public function getSelectedOptions(): ?array
	{
		if ($this->selectedOptions !== null) {
			return $this->selectedOptions;
		}
		if (!in_array($this->get('type'), ['checkbox'])) {
			return null;
		}
		$selectedOptions = [];
		$value = $this->getValue();
		if (is_array($value)) {
			foreach ($value as $val) {
				$selectedOptions[$val] = $val;
			}
		} else {
			$selectedOptions[$value] = $value;
		}
		return $selectedOptions;
	}
}
