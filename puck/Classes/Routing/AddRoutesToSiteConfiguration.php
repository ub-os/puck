<?php

declare(strict_types=1);

namespace UBOS\Puck\Routing;

use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\SiteConfigurationLoadedEvent;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsEventListener]
final readonly class AddRoutesToSiteConfiguration
{
	public function __construct(
		private YamlFileLoader $yamlFileLoader,
	) {}

	public function __invoke(SiteConfigurationLoadedEvent $event): void
	{
		$streamlinedFilePath = GeneralUtility::getFileAbsFileName('EXT:puck/Configuration/Routes/');

		$finder = new Finder();
		$finder
			->files()
			->name('*.yaml')
			->in($streamlinedFilePath);

		if (! $finder->hasResults()) {
			return;
		}

		$configuration = $event->getConfiguration();
		foreach ($finder as $file) {
			$routeConfiguration = $this->yamlFileLoader->load($file->getRealPath());
			$routeEnhancers = $routeConfiguration['routeEnhancers'] ?? [];
			foreach( $routeEnhancers as $key => $enhancer ) {
				if (($enhancer['limitToPages'] ?? false) !== self::class) {
					continue;
				}
				$routeConfiguration['routeEnhancers'][$key]['limitToPages'] = $this->getPageLimitForEnhancer($key, $enhancer, $configuration);
			}

			ArrayUtility::mergeRecursiveWithOverrule($configuration, $routeConfiguration);
		}

		$event->setConfiguration($configuration);
	}

	protected function getPageLimitForEnhancer(string $enhancerName, array $enhancerConfiguration, array $siteConfiguration): ?array
	{
		switch ($enhancerName) {
			case 'Sitemap':
			case 'SitemapPage':
				return [$siteConfiguration['rootPageId']];
			case 'PageMenu':
				return $siteConfiguration['route_pageMenu_limitToPages'] ?? [0];
			case 'IndexedSearch':
				return $siteConfiguration['route_indexedSearch_limitToPages'] ?? [0];
			default:
				return null;
		}
	}
}