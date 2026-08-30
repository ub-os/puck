<?php

declare(strict_types=1);

namespace UBOS\Puck\Install;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\Event\AfterPackageActivationEvent;
use TYPO3\CMS\Core\Upgrades\ChattyInterface;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[UpgradeWizard('puck_extension_setup')]
final class ExtensionSetup implements UpgradeWizardInterface, ChattyInterface
{
	private const EXTENSION_KEY = 'puck';
	private const ROUTE_PREFIX = '/_assets/theme';
	private OutputInterface $output;

	public function setOutput(OutputInterface $output): void
	{
		$this->output = $output;
	}

	/**
	 * Create symlink after extension installation (signal/slot method)
	 */
	#[AsEventListener]
	public function __invoke(AfterPackageActivationEvent $event): void
	{
		if ($event->getPackageKey() === self::EXTENSION_KEY) {
			$this->createSymlink();
		}
	}

	/**
	 * Get title for the upgrade wizard
	 */
	public function getTitle(): string
	{
		return 'Setup Puck Extension';
	}
	/**
	 * Get description for the upgrade wizard
	 */
	public function getDescription(): string
	{
		return 'Creates a symlink to serve public extension resources under "/_assets/theme" path.';
	}
	/**
	 * Create symlink during upgrade wizard execution
	 */
	public function executeUpdate(): bool
	{
		return $this->createSymlink();
	}
	/**
	 * Check if update is needed
	 */
	public function updateNecessary(): bool
	{
		$publicPath = Environment::getPublicPath();
		$symlinkPath = $publicPath . self::ROUTE_PREFIX;
		// Update needed if symlink doesn't exist or is broken
		return !is_link($symlinkPath) || !file_exists($symlinkPath);
	}
	public function getPrerequisites(): array
	{
		return [];
	}

	private function createSymlink(): bool
	{
		$publicPath = Environment::getPublicPath();
		try {
			$symlinkPath = $publicPath . self::ROUTE_PREFIX;
			$absoluteTargetPath = GeneralUtility::getFileAbsFileName('EXT:puck/Resources/Public/');
			$relativeTargetPath = '../../vendor/ubos/puck/Resources/Public';
			// Check if target exists
			if (!is_dir($absoluteTargetPath)) {
				$this->output->writeln('Target is not a folder: ' . $absoluteTargetPath);
				return false;
			}

			// Remove existing if present
			if (file_exists($symlinkPath)) {
				if (is_link($symlinkPath)) {
					unlink($symlinkPath);
				} else {
					// Don't overwrite existing directories
					$this->output->writeln('Symlink path already exists and is not a symlink: ' . $symlinkPath);
					return false;
				}
			}

			// Create symlink
			return symlink($relativeTargetPath, $symlinkPath);

		} catch (\Exception $e) {
			$this->output->writeln('Error creating symlink: ' . $e->getMessage());
			return false;
		}
	}
}
