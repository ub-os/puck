<?php

namespace UBOS\Puck\Domain;


class SingleSelectOptionFieldRecord extends GenericFieldRecord
{
	public function computeProperties(): void
	{
		$value = null;
		foreach ($this->get('field_options') as $option) {
			if ($option->get('selected')) {
				$value = $option->get('value');
				break;
			}
		}
		$this->properties['default_value'] = $value;
	}
}
