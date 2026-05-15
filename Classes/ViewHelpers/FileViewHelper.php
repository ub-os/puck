<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Resource;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
	 * @return null|string|Resource\FileReference|Resource\File File object or error message
	 */
	public static function getFile(mixed $file): false|string|Resource\FileReference|Resource\File
	{
		if (!$file) {
			return $file;
		}
		if (is_int($file)) {
			$resourceFactory = GeneralUtility::makeInstance(Resource\ResourceFactory::class);
			try {
				return $resourceFactory->getFileObject($file);
			} catch (\Exception $e) {
				return false;
			}
		}
		if (is_string($file)) {
			$resourceFactory = GeneralUtility::makeInstance(Resource\ResourceFactory::class);
			try {
				return $resourceFactory->getFileObjectFromCombinedIdentifier($file);
			} catch (\Exception $e) {
				return false;
			}
		}
		if (is_object($file)) {
			if (get_class($file) === 'SMS\FluidComponents\Domain\Model\FalFile') {
				$file = $file->getFile();
			}
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
	 * Gets the thumbnail URL for online media files (YouTube/Vimeo)
	 *
	 * For YouTube:
	 * - Can use Usercentrics privacy proxy if useUcSource=true
	 * - Otherwise tries several thumbnail resolutions (maxres, hq, mq)
	 *
	 * For Vimeo:
	 * - Retrieves the large thumbnail from Vimeo API
	 *
	 * @param Resource\FileReference|Resource\File $file File or FileReference object
	 * @param array $arguments ViewHelper arguments
	 * @return string|null Thumbnail URL or null if not available
	 */
	protected static function getOnlineMediaImageSrc(Resource\FileReference|Resource\File $file, array $arguments = []): string|null
	{
		$id = $file->getContents();
		if ($file->getProperty('extension') === 'youtube') {
			if ($arguments['useUcSource']) {
				return "https://privacy-proxy-server.usercentrics.eu/video/youtube/{$id}-poster-image";
			}
			$resolutions = array('maxresdefault', 'hqdefault', 'mqdefault');
			foreach ($resolutions as $res) {
				$imgUrl = 'https://i.ytimg.com/vi/' . $file->getContents() . $res . '.jpg';
				if (@getimagesize(($imgUrl)))
					return $imgUrl;
			}
		}
		if ($file->getProperty('extension') === 'vimeo') {
			$data = file_get_contents('https://vimeo.com/api/v2/video/' . $file->getContents() . '.json');
			$data = json_decode($data);
			return $data[0]->{'thumbnail_large'};
		}
		return null;
	}
}