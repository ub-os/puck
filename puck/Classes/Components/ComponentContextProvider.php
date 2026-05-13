<?php

namespace UBOS\Puck\Components;

use UBOS\Puck\Constants;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Fluid\Event\ProvideStaticVariablesToComponentEvent;
use TYPO3\CMS\Fluid\Event\RenderComponentEvent;

final class ComponentContextProvider
{
	private function isOwnCollection(string $namespace): bool
	{
		return str_starts_with($namespace, 'UBOS\\Puck\\Components');
	}

	#[AsEventListener]
	public function provideStaticVariables(ProvideStaticVariablesToComponentEvent $event): void
	{
		if (!$this->isOwnCollection($event->getComponentCollection()->getNamespace())) {
			return;
		}
		$event->setStaticVariables([
			...$event->getStaticVariables(),
			'constants' => Constants::toArray(),
		]);
	}

	#[AsEventListener]
	public function provideRenderArguments(RenderComponentEvent $event): void
	{
		if (!$this->isOwnCollection($event->getComponentCollection()->getNamespace())) {
			return;
		}

		$site = $event->getRequest()->getAttribute('site');
		if (!$site) {
			return;
		}

		$settings = $site->getSettings();

		$event->setArguments([
			...$event->getArguments(),
			'site' => [
				'settings' => [
					'puck' => $settings->get('puck') ?? []
				]
			],
		]);
	}
}