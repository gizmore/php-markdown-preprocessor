<?php
namespace gizmore\pp4md;

/**
 * Replace some urls into markup encoding.
 * 
 * @author gizmore
 */
final class Quicklinks
{
	
	public static $QUICKLINKS = [
		'/%cgx%(\d+)%/iD' => 'https://www.wechall.net/cgx/$1',
		'/%wc%([\d_\w]+)%/iD' => 'https://www.wechall.net/profile/$1',
		'/%yt%@([^%]+)%/iD' => 'https://youtube.com/@$1',
		'/%yt%([^%]+)%/iD' => 'https://youtu.be/$1',
		'/%tw%(@[^%]+)%/iD' => 'https://twitter.com/$1',
		'/%tw%(\d+)%/iD' => 'https://twitter.com/$1',
	];
	
	public static function processLine(string $line): string
	{
		foreach (self::$QUICKLINKS as $pattern => $replacement)
		{
			$line = preg_replace($pattern, $replacement, $line);
		}
		return $line;
	}
	
}
