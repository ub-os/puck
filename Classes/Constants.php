<?php

namespace UBOS\Puck;

use TYPO3\CMS\Core\Domain\Repository\PageRepository;


/**
 * Constants for the extension
 */
class Constants
{
	public const array DOKTYPES = [
		'default' => PageRepository::DOKTYPE_DEFAULT,
		'shortcut' => PageRepository::DOKTYPE_SHORTCUT,
		'link' => PageRepository::DOKTYPE_LINK,
		'sysfolder' => PageRepository::DOKTYPE_SYSFOLDER,
		'mountpoint' => PageRepository::DOKTYPE_MOUNTPOINT,
		'spacer' => PageRepository::DOKTYPE_SPACER,
		'news' => 16503,
	];

	public static function toArray(): array
	{
		return [
			'doktypes' => self::DOKTYPES,
		];
	}
}