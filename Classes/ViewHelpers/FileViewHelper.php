<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Resource;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper for handling file resources in Fluid templates
 *
 * This ViewHelper helps with retrieving and working with File and FileReference
 * objects in templates. It can find files by ID or combined identifier, and provides
 * utilities for online media files (YouTube, Vimeo).
 *
 * Example usage:
 * <u:file get="1" set="fileObject" /> <!-- Get file with ID 1 and store in variable -->
 * <u:file getOnlineMediaImageSrc="{fileReference}" /> <!-- Get YouTube/Vimeo thumbnail -->
 */
class FileViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('get', 'mixed', 'Identifier or object to retrieve a file (uid, combined identifier, or File/FileReference object)', false, null);
		$this->registerArgument('getOnlineMediaImageSrc', 'mixed', 'Get thumbnail URL for online media file (YouTube/Vimeo)', false, null);
		$this->registerArgument('useUcSource', 'boolean', 'Use Usercentrics privacy proxy for YouTube thumbnails', false, null);
		$this->registerArgument('set', 'string', 'Variable name to store result in', false, '');
	}

	/**
	 * Processes the file-related operation based on provided arguments
	 *
	 * @return mixed File object, thumbnail URL, or null if result stored in variable
	 */
	public function render(): mixed
	{
		$result = null;
		if ($this->arguments['get']) {
			$result = self::getFile($this->arguments['get']);
		}
		if ($this->arguments['getOnlineMediaImageSrc']) {
			$file = self::getFile($this->arguments['getOnlineMediaImageSrc']);
			if (is_object($file)) {
				return self::getOnlineMediaImageSrc($file, $this->arguments);
			}
		}
		if ($this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
			return null;
		}
		return $result;
	}

	/**
	 * Retrieves a file or file reference based on different input types
	 *
	 * Can handle:
	 * - Integer UIDs
	 * - String combined identifiers (e.g. "1:/path/to/file.jpg")
	 * - FileReference objects
	 * - File objects
	 * - FalFile objects (from FluidComponents)
	 *
	 * @param mixed $file The file reference to retrieve
	 * @return string|Resource\FileReference|Resource\File|null File object or error message
	 */
	public static function getFile(mixed $file): string|Resource\FileReference|Resource\File|null
	{
		if (!$file) {
			return null;
		}
		if (is_int($file)) {
			$resourceFactory = GeneralUtility::makeInstance(Resource\ResourceFactory::class);
			try {
				return $resourceFactory->getFileObject($file);
			} catch (\Exception $e) {
				return null;
			}
		}
		if (is_string($file)) {
			$resourceFactory = GeneralUtility::makeInstance(Resource\ResourceFactory::class);
			try {
				return $resourceFactory->getFileObjectFromCombinedIdentifier($file);
			} catch (\Exception $e) {
				return null;
			}
		}
		if (is_object($file)) {
			if (get_class($file) === 'TYPO3\CMS\Extbase\Domain\Model\FileReference') {
				$file = $file->getOriginalResource();
			}
			if (get_class($file) === 'TYPO3\CMS\Core\Resource\FileReference') {
				return $file;
			}
			if (get_class($file) === 'TYPO3\CMS\Core\Resource\File') {
				return $file;
			}
			return 'Provided "get" argument is not a FileReference object';
		}
		return '"get" argument must be a string (file combined identifier), integer (uid) or a File/FileReference object';
	}

	/**
	 * Resolves a poster/thumbnail URL for an online media file (YouTube/Vimeo).
	 *
	 * - useUcSource=true (YouTube only): returns the Usercentrics privacy-proxy
	 *   URL, without any HTTP request.
	 * - otherwise: delegates to the TYPO3 core online media helper, which fetches
	 *   the poster once and caches it on disk, and returns the local web path.
	 *
	 * @param array $arguments ViewHelper arguments
	 * @return string|null Poster URL or null if none is available
	 */
	protected static function getOnlineMediaImageSrc(Resource\FileReference|Resource\File $file, array $arguments = []): ?string
	{
		$originalFile = $file instanceof Resource\FileReference ? $file->getOriginalFile() : $file;

		if (($arguments['useUcSource'] ?? false) && $originalFile->getExtension() === 'youtube') {
			return 'https://privacy-proxy-server.usercentrics.eu/video/youtube/'
				. $originalFile->getContents() . '-poster-image';
		}

		$helper = GeneralUtility::makeInstance(OnlineMediaHelperRegistry::class)->getOnlineMediaHelper($originalFile);
		if ($helper === false) {
			return null;
		}
		$localPreview = $helper->getPreviewImage($originalFile);
		return $localPreview !== '' && is_file($localPreview)
			? PathUtility::getAbsoluteWebPath($localPreview)
			: null;
	}
}
