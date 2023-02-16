<?php
namespace gizmore\pp;

use gizmore\pp4md\PP4MD;

require 'vendor/autoload.php';

global $argv;

$rest = null;

$opt = getopt('fho::rv', [
	'force',
	'help',
	'outfile::',
	'recursive',
	'verbose',
	# PP options
	'brnl',
	'format:',
	'quicklinks',
	'rulers',
	'smileys::',
], $rest);

$files = array_slice($argv, $rest);

$pp = new PP4MD();

if (count($files) > 1)
{
	$pp->usageError('Too many paramteres.');
}

# ##############
# ## Options ###
# ##############
if (isset($opt['h']) || (isset($opt['help'])) || count($files) > 1)
{
	echo "Usage: {$argv[0]} [--help] [--verbose] [--simulate] [--outfile] [--recursive] [--replace] [<path>]";
	return 0;
}

if (isset($opt['s']) || isset($opt['simulate']))
{
	$pp->simulate(true);
}

if (isset($opt['r']) || isset($opt['replace']))
{
	$pp->replace(true);
}

if (isset($opt['R']) || isset($opt['recursive']))
{
	$pp->recurse(true);
}

if (isset($opt['v']) || isset($opt['verbose']))
{
	$pp->verbose(true);
}

if (isset($opt['o']) || isset($opt['outfile']))
{
	$pp->output($opt['o'] ? $opt['o'] : $opt['outfile']);
}

if (isset($opt['u']) || isset($opt['uglify']))
{
	$pp->uglify(true);
}

if (count($files) === 1)
{
	foreach ($files as $path)
	{
		$pp->verb("Processing {$path}");
		$pp->input($path);
	}
}

$pp->verb('Executing PP preprocesser...');
if ($pp->execute())
{
	$pp->message('All done.');
}
else
{
	$pp->error('An error occured.');
}
