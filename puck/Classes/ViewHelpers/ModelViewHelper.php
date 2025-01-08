<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ModelViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('data', 'array', '', false, []);
		$this->registerArgument('map', 'string', '', false, '');
		$this->registerArgument('bulk', 'bool', '', false, false);
		$this->registerArgument('set', 'string', '', false, '');
	}

	public function render(): mixed
	{
		$data = $this->arguments['data'] ?: $this->renderChildren() ?? [];
		if (!isset($data['uid'])) {
			$data['uid'] = 0;
		}
		$name = $this->arguments['map'];
		if (str_starts_with($name, '~')) {
			$name = 'UBOS\\Puck\\Domain\\Model\\' . substr($name, 1);
		}
		$dataMapper = GeneralUtility::makeInstance(DataMapper::class);
		if ($this->arguments['bulk']) {
			$result = $dataMapper->map($name, $data);
		} else {
			$result = $dataMapper->map($name, [$data])[0];
		}
		if ($this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
			return null;
		}
		return $result;
	}
}
