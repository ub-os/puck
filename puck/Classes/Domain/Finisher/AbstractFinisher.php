<?php

namespace UBOS\Puck\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

abstract class AbstractFinisher
{
	protected Extbase\Mvc\RequestInterface $request;
	protected array $pluginSettings;
	protected array $settings;
	protected array $finisherData;
	protected array $formValues;
	protected Core\Domain\Record $formRecord;

	final public function execute(
		Extbase\Mvc\RequestInterface $request,
		array $finisherData,
		Core\Domain\Record $formRecord,
		array $pluginSettings,
		array $formValues,
	):  ?ResponseInterface
	{
		$this->request = $request;
		$this->pluginSettings = $pluginSettings;
		$this->finisherData = $finisherData;
		$this->formValues = $formValues;
		$this->formRecord = $formRecord;
		$flexFormService = GeneralUtility::makeInstance(Core\Service\FlexFormService::class);
		$this->settings = $flexFormService->convertFlexFormContentToArray($finisherData['settings']);
		$this->executeInternal();
		return $this->responseOverride();
	}
	abstract protected function executeInternal(): void;
	protected function responseOverride(): ?ResponseInterface
	{
		return null;
	}
}