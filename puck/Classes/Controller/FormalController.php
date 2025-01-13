<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puckloader\Attribute\Plugin;
use UBOS\Puck;


// todo: dispatch events
// todo: exceptions
// todo: consent finisher
// todo: file upload handling
// todo: captcha field
// todo: spam protection / honeypot
// todo: tca utlitity like addFinisherType, addFieldType
class FormalController extends ActionController
{
	use ContentControllerViewPreparationTrait;

	protected ?Core\Domain\Record $formRecord = null;

	#[Plugin(
		"FormalForm",
		actions: [FormalController::class =>
			'formalForm, formalSubmit, formalFinisher'],
		noCacheActions: [FormalController::class =>
			'formalSubmit, formalFinisher']
	)]
	public function formalFormAction(): ResponseInterface
	{
		$this->prepareContentView();
		$this->view->assign('form', $this->getFormRecord());
		$this->view->assign('formName', 'formData');
		return $this->htmlResponse();
	}

	public function formalSubmitAction(): ResponseInterface
	{
		$formValues = $this->request->getArguments()['formData'] ?? [];
		if (!$this->validateFormValues($formValues)) {
			return $this->errorResponse('Invalid form values');
		}
		return $this->redirect(
			'formalFinisher',
			arguments: ['formValues' => $formValues]
		);
	}


	// todo: validation
	protected function validateFormValues(array $formValues): bool
	{
		return true;
	}

	// todo: error responses
	protected function errorResponse(string $message): ResponseInterface
	{
		return $this->htmlResponse($message);
	}


	public function formalFinisherAction(array $formValues): ResponseInterface
	{
		$response = null;
		foreach ($this->getFinishers() as $finisherData) {
			// todo: add condition support
			if ($finisherData['condition'] ?? false) {
				continue;
			}
			try {
				$response = $this->makeFinisherInstance($finisherData)?->execute(
						$this->request,
						$finisherData,
						$this->getFormRecord(),
						$this->settings,
						$formValues
				) ?? $response;
			} catch (\Exception $e) {
				DebugUtility::debug($e);
				continue;
			}
		}
		return $response ?? $this->htmlResponse('finished');
	}

	protected function makeFinisherInstance(array $finisherData): ?Puck\Domain\Finisher\AbstractFinisher
	{
		$className = $finisherData['type'] ?? '';
		if (!$className || !class_exists($className)) {
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
		//$recordFactory = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class);
		//$finishers = [];
		//$finisherRecords[] = $recordFactory->createResolvedRecordFromDatabaseRow('tx_formal_finisher', $row);
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
