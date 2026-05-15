<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core;
class TranslationUidViewHelper extends AbstractViewHelper
{

	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('uid', 'integer', '', true);
		$this->registerArgument('table', 'string', '', false, 'tt_content');
		$this->registerArgument('languageParentField', 'string', '', false, 'l18n_parent');
	}

	public function render(): ?int
	{
		// check if language id is not 0, if it is, return the original uid, otherwise look for the translated element in db
		$context = Core\Utility\GeneralUtility::makeInstance(Core\Context\Context::class);
		$lang = $context->getPropertyFromAspect('language', 'id');
		if ($lang) {
			$translatedUid = Core\Utility\GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
				->getConnectionForTable($this->arguments['table'])
				->select(
					columns: ['uid'],
					tableName: $this->arguments['table'],
					identifiers: [
						$this->arguments['languageParentField'] => $this->arguments['uid'],
						'sys_language_uid' => $lang
					]
				)
				->fetchAssociative();
			return $translatedUid['uid'] ?: $this->arguments['uid'];
		} else {
			return $this->arguments['uid'];
		}
	}
}