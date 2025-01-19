<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3\CMS\Extbase\Validation\Validator;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use UBOS\Puck;

// todo: condition support
// todo: validation
// todo: consent finisher
// todo: dispatch events
// todo: exceptions
// todo: captcha field
// todo: delete/move uploads finisher?
// note: upload and radio fields will not be in formValues if no value is set
class FormalController extends Extbase\Mvc\Controller\ActionController
{
	use ContentControllerViewPreparationTrait;

	protected ?Core\Domain\Record $formRecord = null;
	protected ?Core\Domain\Record $contentRecord = null;

	private string $formName = 'values';
	protected ?array $session = [
		'id' => '',
		'prevStep' => 1,
		'values' => [],
		'filenames' => [],
	];
	protected array $validationErrors = [];
	protected bool $hasValidationError = false;

	public function __construct(
		private readonly Core\Resource\StorageRepository $storageRepository,
	) {}


	protected function initializeSession(bool $validateAll = false): void
	{
		if (!isset($this->request->getArguments()[$this->formName])) {
			return;
		}
		$this->session = json_decode($this->request->getArguments()['session'] ?? '[]', true);
		$postValues = $this->request->getArguments()[$this->formName];
		$mergedValues = array_merge($this->session['values'], $postValues);
		$this->hasValidationError = !$this->validateFormValues($validateAll ? $mergedValues : $postValues);
		if ($this->hasValidationError) {
			return;
		}
		$this->session['values'] = $mergedValues;
	}

	public function formalFormAction(): ResponseInterface
	{
		$this->prepareContentView();
		$step = 1;
		$lastStep = count($this->getFormRecord()->get('steps'));
		$currentStepRecord = $this->getFormRecord()->get('steps')[$step - 1];
		$cObj = $this->request->getAttribute('currentContentObject');
		$viewVariables = [
			'session' => $this->session,
			'sessionJson' => json_encode($this->session),
			'form' => $this->getFormRecord(),
			'contentData' => $this->getContentRecord(),
			'formName' => $this->formName,
			'step' => $step,
			'nextStep' => $lastStep === $step ? null : $step + 1,
			'previousStep' => null,
			'isFirstStep' => true,
			'isLastStep' => $lastStep === $step,
			'currentStepRecord' => $currentStepRecord,
			'action' => $step < $lastStep ? 'formalFormStep' : 'formalSubmit',
		];
		$this->view->assignMultiple($viewVariables);
		return $this->htmlResponse();
	}


	protected function getSessionFileFolder(): string
	{
		if (!$this->session) {
			return '';
		}
		return 'user_upload/formal-' . $this->session['id'];
	}

	protected function getSessionKey(): string
	{
		return 'tx_formal_c' . $this->getContentRecord()->getUid() . '_f' . $this->getFormRecord()->getUid();
	}

	public function formalFormStepAction(int $step = 1): ResponseInterface
	{
		$this->initializeSession();
		// if step is greater than 0 and no previous step form values are available, redirect to first step
		if ($this->hasValidationError) {
			return $this->redirect('formalForm');
		}
		if (!$this->session['values']) {
			return $this->redirect('formalForm');
		}
		$this->session['id'] = $this->session['id'] ?: GeneralUtility::makeInstance(Core\Crypto\Random::class)->generateRandomHexString(20);

		$isStepBack = $this->session['prevStep'] ?? 1 > $step;

/*		foreach ($formValues as $fieldId => $value) {
			if (is_object($value) && get_class($value) === Core\Http\UploadedFile::class) {
				$this->saveUploadedFile($value, $fieldId);
				$this->session['values'][$fieldId] = '';
			}
		}*/


		$passwordHasher = GeneralUtility::makeInstance(Core\Crypto\PasswordHashing\PasswordHashFactory::class)->getDefaultHashInstance('FE');


		foreach($this->getFormRecord()->get('steps')[$this->session['prevStep']-1]->get('fields') as $field) {
			$id = $field->get('identifier');
			if (!isset($this->session['values'][$id])) {
				continue;
			}
			$value = $this->session['values'][$id];
			if (is_object($value) && get_class($value) === Core\Http\UploadedFile::class) {
				$this->saveUploadedFile($value, $id);
				$this->session['values'][$id] = '';
			}
			if ($field->get('type') === 'password') {
				//$this->session['values'][$id] = $passwordHasher->getHashedPassword($value);
			}
		}

		DebugUtility::debug($this->session['values']);
		$this->prepareContentView();
		$lastStep = count($this->getFormRecord()->get('steps'));
		$currentStepRecord = $this->getFormRecord()->get('steps')[$step - 1];

		foreach ($currentStepRecord->get('fields') as $field) {
			$id = $field->get('identifier');
			if (isset($this->session['values'][$id])) {
				$field->setValue($this->session['values'][$id]);
				unset($this->session['values'][$id]);
			}
			if ($field->get('type') === 'repeatable-container' && $field->getValue()) {
				$field->createRepeatableContainerPrevFields($field->getValue());
			}
		}

		// use fe_session to store form session?
		// how to handle garbage collection?
		//Core\Session\UserSessionManager::create('FE')->collectGarbage(10);
		//$this->getFrontendUser()->setKey('ses', $this->getSessionKey(), $this->session);
		//DebugUtility::debug($this->getFrontendUser()->getKey('ses', $this->getSessionKey()));

		$viewVariables = [
			'session' => $this->session,
			'sessionJson' => json_encode($this->session),
			'form' => $this->getFormRecord(),
			'contentData' => $this->getContentRecord(),
			'formName' => $this->formName,
			'step' => $step,
			'nextStep' => $lastStep === $step ? null : $step + 1,
			'previousStep' => $step - 1,
			'isFirstStep' => $step === 1,
			'isLastStep' => $step === $lastStep,
			'currentStepRecord' => $currentStepRecord,
			'action' => $step < $lastStep ? 'formalFormStep' : 'formalSubmit',
			//'currentStepFieldValues' => $currentStepFieldValues,
		];
		$this->view->assignMultiple($viewVariables);
		$this->view->setTemplate('formalForm');
		return $this->htmlResponse();
	}

