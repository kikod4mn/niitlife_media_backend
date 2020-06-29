<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use Crudle\Profanity\Dictionary\GB;
use Crudle\Profanity\Dictionary\US;
use Crudle\Profanity\Filter;
use HTMLPurifier;
use HTMLPurifier_Config;

trait FilterProfanitiesTrait
{
	/**
	 * Clean the string from html and profanities. No validation, just cleaning.
	 * @param  string  $text
	 * @return string
	 */
	protected function cleanString(string $text): string
	{
		if (! $this->containsProfanities($text)) {
			
			return $text;
		}
		
		return $this->filterHTML(
			$this->filterProfanities(
				$text
			)
		);
	}
	
	/**
	 * Filter out HTML tags, allowed tags are kept.
	 * @param  string  $text
	 * @return string
	 */
	protected function filterHTML(string $text): string
	{
		return $this->htmlPurifier()->purify($text);
	}
	
	/**
	 * Return a new html purifier with configuration.
	 * @return HTMLPurifier
	 */
	protected function htmlPurifier(): HTMLPurifier
	{
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.Allowed', $this->allowedHtmlTags());
		
		return new HTMLPurifier($config);
	}
	
	/**
	 * Get the custom allowed tags with defaults or return just defaults.
	 * @return string
	 */
	protected function allowedHtmlTags(): string
	{
		return defined('static::HTML_TAGS_ALLOWED')
			? implode(',', static::HTML_TAGS_ALLOWED) . ',' . static::DEFAULT_HTML_TAGS
			: static::DEFAULT_HTML_TAGS;
	}
	
	/**
	 * Cleans the string of profanities. Does not validate, just cleans.
	 * @param  string  $text
	 * @return string
	 */
	protected function filterProfanities(string $text): string
	{
		foreach ($this->profanityDictionaries() as $dict) {
			
			$text = (new Filter($dict))->cleanse($text);
		}
		
		return $text;
	}
	
	/**
	 * Determine if the string contains profanities. Only checks, does not cleanse.
	 * @param  string  $text
	 * @return bool
	 */
	protected function containsProfanities(string $text): bool
	{
		foreach ($this->profanityDictionaries() as $dict) {
			
			if ((new Filter($dict))->isDirty($text)) {
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Return custom filters together with defaults or just the defaults.
	 * @return array
	 */
	protected function profanityDictionaries(): array
	{
		$defaults = [new GB(), new US()];
		
		return defined('static::PROFANITY_FILTERS') ? array_merge(static::PROFANITY_FILTERS, $defaults) : $defaults;
	}
}