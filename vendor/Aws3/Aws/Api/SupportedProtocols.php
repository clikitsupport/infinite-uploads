<?php

namespace ClikIT\Infinite_Uploads\Aws\Api;

/**
 * Priority ordered collection of supported AWS protocols.
 */
final class SupportedProtocols
{
	public const JSON      = 'json';
	public const REST_JSON = 'rest-json';
	public const REST_XML  = 'rest-xml';
	public const QUERY     = 'query';
	public const EC2       = 'ec2';

	/**
	 * Get all supported protocols.
	 *
	 * @return string[]
	 */
	public static function all(): array
	{
		return [
			self::JSON,
			self::REST_JSON,
			self::REST_XML,
			self::QUERY,
			self::EC2,
		];
	}

	/**
	 * Check if a protocol is supported.
	 *
	 * @param string $protocol
	 * @return bool True if the protocol is supported, otherwise false.
	 */
	public static function isSupported(string $protocol): bool
	{
		return in_array($protocol, self::all(), true);
	}
}
