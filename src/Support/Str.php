<?php

declare(strict_types = 1);

namespace App\Support;

use DOMDocument;
use Exception;
use HtmlSanitizer\Sanitizer;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;

/**
 * Modified Str class from Laravel.
 * Laravel - A PHP Framework For Web Artisans
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */
class Str
{
	/**
	 * The cache of snake-cased words.
	 * @var array
	 */
	protected static $snakeCache = [];
	
	/**
	 * The cache of camel-cased words.
	 * @var array
	 */
	protected static $camelCache = [];
	
	/**
	 * The cache of studly-cased words.
	 * @var array
	 */
	protected static $studlyCache = [];
	
	/**
	 * The callback that should be used to generate UUIDs.
	 * @var callable
	 */
	protected static $uuidFactory;
	
	/**
	 * @param  string  $text
	 * @return string
	 */
	public static function removeTrailingSlashes(string $text): string
	{
		return ltrim(rtrim($text, '/\\'));
	}
	
	/**
	 * Return the remainder of a string after the first occurrence of a given value.
	 * @param  string  $subject
	 * @param  string  $search
	 * @return string
	 */
	public static function after(string $subject, string $search): string
	{
		return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
	}
	
	/**
	 * Return the remainder of a string after the last occurrence of a given value.
	 * @param  string  $subject
	 * @param  string  $search
	 * @return string
	 */
	public static function afterLast(string $subject, string $search): string
	{
		if ($search === '') {
			return $subject;
		}
		
		$position = strrpos($subject, $search);
		
		if ($position === false) {
			return $subject;
		}
		
		return substr($subject, $position + strlen($search));
	}
	
	/**
	 * Get the portion of a string before the first occurrence of a given value.
	 * @param  string  $subject
	 * @param  string  $search
	 * @return string
	 */
	public static function before(string $subject, string $search): string
	{
		return $search === '' ? $subject : explode($search, $subject)[0];
	}
	
	/**
	 * Get the portion of a string before the last occurrence of a given value.
	 * @param  string  $subject
	 * @param  string  $search
	 * @return string
	 */
	public static function beforeLast(string $subject, string $search): string
	{
		if ($search === '') {
			return $subject;
		}
		
		$pos = mb_strrpos($subject, $search);
		
		if ($pos === false) {
			return $subject;
		}
		
		return static::substr($subject, 0, $pos);
	}
	
	/**
	 * Get the portion of a string between two given values.
	 * @param  string  $subject
	 * @param  string  $from
	 * @param  string  $to
	 * @return string
	 */
	public static function between(string $subject, string $from, string $to): string
	{
		if ($from === '' || $to === '') {
			return $subject;
		}
		
		return static::beforeLast(static::after($subject, $from), $to);
	}
	
	/**
	 * Convert a value to camel case.
	 * @param  string  $value
	 * @return string
	 */
	public static function camel(string $value): string
	{
		if (isset(static::$camelCache[$value])) {
			return static::$camelCache[$value];
		}
		
		return static::$camelCache[$value] = lcfirst(static::studly($value));
	}
	
