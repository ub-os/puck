<?php

namespace UBOS\Puck\Domain\Finisher;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core;
use TYPO3\CMS\Fluid;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SendEmailFinisher extends AbstractFinisher
{

	const TEMPLATE_NAME = 'SendEmailFinisher';
	const SUBJECT_FALLBACK = 'TYPO3 Formal Finisher Email';

	protected function executeInternal(): void
	{
		$this->settings = array_merge([
			'mailSubject' => '',
			'mailBody' => '',
			'senderAddress' => '',
			'senderName' => '',
			'recipientAddress' => '',
			'recipientAddressField' => '',
		], $this->settings);

		Core\Utility\DebugUtility::debug($this);
		$email = new Core\Mail\FluidEmail();
		$email
			->from($this->getSenderAddress())
			->to(...$this->getRecipientAddresses())
			->subject($this->settings['mailSubject'] ?: self::SUBJECT_FALLBACK)
			->format(Core\Mail\FluidEmail::FORMAT_BOTH)
			->setTemplate(self::TEMPLATE_NAME)
			->assignMultiple([
				'formValues' => $this->formValues,
				'formRecord' => $this->formRecord,
				'settings' => $this->settings,
				'renderedMailBody' => $this->renderMailBody(),
			]);
		GeneralUtility::makeInstance(Core\Mail\MailerInterface::class)->send($email);
	}

	protected function getSenderAddress(): Address
	{
		return new Address(
			$this->settings['senderAddress'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
			$this->settings['senderName'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);
	}

	protected function getRecipientAddresses(): array
	{
		$addresses = [];
		foreach (GeneralUtility::trimExplode(',', $this->settings['recipientAddress'], true) as $address) {
			$addresses[] = $address;
		}
		if ($this->settings['recipientAddressField'] && $this->formValues[$this->settings['recipientAddressField']]) {
			$addresses[] = $this->formValues[$this->settings['recipientAddressField']];
		}
		return $addresses;
	}

	protected function renderMailBody(): string
	{
		$view = GeneralUtility::makeInstance(Fluid\View\StandaloneView::class);
		$view
			->setTemplateSource($this->settings['mailBody'])
			->assignMultiple($this->formValues);
		return html_entity_decode($view->render(), ENT_QUOTES, 'UTF-8');
	}
}