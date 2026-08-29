<?php

declare(strict_types=1);

namespace UBOS\Puck\Security\Csp;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\SiteConfigurationBeforeWriteEvent;
use TYPO3\CMS\Core\Configuration\Exception\SiteConfigurationWriteException;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Disposition;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Event\PolicyMutatedEvent;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Event\PolicyPreparedEvent;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Policy;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Wires the "Tracking & Security" site configuration fields into the frontend
 * Content-Security-Policy, so administrators configure CSP entirely from the
 * Sites module and never touch csp.yaml:
 *
 *  - puck_csp_mode (off | report | enforce)
 *    csp.yaml declares both an "enforce:" and a "report:" disposition; the
 *    policy of whichever disposition(s) the mode does not want is emptied so the
 *    corresponding HTTP header is not sent.
 *
 *  - puck_csp_allow_inline_scripts
 *    adds 'unsafe-inline' to script-src / style-src and disables hash mode (the
 *    two are mutually exclusive - browsers ignore 'unsafe-inline' once a hash is
 *    present).
 *
 *  - puck_csp_rules
 *    line based directive mutations applied on top of everything else; validated
 *    on save, tolerated (logged and skipped) at runtime.
 */
final class SiteConfigContentSecurityPolicy
{
	public function __construct(
		private readonly CspRuleParser $parser,
		private readonly LoggerInterface $logger,
	) {}

	/**
	 * puck_csp_rules -> late policy mutations. Runs per disposition; supports
	 * "set" / "remove", so it can override or strip the core baseline.
	 */
	#[AsEventListener]
	public function applyRules(PolicyMutatedEvent $event): void
	{
		$site = $this->resolveFrontendSite($event->scope, $event->request);
		if ($site === null) {
			return;
		}

		$rules = trim((string)($site->getConfiguration()['puck_csp_rules'] ?? ''));
		if ($rules === '') {
			return;
		}

		try {
			$mutations = $this->parser->parse($rules);
		} catch (CspRuleException $exception) {
			// Invalid rules are rejected on save; if one slips through (e.g. an
			// imported config.yaml) we must not break the frontend response.
			$this->logger->warning('Ignoring invalid puck_csp_rules for site "{site}": {message}', [
				'site' => $site->getIdentifier(),
				'message' => $exception->getMessage(),
			]);
			return;
		}

		if ($mutations !== []) {
			$event->setCurrentPolicy($event->getCurrentPolicy()->mutate(...$mutations));
		}
	}

	/**
	 * puck_csp_mode + puck_csp_allow_inline_scripts -> disposition selection and
	 * inline script handling.
	 */
	#[AsEventListener]
	public function applyBehavior(PolicyPreparedEvent $event): void
	{
		$policyBag = $event->policyBag;
		$site = $this->resolveFrontendSite($policyBag->scope, $event->request);
		if ($site === null) {
			return;
		}

		$config = $site->getConfiguration();
		$mode = (string)($config['puck_csp_mode'] ?? 'report');
		$allowInlineScripts = (bool)($config['puck_csp_allow_inline_scripts'] ?? false);

		foreach ($policyBag->dispositionMap as $disposition => $_) {
			if (!$this->keepsDisposition($mode, $disposition)) {
				if (!$policyBag->getPolicy($disposition)->isEmpty()) {
					$policyBag->setPolicy($disposition, new Policy());
				}
				continue;
			}
			if ($allowInlineScripts) {
				$policyBag->setPolicy(
					$disposition,
					$this->withInlineScriptsAllowed($policyBag->getPolicy($disposition))
				);
			}
		}

		if ($allowInlineScripts) {
			// Hash sources and 'unsafe-inline' are mutually exclusive per the CSP spec.
			$policyBag->behavior->useHash = false;
		}
	}

	/**
	 * Rejects a site configuration save when puck_csp_rules does not parse, with
	 * a backend flash message instead of a silently broken policy.
	 */
	#[AsEventListener]
	public function validateRules(SiteConfigurationBeforeWriteEvent $event): void
	{
		$rules = trim((string)($event->getConfiguration()['puck_csp_rules'] ?? ''));
		if ($rules === '') {
			return;
		}

		try {
			$this->parser->parse($rules);
		} catch (CspRuleException $exception) {
			throw new SiteConfigurationWriteException(
				'Content-Security-Policy rules could not be saved. ' . $exception->getMessage(),
				1756300001,
				$exception
			);
		}
	}

	private function resolveFrontendSite(Scope $scope, ?ServerRequestInterface $request): ?Site
	{
		if (!$scope->isFrontendSite()) {
			return null;
		}
		$site = $scope->site ?? $request?->getAttribute('site');
		return $site instanceof Site ? $site : null;
	}

	private function keepsDisposition(string $mode, Disposition $disposition): bool
	{
		return match ($mode) {
			'enforce' => $disposition === Disposition::enforce,
			'report' => $disposition === Disposition::report,
			default => false,
		};
	}

	private function withInlineScriptsAllowed(Policy $policy): Policy
	{
		if (
			$policy->isEmpty()
			|| $policy->containsDirective(Directive::ScriptSrc, SourceKeyword::unsafeInline)
		) {
			return $policy;
		}
		return $policy->mutate(
			new Mutation(MutationMode::Extend, Directive::ScriptSrc, SourceKeyword::unsafeInline),
			new Mutation(MutationMode::Extend, Directive::StyleSrc, SourceKeyword::unsafeInline),
		);
	}
}