	public function formalSubmitAction(): ResponseInterface
	{
		$this->initializeSession();
		if (!$this->session['values']) {
			return $this->redirect('formalForm');
		}
		if (!$this->validateFormValues($this->session['values'])) {
			return $this->errorResponse('Invalid form values');
		}
		foreach ($this->session['values'] as $identifier => $value) {
			if (is_object($value) && get_class($value) === Core\Http\UploadedFile::class) {
				$this->saveUploadedFile($value, $identifier);
			}
		}
		foreach ($this->session['filenames'] as $fieldId => $filename) {
			$this->session['values'][$fieldId] = $this->getSessionFileFolder() . '/' . $filename;
		}
		$response = null;
		foreach ($this->getFinishers() as $finisherData) {
			// todo: add condition support
			if ($finisherData['condition'] ?? false) {
				continue;
			}
			try {
				$response = $this->makeFinisherInstance($finisherData, $this->session['values'])?->execute() ?? $response;
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

	protected function validateFields(array $values, array $fields): bool
	{
		$valid = true;
		$validationErrors = [];
		foreach ($fields as $field) {
			$type = $field->get('type');
			$id = $field->get('identifier');
			$value = $values[$id] ?? null;
			if ($type === 'file') {
				continue;
			}
			if ($field->get('pattern')) {
				if ($this->hasValidationError(
					Validator\RegexValidator::class,
					['regularExpression' => $field->get('pattern')],
					$value)) {
					$valid = false;
				}
			}
			if ($field->get('required')) {
				if ($this->hasValidationError(
					Validator\NotEmptyValidator::class,
					[],
					$value)) {
					$valid = false;
				}
			}
			if ($type === 'email') {
				if ($this->hasValidationError(
					Validator\EmailAddressValidator::class,
					[],
					$value)) {
					$valid = false;
				}
			}
			if ($field->get('accept')) {
				if ($this->hasValidationError(
					Validator\MimeTypeValidator::class,
					['allowedMimeTypes' => explode(',', $field->get('accept'))],
					$value)) {
					$valid = false;
				}

			}
			if ($field->get('maxlength')) {
				if ($this->hasValidationError(
					Validator\StringLengthValidator::class,
					['maximum' => $field->get('maxlength')],
					$value)) {
					$valid = false;
				}
			}
			if ($type === 'url') {
				if ($this->hasValidationError(
					Validator\UrlValidator::class,
					[],
					$value)) {
					$valid = false;
				}
			}
			if ($type === 'number') {
				if ($this->hasValidationError(
					Validator\NumberValidator::class,
					[],
					$value)) {
					$valid = false;
				}
			}

		}
		return true;
	}


	protected function hasValidationError(string $validator, array $options, mixed $value): bool
	{
		$validator = GeneralUtility::makeInstance($validator);
		$validator->setOptions($options);
		return $validator->validate($value)->hasError();
	}

	// todo: error responses
	protected function errorResponse(string $message): ResponseInterface
	{
		return $this->htmlResponse($message);
	}

	protected function makeFinisherInstance(array $finisherData, $formValues): ?Puck\Domain\Finisher\AbstractFinisher
	{
		$className = $finisherData['type'] ?? '';
		if (!$className || !class_exists($className)) {
			return null;
		}
		return GeneralUtility::makeInstance(
			$className,
			$this->request,
			$finisherData,
			$this->settings,
			$this->getFormRecord(),
			$formValues);
	}

	protected function saveUploadedFile(Core\Http\UploadedFile $file, string $fieldId): void
	{
		$storage = $this->storageRepository->getDefaultStorage();
		$folderId = $this->getSessionFileFolder();
		$fileName = $file->getClientFilename();
		if (!$storage->hasFolder($folderId)) {
			$storage->createFolder($folderId);
		}
		$newFile = $storage->addUploadedFile(
			$file,
			$storage->getFolder($folderId),
			$fileName,
			Core\Resource\Enum\DuplicationBehavior::RENAME
		);
		if (!isset($this->session['filenames'])) {
			$this->session['filenames'] = [];
		}
		$this->session['filenames'][$fieldId] = $newFile->getName();
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
	protected function getContentRecord(): Core\Domain\Record
	{
		if ($this->contentRecord) {
			return $this->contentRecord;
		}
		$cObj = $this->request->getAttribute('currentContentObject');
		$this->contentRecord = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tt_content', $cObj->data);
		return $this->contentRecord;
	}

	protected function getFrontendUser(): Frontend\Authentication\FrontendUserAuthentication
	{
		return $this->request->getAttribute('frontend.user');
	}
}
