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
		$defaultValue = $this->get('default_value');
		if (gettype($defaultValue) === 'object' && (get_class($defaultValue) === 'DateTime' || get_class($defaultValue) === 'DateTimeImmutable')) {
			$this->properties['default_value'] = $defaultValue->format('Y-m-d');
		}
		if ($this->get('type') === 'radio') {
			$value = null;
			foreach ($this->get('field_options') as $option) {
				if ($option->get('selected')) {
					$value = $option->get('value');
					break;
				}
			}
			$this->properties['default_value'] = $value;
		}
		if (in_array($this->get('type'), ['checkbox', 'select'])) {
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
	public function getSelectedOptions(): ?array
	{
		if ($this->selectedOptions !== null) {
			return $this->selectedOptions;
		}
		if (!in_array($this->get('type'), ['checkbox', 'select'])) {
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
