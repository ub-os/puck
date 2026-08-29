<?php

declare(strict_types=1);

namespace UBOS\Puck\Security\Csp;

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceScheme;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;

/**
 * Parses the line based DSL entered in the "Content-Security-Policy rules" site
 * configuration field into a list of core CSP mutations.
 *
 * Syntax (one rule per line):
 *
 *   <directive> [<mode>] <source> [<source> ...]
 *
 * - <directive>  a CSP directive, e.g. script-src, img-src, frame-src
 * - <mode>       optional, one of: extend (default), append, set, remove, reduce, inherit
 * - <source>     'self', 'none', 'unsafe-inline', a scheme like data:, or a host / URL
 *
 * Lines that are empty or start with "#" are ignored.
 */
final class CspRuleParser
{
	private const MODES = [
		'extend' => MutationMode::Extend,
		'append' => MutationMode::Append,
		'set' => MutationMode::Set,
		'remove' => MutationMode::Remove,
		'reduce' => MutationMode::Reduce,
		'inherit' => MutationMode::InheritOnce,
	];

	/**
	 * @return list<Mutation>
	 * @throws CspRuleException on unknown directive / mode
	 */
	public function parse(string $input): array
	{
		$mutations = [];
		foreach ($this->splitLines($input) as $lineNumber => $line) {
			$mutation = $this->parseLine($line, $lineNumber);
			if ($mutation !== null) {
				$mutations[] = $mutation;
			}
		}
		return $mutations;
	}

	/**
	 * @return array<int, string> map of 1-indexed line number => trimmed, non-empty, non-comment line
	 */
	private function splitLines(string $input): array
	{
		$lines = [];
		foreach (preg_split('/\R/', $input) ?: [] as $index => $raw) {
			$line = trim((string)$raw);
			if ($line === '' || str_starts_with($line, '#')) {
				continue;
			}
			$lines[$index + 1] = $line;
		}
		return $lines;
	}

	private function parseLine(string $line, int $lineNumber): ?Mutation
	{
		$tokens = preg_split('/\s+/', $line) ?: [];
		$directiveToken = array_shift($tokens);
		if ($directiveToken === null || $directiveToken === '') {
			return null;
		}

		$directive = Directive::tryFrom(strtolower($directiveToken));
		if ($directive === null) {
			throw new CspRuleException(sprintf(
				'Line %d: unknown CSP directive "%s".',
				$lineNumber,
				$directiveToken
			));
		}

		$mode = MutationMode::Extend;
		if ($tokens !== [] && isset(self::MODES[strtolower($tokens[0])])) {
			$mode = self::MODES[strtolower((string)array_shift($tokens))];
		}

		if ($mode === MutationMode::Remove) {
			return new Mutation($mode, $directive);
		}

		$sources = array_map($this->parseSource(...), $tokens);
		if ($sources === []) {
			throw new CspRuleException(sprintf(
				'Line %d: directive "%s" requires at least one source (or use mode "remove").',
				$lineNumber,
				$directiveToken
			));
		}

		return new Mutation($mode, $directive, ...$sources);
	}

	private function parseSource(string $token): SourceKeyword|SourceScheme|UriValue
	{
		$unquoted = trim($token, "'\"");

		if ($unquoted === 'nonce-proxy') {
			return SourceKeyword::nonceProxy;
		}

		$keyword = SourceKeyword::tryFrom($unquoted);
		if ($keyword !== null) {
			return $keyword;
		}

		if (str_ends_with($token, ':')) {
			$scheme = SourceScheme::tryFrom(rtrim($token, ':'));
			if ($scheme !== null) {
				return $scheme;
			}
		}

		return new UriValue($token);
	}
}
