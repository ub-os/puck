<?php

namespace UBOS\Puck\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core;
use TYPO3\CMS\Fluid;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SendEmailFinisher extends AbstractFinisher
{

	const TEMPLATE_NAME = 'SendEmailFinisher';
	const SUBJECT_FALLBACK = 'TYPO3 Formal Finisher Email';
	const MAIL_FORMAT = Core\Mail\FluidEmail::FORMAT_BOTH;

	protected function executeInternal(): void
	{
		$this->settings = array_merge([
			'mailSubject' => '',
			'mailBody' => '',
			'mailTemplate' => '',
			'senderAddress' => '',
			'senderName' => '',
			'recipientAddress' => '',
			'recipientAddressField' => '',
		], $this->settings);

		$email = new Core\Mail\FluidEmail();
		$email
			->from($this->resolveSenderAddress())
			->to(...$this->resolveRecipientAddresses())
			->subject($this->settings['mailSubject'] ?: self::SUBJECT_FALLBACK)
			->setRequest($this->request)
			->format(self::MAIL_FORMAT)
			->setTemplate($this->settings['mailTemplate'] ?: self::TEMPLATE_NAME)
			->assignMultiple([
				'formValues' => $this->formValues,
				'formRecord' => $this->formRecord,
				'settings' => $this->settings,
				'interpolatedMailBody' => $this->interpolateStringWithFormValues($this->settings['mailBody']),
			]);
		GeneralUtility::makeInstance(Core\Mail\MailerInterface::class)->send($email);
	}

	protected function resolveSenderAddress(): Address
	{
		return new Address(
			$this->settings['senderAddress'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
			$this->settings['senderName'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);
	}

	protected function resolveRecipientAddresses(): array
	{
		$addresses = [];
		foreach (GeneralUtility::trimExplode(',', $this->settings['recipientAddress'], true) as $address) {
			$addresses[] = $address;
		}
		if ($this->settings['recipientAddressField'] && isset($this->formValues[$this->settings['recipientAddressField']])) {
			$addresses[] = $this->formValues[$this->settings['recipientAddressField']];
		}
		return $addresses;
	}

	protected function interpolateStringWithFormValues(string $string): string
	{
		foreach ($this->formValues as $key => $value) {
			$string = str_replace('{' . $key . '}', $value, $string);
		}
		return $string;
	}
}