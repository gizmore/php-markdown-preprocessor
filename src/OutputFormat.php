<?php
namespace gizmore\pp4md;

final class OutputFormat
{
	
	const ASCII = 0;
	const Unicode = 1;
	const AsciiCode = 2;
	const Markdown = 3;
	const YouTube = 4;
	const GitHub = 5;
	const Twitter = 6;
	
	public static $FORMATS = [
		'ASCII',
		'Unicode',
		'AsciiCode',
		'Markdown',
		'YouTube',
		'GitHub',
		'Twitter',
	];

	public static function wantsRuler(int $format): bool
	{
		static $formats = [self::ASCII, self::Unicode, self::YouTube];
		return in_array($format, $formats, true); 
	}
	
	public static function wantsQuicklinks(int $format): bool
	{
		return true;
	}
	
}