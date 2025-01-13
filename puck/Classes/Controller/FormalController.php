<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puckloader\Attribute\Plugin;

class FormalController extends ActionController
{
	use ContentControllerViewPreparationTrait;

	protected ?Core\Domain\Record $formRecord = null;
	#[Plugin(
		"FormalForm",
		actions: [FormalController::class => 'formalForm, formalSubmit'],
		noCacheActions: [FormalController::class => 'formalSubmit']
	)]
	public function formalFormAction(): ResponseInterface
	{
		$this->prepareContentView();
		$this->view->assign('form', $this->getFormRecord());
		$this->view->assign('formName', 'formData');
		DebugUtility::debug($this->getFinishers());
		DebugUtility::debug($this->request);
		return $this->htmlResponse();
	}

	public function formalSubmitAction(): ResponseInterface
	{
		DebugUtility::debug($this->request);
		$formValues = $this->request->getArguments()['formData'] ?? [];
		$finishers = $this->getFinishers();
		foreach ($finishers as $finisher) {
			try {
				$this->makeFinisherInstance($finisher['type'])
					?->execute($this->request, $this->settings, $finisher['settings'], $formValues, $this->getFormRecord());

			} catch (\Exception $e) {
				DebugUtility::debug($e);
				continue;
			}
		}
		DebugUtility::debug($formValues);
		return $this->htmlResponse('submitti');
	}

	protected function makeFinisherInstance(string $className): ?\UBOS\Puck\Domain\Finisher\AbstractFinisher
	{
		if (!class_exists($className)) {
			return null;
		}
		return GeneralUtility::makeInstance($className);
	}

	protected function getFinishers(): array
	{
		$cObj = $this->request->getAttribute('currentContentObject');
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_formal_finisher');
		$finishers = $queryBuilder
			->select('*')->from('tx_formal_finisher')
			->where(
				$queryBuilder->expr()->eq('plugin_uid', $cObj->data['uid']),
				$queryBuilder->expr()->eq('hidden', 0),
				$queryBuilder->expr()->eq('deleted', 0),
			)
			->executeQuery()->fetchAllAssociative() ?? [];
		foreach ($finishers as $key => $finisher) {
			$finishers[$key]['settings'] = GeneralUtility::makeInstance(Core\Service\FlexFormService::class)
				->convertFlexFormContentToArray($finishers[$key]['settings']);
		}
		return $finishers;
	}

	protected function getFormRecord(): ?Core\Domain\Record
	{
		if ($this->formRecord) {
			return $this->formRecord;
		}
		$langId = (int)$this->request->getAttribute('language')->getLanguageId();
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_formal_form');
		$row = $queryBuilder
			->select('*')->from('tx_formal_form')
			->where(
				$queryBuilder->expr()->eq('uid', (int)$this->settings['form']),
				$queryBuilder->expr()->eq('hidden', 0),
				$queryBuilder->expr()->eq('deleted', 0),
				$queryBuilder->expr()->eq('sys_language_uid', $langId),
			)
			->executeQuery()->fetchAllAssociative()[0];
		$recordFactory = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class);
		$this->formRecord = $recordFactory->createResolvedRecordFromDatabaseRow('tx_formal_form', $row);
		return $this->formRecord;
	}
}