	/**
	 * Determine if a given string contains a given substring.
	 * @param  string           $haystack
	 * @param  string|string[]  $needles
	 * @return bool
	 */
	public static function contains(string $haystack, $needles): bool
	{
		foreach ((array) $needles as $needle) {
			if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Determine if a given string contains all array values.
	 * @param  string    $haystack
	 * @param  string[]  $needles
	 * @return bool
	 */
	public static function containsAll(string $haystack, array $needles): bool
	{
		foreach ($needles as $needle) {
			if (! static::contains($haystack, $needle)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Determine if a given string ends with a given substring.
	 * @param  string           $haystack
	 * @param  string|string[]  $needles
	 * @return bool
	 */
	public static function endsWith(string $haystack, $needles): bool
	{
		foreach ((array) $needles as $needle) {
			if (substr($haystack, -strlen($needle)) === (string) $needle) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Cap a string with a single instance of a given value.
	 * @param  string  $value
	 * @param  string  $cap
	 * @return string
	 */
	public static function finish(string $value, string $cap): string
	{
		$quoted = preg_quote($cap, '/');
		
		return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $cap;
	}
	
	/**
	 * Determine if a given string matches a given pattern.
	 * @param  string|array  $pattern
	 * @param  string        $value
	 * @return bool
	 */
	public static function is($pattern, string $value): bool
	{
		$patterns = Arr::wrap($pattern);
		
		if (empty($patterns)) {
			return false;
		}
		
		foreach ($patterns as $pattern) {
			// If the given value is an exact match we can of course return true right
			// from the beginning. Otherwise, we will translate asterisks and do an
			// actual pattern match against the two strings to see if they match.
			if ($pattern == $value) {
				return true;
			}
			
			$pattern = preg_quote($pattern, '#');
			
			// Asterisks are translated into zero-or-more regular expression wildcards
			// to make it convenient to check if the strings starts with the given
			// pattern such as "library/*", making any string check convenient.
			$pattern = str_replace('\*', '.*', $pattern);
			
			if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Determine if a given string is a valid UUID.
	 * @param  string  $value
	 * @return bool
	 */
	public static function isUuid(string $value): bool
	{
		if (! is_string($value)) {
			return false;
		}
		
		return preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/iD', $value) > 0;
	}
	
	/**
	 * Convert a string to kebab case.
	 * @param  string  $value
	 * @return string
	 */
	public static function kebab(string $value): string
	{
		return static::snake($value, '-');
	}
	
	/**
	 * Return the length of the given string.
	 * @param  string       $value
	 * @param  string|null  $encoding
	 * @return int
	 */
	public static function length(string $value, $encoding = null): int
	{
		if ($encoding) {
			return mb_strlen($value, $encoding);
		}
		
		return mb_strlen($value);
	}
	
	/**
	 * Limit the number of characters in a string.
	 * @param  string  $value
	 * @param  int     $limit
	 * @param  string  $end
	 * @return string
	 */
	public static function limit(string $value, int $limit = 100, string $end = '...'): string
	{
		if (mb_strwidth($value, 'UTF-8') <= $limit) {
			return $value;
		}
		
		return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
	}
	
	/**
	 * Convert the given string to lower-case.
	 * @param  string  $value
	 * @return string
	 */
	public static function lower(string $value): string
	{
		return mb_strtolower($value, 'UTF-8');
	}
	
	/**
	 * Limit the number of words in a string.
	 * @param  string  $value
	 * @param  int     $words
	 * @param  string  $end
	 * @return string
	 */
	public static function words(string $value, int $words = 100, string $end = '...'): string
	{
		preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches);
		
		if (! isset($matches[0]) || static::length($value) === static::length($matches[0])) {
			return $value;
		}
		
		return rtrim($matches[0]) . $end;
	}
	
	/**
	 * Parse a Class[@]method style callback into class and method.
	 * @param  string       $callback
	 * @param  string|null  $default
	 * @return array<int, string|null>
	 */
	public static function parseCallback(string $callback, string $default = null)
	{
		return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
	}
	
	/**
	 * Get the plural form of an English word.
	 * @param  string  $value
	 * @param  int     $count
	 * @return string
	 */
	public static function plural(string $value, int $count = 2): string
	{
		return Pluralizer::plural($value, $count);
	}
	
	/**
	 * Pluralize the last word of an English, studly caps case string.
	 * @param  string  $value
	 * @param  int     $count
	 * @return string
	 */
	public static function pluralStudly(string $value, int $count = 2): string
	{
		$parts = preg_split('/(.)(?=[A-Z])/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		$lastWord = array_pop($parts);
		
		return implode('', $parts) . self::plural($lastWord, $count);
	}
	
	/**
	 * Generate a more truly "random" alpha-numeric string.
	 * @param  int  $length
	 * @return string
	 * @throws Exception
	 */
	public static function random(int $length = 16): string
	{
		$string = '';
		
		while (($len = strlen($string)) < $length) {
			$size = $length - $len;
			
			$bytes = random_bytes($size);
			
			$string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
		}
		
		return $string;
	}
	
	/**
	 * Replace a given value in the string sequentially with an array.
	 * @param  string                     $search
	 * @param  array<int|string, string>  $replace
	 * @param  string                     $subject
	 * @return string
	 */
	public static function replaceArray(string $search, array $replace, string $subject): string
	{
		$segments = explode($search, $subject);
		
		$result = array_shift($segments);
		
		foreach ($segments as $segment) {
			$result .= (array_shift($replace) ?? $search) . $segment;
		}
		
		return $result;
	}
	
	/**
	 * Replace the first occurrence of a given value in the string.
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $subject
	 * @return string
	 */
	public static function replaceFirst(string $search, string $replace, string $subject): string
	{
		if ($search == '') {
			return $subject;
		}
		
		$position = strpos($subject, $search);
		
		if ($position !== false) {
			return substr_replace($subject, $replace, $position, strlen($search));
		}
		
		return $subject;
	}
	
	/**
	 * Replace the last occurrence of a given value in the string.
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $subject
	 * @return string
	 */
	public static function replaceLast(string $search, string $replace, string $subject): string
	{
		$position = strrpos($subject, $search);
		
		if ($position !== false) {
			return substr_replace($subject, $replace, $position, strlen($search));
		}
		
		return $subject;
	}
	
	/**
	 * Begin a string with a single instance of a given value.
	 * @param  string  $value
	 * @param  string  $prefix
	 * @return string
	 */
	public static function start(string $value, string $prefix): string
	{
		$quoted = preg_quote($prefix, '/');
		
		return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
	}
	
	/**
	 * Convert the given string to upper-case.
	 * @param  string  $value
	 * @return string
	 */
	public static function upper(string $value): string
	{
		return mb_strtoupper($value, 'UTF-8');
	}
	
	/**
	 * Convert the given string to title case.
	 * @param  string  $value
	 * @return string
	 */
	public static function title(string $value): string
	{
		return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
	}
	
	/**
	 * Get the singular form of an English word.
	 * @param  string  $value
	 * @return string
	 */
	public static function singular(string $value): string
	{
		return Pluralizer::singular($value);
	}
	
	/**
	 * Generate a URL friendly "slug" from a given string.
	 * @param  string  $title
	 * @param  string  $separator
	 * @return string
	 */
	public static function slug(string $title, string $separator = '-'): string
	{
		// Convert all dashes/underscores into separator
		$flip = $separator === '-' ? '_' : '-';
		
		$title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);
		
		// Replace @ with the word 'at'
		$title = str_replace('@', $separator . 'at' . $separator, $title);
		
		// Remove all characters that are not the separator, letters, numbers, or whitespace.
		$title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', static::lower($title));
		
		// Replace all separator characters and whitespace by a single separator
		$title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);
		
		return trim($title, $separator);
	}
	
	/**
	 * Convert a string to snake case.
	 * @param  string  $value
	 * @param  string  $delimiter
	 * @return string
	 */
	public static function snake(string $value, string $delimiter = '_'): string
	{
		$key = $value;
		
		if (isset(static::$snakeCache[$key][$delimiter])) {
			return static::$snakeCache[$key][$delimiter];
		}
		
		if (! ctype_lower($value)) {
			$value = preg_replace('/\s+/u', '', ucwords($value));
			
			$value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
		}
		
		return static::$snakeCache[$key][$delimiter] = $value;
	}
	
	/**
	 * Determine if a given string starts with a given substring.
	 * @param  string           $haystack
	 * @param  string|string[]  $needles
	 * @return bool
	 */
	public static function startsWith(string $haystack, $needles): bool
	{
		foreach ((array) $needles as $needle) {
			if ((string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Convert a value to studly caps case.
	 * @param  string  $value
	 * @return string
	 */
	public static function studly(string $value): string
	{
		$key = $value;
		
		if (isset(static::$studlyCache[$key])) {
			return static::$studlyCache[$key];
		}
		
		$value = ucwords(str_replace(['-', '_'], ' ', $value));
		
		return static::$studlyCache[$key] = str_replace(' ', '', $value);
	}
	
	/**
	 * Returns the portion of string specified by the start and length parameters.
	 * @param  string    $string
	 * @param  int       $start
	 * @param  int|null  $length
	 * @return string
	 */
	public static function substr(string $string, int $start, int $length = null): string
	{
		return mb_substr($string, $start, $length, 'UTF-8');
	}
	
	/**
	 * Returns the number of substring occurrences.
	 * @param  string    $haystack
	 * @param  string    $needle
	 * @param  int       $offset
	 * @param  int|null  $length
	 * @return int
	 */
	public static function substrCount(string $haystack, string $needle, int $offset = 0, int $length = null): int
	{
		if (! is_null($length)) {
			return substr_count($haystack, $needle, $offset, $length);
		} else {
			return substr_count($haystack, $needle, $offset);
		}
	}
	
	/**
	 * Make a string's first character uppercase.
	 * @param  string  $string
	 * @return string
	 */
	public static function ucfirst(string $string): string
	{
		return static::upper(static::substr($string, 0, 1)) . static::substr($string, 1);
	}
	
	/**
	 * Generate a UUID (version 4).
	 * @return UuidInterface
	 */
	public static function uuid(): UuidInterface
	{
		return static::$uuidFactory
			? call_user_func(static::$uuidFactory)
			: Uuid::uuid4();
	}
	
	/**
	 * Generate a time-ordered UUID (version 4).
	 * @return UuidInterface
	 */
	public static function orderedUuid(): UuidInterface
	{
		if (static::$uuidFactory) {
			return call_user_func(static::$uuidFactory);
		}
		
		$factory = new UuidFactory();
		
		$factory->setRandomGenerator(
			new CombGenerator(
				$factory->getRandomGenerator(),
				$factory->getNumberConverter()
			)
		);
		
		$factory->setCodec(
			new TimestampFirstCombCodec(
				$factory->getUuidBuilder()
			)
		);
		
		return $factory->uuid4();
	}
	
	/**
	 * Set the callable that will be used to generate UUIDs.
	 * @param  callable|null  $factory
	 * @return void
	 */
	public static function createUuidsUsing(callable $factory = null): void
	{
		static::$uuidFactory = $factory;
	}
	
	/**
	 * Indicate that UUIDs should be created normally and not using a custom factory.
	 * @return void
	 */
	public static function createUuidsNormally(): void
	{
		static::$uuidFactory = null;
	}
	
	/**
	 * @param  string  $html
	 * @return string
	 */
	public static function clean(string $html): string
	{
		$sanitizer = Sanitizer::create(
			[
				'max_input_length' => 30000,
				'extensions'       => ['basic', 'list', 'table', 'image', 'details', 'extra'],
			]
		);
		
		return $sanitizer->sanitize(self::stripJS($html));
	}
	
	/**
	 * @param  string  $html
	 * @return string
	 */
	public static function purify(string $html): string
	{
		$sanitizer = Sanitizer::create(
			[
				'max_input_length' => 30000,
			]
		);
		
		return $sanitizer->sanitize(self::stripJS($html));
	}
	
	/**
	 * @param  string  $html
	 * @return string
	 */
	public static function stripJS(string $html): string
	{
		return preg_replace('#<(.*)script(.*)>(.*)<(.*)/(.*)script(.*)>#i', '', $html);
	}
}
