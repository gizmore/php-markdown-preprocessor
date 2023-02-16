<?php
namespace gizmore\pp4md;

/**
 * (Pre)Processor for markdown-like text.
 *
 * @author gizmore
 * @see https://github.com/gizmore/phpgdo
 * @version 7.0.2
 * @since 7.0.2
 */
final class PP4MD
{

	/**
	 * Call this to include and init.
	 */
	public static function init(): self
	{
		return new self();
	}

	public function __construct()
	{
		$this->format(self::UNICODE);
	}

	# ##############
	# ## Options ###
	# ##############
	# Output formats
	const ASCII = 'ascii';
	const UNICODE = 'unicode';
	const ASCIICODE = 'asciicode';
	const MARKDOWN = 'markdown';
	const YOUTUBE = 'youtube';
	const GITHUB = 'github';
	const TWITTER = 'twitter';

	private $callable = null;

	public string $format = self::UNICODE;

	public function format(string $format): self
	{
		$this->format = $format;
		$cb = [
			$this,
			"_{$format}"
		];
		$this->callable = $cb;
		return $this;
	}

	# I/O
	/**
	 *
	 * @var resource
	 */
	private $in = STDIN;

	/**
	 *
	 * @var resource
	 */
	private $out = STDOUT;

	private ?string $infile = null;

	# full path
	public function infile(string $infile = null, bool $recursive = true): self
	{
		if ($infile === null)
		{
			unset($this->infile);
			$this->infile = STDOUT;
		}
		elseif (is_readable($infile))
		{
			$this->infile = $infile;
			if (is_dir($infile))
			{
				return $this->recursive($recursive);
			}
		}
		else
		{
			throw new \Exception('Infile is not readable');
		}
		return $this;
	}

	public ?string $outfile = null;

	public function outfile(string $path = null): self
	{
		if ($path === null)
		{
			unset($this->outfile);
		}
		else
		{
			if ( !is_writable($path))
			{
				$this->error( -4, 'Cannot write out path: ' . $path);
			}
			$this->outfile = $path;
		}
		return $this;
	}

	public function getOutPath(): ?string
	{
		if ($this->recursive)
		{
			return $this->getAlikePath();
		}
		return isset($this->outfile) ? $this->outfile : null;
	}

	public string $filePattern = '/\\.md?$/iD';

	public function filePattern(string $filePattern): self
	{
		$this->filePattern = $filePattern;
		return $this;
	}

	public bool $force = false;

	public function force(bool $force = true): self
	{
		$this->force = $force;
		return $this;
	}

	public bool $recursive = false;

	public function recursive(bool $recursive = true): self
	{
		$this->recursive = $recursive;
		return $this;
	}

	public bool $verbose = false;

	public function verbose(bool $verbose = true): self
	{
		$this->verbose = $verbose;
		return $this;
	}

	public bool $rulers = false;

	public function rulers(bool $rulers = true): self
	{
		$this->rulers = $rulers;
		return $this;
	}

	public bool $brnl = false;

	public function brnl(bool $brnl = true): self
	{
		$this->brnl = $brnl;
		return $this;
	}

	public bool $quicklinks = false;

	public function quicklinks(bool $quicklinks = true): self
	{
		$this->quicklinks = $quicklinks;
		return $this;
	}

	# ###########
	# ## Main ###
	# ###########
	public function execute(): void
	{
		try
		{
			$this->open();
			$this->processFD($this->in, $this->out);
		}
		finally
		{
			$this->close();
		}
	}

	/**
	 *
	 * @param resource $in
	 * @param resource $out
	 */
	public function processFD($in, $out): void
	{
		$this->__processHandles($in, $out);
	}

	public function processLine(string $line): string
	{
		return $this->__process($line);
	}

	# ##########
	# ## I/O ###
	# ##########
	private function open(): void
	{
		if ($this->in !== STDIN)
		{
			$this->in = fopen($this->infile, "r");
		}
		if ($this->out !== STDOUT)
		{
			$path = $this->getOutPath();
			if (file_exists($path) && ( !$this->force))
			{
				$this->error( -2, 'Do not want to overwrite output file: ' . $path);
			}
			$this->out = fopen($path, "w");
		}
	}

	private function close(): void
	{
		if ($this->in !== STDIN)
		{
			fclose($this->in);
			$this->in = STDIN;
		}
		if ($this->out !== STDOUT)
		{
			fclose($this->out);
			$this->out = STDOUT;
		}
	}

	private function __processHandles($in, $out): void
	{
		while ($line = fgets($in))
		{
			$this->bytesProcessed += strlen($line);
			$line = $this->processLine($line);
			fwrite($out, $line);
		}
		$this->filesProcessed++;
	}

	# ######################
	# ## Line processors ###
	# ######################
	private function __process(string $line): string
	{
		$this->line++;
		if ($line = trim($line))
		{
			$line = call_user_func($this->callable, $line);
		}
		$this->linesProcessed++;
		return $line;
	}

	private function _ascii(string $line): string
	{
		if ($this->rulers)
		{
			$line = Expressions::ASCII_RULER($line);
		}
		if ($this->brnl)
		{
			$line = Expressions::BRNL($line);
		}
		return $line;
	}

	private function _unicode(string $line): string
	{
		return $line;
	}

	private function _asciicode(string $line): string
	{
		return $line;
	}

	private function _markdown(string $line): string
	{
		return $line;
	}

	private function _youtube(string $line): string
	{
		return $line;
	}

	private function _github(string $line): string
	{
		return $line;
	}

	private function _twitter(string $line): string
	{
		return $line;
	}

	# ############
	# ## Debug ###
	# ############
	public int $line = 0;

	public int $bytesProcessed = 0;

	public int $linesProcessed = 0;

	public int $filesProcessed = 0;

	public function usageError(string $error): void
	{
		$this->printUsage( -1, $error);
	}

	private function printUsage(int $code, string $error = ''): void
	{
		global $argv;
		echo "Usage: {$argv[0]} [-hRrv] [-o outpath] --format outputformat [--rulers] [--rulers] [--brnl] [--quicklinks] [--smileys smileyformat] [<file or folder>]\n";
		echo "Output formats: ASCII, Unicode, ASCIICode, Markdown, YouTube, GitHub, Twitter.\n";
		echo "Smiley formats: Ignore, ASCII, Unicode, GitHub, CDN, Markdown. Default: ignore.\n";
		echo "\n";
		echo "Example: {$argv[0]} --quicklinks my.md --format youtube > my.yt \n";
		echo "\n";
		$error && $this->error($code, $error);
		die($code);
	}

	public function error(int $code, string $error): void
	{
		fwrite(STDERR, "Error: {$error}\n");
		die($code);
	}

	public function message(string $message): void
	{
		fwrite(STDOUT, "{$message}\n");
	}

	public function debug(string $message): void
	{
		if ($this->verbose)
		{
			fwrite(STDOUT, "{$message}\n");
		}
	}

}
