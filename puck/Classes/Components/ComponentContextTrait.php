<?php

namespace UBOS\Puck\Components;

trait ComponentContextTrait
{
	protected ?array $settingsVariable = null;
	protected function getSettingsVariable(): array
	{
		if ($this->settingsVariable == null) {
			$request = $GLOBALS['TYPO3_REQUEST'];
			$site = $request->getAttribute('site');
			$siteSettings = $site->getSettings();
			$this->settingsVariable = [
				'system' => $siteSettings->get('system'),
				'doktypes' => $siteSettings->get('doktypes'),
				'template' => $siteSettings->get('template'),
			];
		}
		return $this->settingsVariable;
	}
	public function getAdditionalVariables(string $viewHelperName): array
	{
		return ['settings' => $this->getSettingsVariable()];
	}
}