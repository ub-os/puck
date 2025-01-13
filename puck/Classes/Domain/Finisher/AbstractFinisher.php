<?php

namespace UBOS\Puck\Domain\Finisher;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;

abstract class AbstractFinisher
{
	protected Extbase\Mvc\RequestInterface $request;
	protected array $controllerSettings;
	protected array $settings;
	protected array $formValues;
	protected Core\Domain\Record $formRecord;

	final public function execute(Extbase\Mvc\RequestInterface $request, array $controllerSettings, array $settings, array $formValues, Core\Domain\Record $formRecord): void
	{
		$this->request = $request;
		$this->controllerSettings = $controllerSettings;
		$this->settings = $settings;
		$this->formValues = $formValues;
		$this->formRecord = $formRecord;
		$this->executeInternal();
	}
	abstract protected function executeInternal(): void;
}