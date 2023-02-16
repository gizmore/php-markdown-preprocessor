<?php
namespace gizmore\pp4md;

/**
 * Regular expression repository.
 * Let's try! :D
 * 
 * @author gizmore
 * @version 7.0.2
 */
final class Expressions 
{
	const ASCII_RULER = ' ============================================== ';

	public static function ASCII_RULER(string $line): string
	{
		$ptnr = '/^\s*[-=_.]{3,}[-\s=_.]*$/iD';
		return preg_replace($ptnr, self::ASCII_RULER, $line);
	}
	
	public static function BRNL(string $line): string
	{
		return str_replace('%%', "<br/>\n", $line);
	}

	public static function ASCII_URLS($line): string
	{
		$line = preg_replace('', '', $line);
		$line = preg_replace('', '', $line);
	}
	
}
