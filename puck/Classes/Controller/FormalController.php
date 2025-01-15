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


// todo: file upload handling
// todo: spam protection / honeypot
// todo: password field handling
// todo: consent finisher
// todo: repeatable fields
// todo: dispatch events
// todo: exceptions
// todo: captcha field
class FormalController extends ActionController
{
	use ContentControllerViewPreparationTrait;

	protected ?Core\Domain\Record $formRecord = null;
	private string $formName = 'formalFormValues';
	protected ?array $formValues = null;

	public function formalFormAction(): ResponseInterface
	{
		$this->prepareContentView();
		$step = 1;
		$lastStep = count($this->getFormRecord()->get('steps'));
		$currentStepRecord = $this->getFormRecord()->get('steps')[$step - 1];
		$cObj = $this->request->getAttribute('currentContentObject');
		$contentRecord = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tt_content', $cObj->data);
		$viewVariables = [
			'form' => $this->getFormRecord(),
			'content' => $contentRecord,
			'formName' => $this->formName,
			'step' => $step,
			'nextStep' => $lastStep === $step ? null : $step + 1,
			'previousStep' => null,
			'isFirstStep' => true,
			'isLastStep' => $lastStep === $step,
			'currentStepRecord' => $currentStepRecord,
			'action' => $step < $lastStep ? 'formalFormStep' : 'formalSubmit',
			'otherStepsFieldValues' => [],
		];
		$this->view->assignMultiple($viewVariables);
		return $this->htmlResponse();
	}

	public function formalFormStepAction(int $step = 1): ResponseInterface
	{
		$formValues = $this->request->getArguments()[$this->formName] ?? [];
		// if step is greater than 0 and no previous step form values are available, redirect to first step
		if (!$formValues) {
			return $this->redirect('formalForm');
		}
		$this->prepareContentView();
		$lastStep = count($this->getFormRecord()->get('steps'));
		$currentStepRecord = $this->getFormRecord()->get('steps')[$step - 1];
		foreach ($currentStepRecord->get('fields') as $field) {
			$identifier = $field->get('identifier');
			if (isset($formValues[$identifier])) {
				$field->setValue($formValues[$identifier]);
				unset($formValues[$identifier]);
			}
		}
		$otherStepsFieldValues = $formValues;
		$cObj = $this->request->getAttribute('currentContentObject');
		$contentRecord = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tt_content', $cObj->data);
		$viewVariables = [
			'form' => $this->getFormRecord(),
			'content' => $contentRecord,
			'formName' => $this->formName,
			'step' => $step,
			'nextStep' => $lastStep === $step ? null : $step + 1,
			'previousStep' => $step - 1,
			'isFirstStep' => $step === 1,
			'isLastStep' => $step === $lastStep,
			'currentStepRecord' => $currentStepRecord,
			'action' => $step < $lastStep ? 'formalFormStep' : 'formalSubmit',
			'otherStepsFieldValues' => $otherStepsFieldValues,
			//'currentStepFieldValues' => $currentStepFieldValues,
		];
		$this->view->assignMultiple($viewVariables);
		$this->view->setTemplate('formalForm');
		return $this->htmlResponse();
	}

	public function formalSubmitAction(): ResponseInterface
	{
		$formValues = $this->request->getArguments()[$this->formName] ?? [];
		if (!$this->validateFormValues($formValues)) {
			return $this->errorResponse('Invalid form values');
		}
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
		$this->formRecord = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tx_formal_form', $row);
		return $this->formRecord;
	}
}
