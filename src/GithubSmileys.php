<?php
namespace gizmore\pp4md;

/**
 * 
 * @author gizmore
 * @see https://github.com/ikatyang/emoji-cheat-sheet/blob/master/README.md
 * @since 7.0.2
 */
final class GithubSmileys
{
	#############
	### Modes ###
	#############
	const IGNORE = 'ignore';
	const ASCII = 'ascii';
	const UNICODE = 'unicode';
	const GITHUB = 'github';
	const CDN = 'cdn';
	const MARKDOWN = 'markdown';
	
	###########
	### Map ###
	###########
	public static array $MAP = [
		'/[^:\\(]:)/iD' => [':)', '😊', ':smile:',  '1f603.png'],
	];
	
	###############
	### Options ###
	###############
	public string $cdn = 'https://github.githubassets.com/images/icons/emoji/unicode/';
	public function cdn(string $cdn): self
	{
		$this->cdn = $cdn;
		return $this;
	}
	
	public string $mode = self::IGNORE;
	public function mode(string $mode): self
	{
		$this->mode = $mode;
		return $this;
	}
	
	###############
	### Process ###
	###############
	public function processLine(string $line): string
	{
		
	}
	
	

}