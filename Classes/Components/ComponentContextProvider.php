<?php

namespace UBOS\Puck\Components;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Fluid\Event\ProvideStaticVariablesToComponentEvent;
use TYPO3\CMS\Fluid\Event\RenderComponentEvent;
use UBOS\Puck\Constants;

final class ComponentContextProvider
{
	/** @var array<string, mixed>|null Built once per request; identical for every component render. */
	private ?array $constants = null;

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
			'constants' => $this->constants ??= Constants::toArray(),
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

		$event->setArguments([
			...$event->getArguments(),
			'site' => [
				'rootPageId' => $site->getRootPageId(),
				'settings' => $site->getSettings(),
			],
		]);
	}
}
