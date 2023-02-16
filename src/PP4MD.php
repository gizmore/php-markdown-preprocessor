<?php
namespace gizmore\pp4md;

use gizmore\Filewalker;
use gizmore\pp4md\Quicklinks;

/**
 * (Pre)Processor for markdown-like text.
 *
 * @author gizmore
 * @see https://github.com/gizmore/phpgdo
 * @version 7.0.2
 * @since 7.0.2
 */
class PP4MD
{
	/**
	 * Just callbacks.
	 * @var callable[]
	 */
	protected array $lineFilters = [];

	/**
	 * Call this to include and init.
	 */
	public static function init(): self
	{
		return new self();
	}

	protected function __construct()
	{
		$this->format(OutputFormat::Unicode);
	}
	
	# ##############
	# ## Options ###
	# ##############
	/**
	 * Current format method.
	 * @var callable
	 */
	protected $callable = null;

	public int $format = OutputFormat::Unicode;

	public function format(int $format): self
	{
		$this->format = $format;
		
		if (!isset(OutputFormat::$FORMATS[$format]))
		{
			$this->error(-8, 'Invalid output format: ' . $format);
		}
		
		$format = OutputFormat::$FORMATS[$format];
		
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
	public $in = STDIN;

	/**
	 *
	 * @var resource
	 */
	public $out = STDOUT;

	public ?string $infile = null;

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
	const E = Expressions::class;
	const Q = Quicklinks::class;
	
	private static $BRNL = [
	];
	
	private function buildFilters(): void
	{
		$this->lineFilters = [];
		if ($this->brnl)
		{
			$this->lineFilters[] = [self::E, 'BRNL'];
		}
		if ($this->rulers &&
			OutputFormat::wantsRuler($this->format))
		{
			$this->lineFilters[] = [self::E, 'ASCII_RULER'];
		}
		if ($this->quicklinks &&
			OutputFormat::wantsQuicklinks($this->format))
		{
			$this->lineFilters[] = [self::Q, 'processLine'];
		}
	}
	
	public function execute(): void
	{
		$this->check();
		$this->buildFilters();
		
		if ($this->infile)
		{
			if (is_dir($this->infile))
			{
				# recurse depth
				$rc = ($this->recursive) ? 32 : 0;
				$cb = [$this, 'executeFile'];
				Filewalker::traverse($this->infile,
					$this->filePattern, $cb, null, $rc);
			}
			else
			{
				$this->executeFile(
					basename($this->infile),
					$this->infile);
			}
		}
		else # stdin, no dir
		{
			try
			{
				$this->open();
				$this->__processHandles($this->in, $this->out);
			}
			finally
			{
				$this->close();
			}
		}
	}
	
	public function processString(string $content): string
	{
		try
		{
			$this->buildFilters();
			$filename = tempnam(sys_get_temp_dir(), 'GDO');
			file_put_contents($filename, $content);
			$in = fopen($filename, 'r');
			$processed = '';
			$this->__processHandles($in, STDOUT, $processed);
			return $processed;
		}
		finally
		{
			@fclose(@$in);
			@unlink(@$filename);
		}
	}
	
	private function processLine(string $line): string
	{
		return $this->__process($line);
	}
	
	###############
	### Private ###
	###############
	private function executeFile(string $entry, string $fullpath): void
	{
		try
		{
			$this->infile = $fullpath;
			$this->outfile = $this->getOutPath();
			$this->open();
			$this->__processHandles($this->in, $this->out);
		}
		finally
		{
			$this->close();
		}
	}

	# ##########
	# ## I/O ###
	# ##########
	private function open(): void
	{
		if ($this->infile)
		{
			$this->in = fopen($this->infile, "r");
		}
		if ($this->outfile || $this->recursive)
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

	private function __processHandles($in, $out, string &$capture=''): void
	{
		while ($line = fgets($in))
		{
			$read = strlen($line);
			$line = $this->processLine($line);
			fwrite($out, $line);
			$capture .= $line;
			$this->bytesProcessed += $read;
		}
		$this->filesProcessed++;
	}
	
	# ######################
	# ## Line processors ###
	# ######################
	private function __process(string $line): string
	{
		$this->line++;
		# generic line filters.
		$line = $this->_lineFilters(trim($line));
		# formats line filters.
		$line = call_user_func($this->callable, $line);
		$this->linesProcessed++;
		return $line;
	}
	
	private function _lineFilters(string $line): string
	{
		foreach ($this->lineFilters as $filter)
		{
			$line = call_user_func($filter, $line);
		}
		return $line;
	}

	private function _ascii(string $line): string
	{
		$line = Expressions::ASCII_URLS($line);
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

	private function check(): void
	{
		if ($this->recursive)
		{
			if ($this->isSTDIN() || (!$this->infile) || (!is_dir($this->infile)))
			{
				$this->error(-7, 'Cannot open recursive Input.');
			}
		}
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

	private function error(int $code, string $error): void
	{
		fwrite(STDERR, "Error: {$error}\n");
		die($code);
	}

	private function message(string $message): void
	{
		fwrite(STDOUT, "{$message}\n");
	}

	private function debug(string $message): void
	{
		if ($this->verbose)
		{
			fwrite(STDOUT, "{$message}\n");
		}
	}

}
