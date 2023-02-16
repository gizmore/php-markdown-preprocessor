<?php
namespace gizmore\pp4md;
#
# Boot
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
#
# Sanity
$pp = PP4MD::init();
assert($pp instanceof PP4MD, 'Let\'s check if PP is a PP.');
assert($pp->processLine("::") === '::', 'Check if :: keeps the same in default mode.');
#
# ASCII Rulers 
$in = '---';
assert($pp->format(PP4MD::ASCII)->processLine($in) === $in, 'Test deactivated ASCII ruler.');
assert($pp->rulers()->processLine($in) !== $in, 'Test deactivated ASCII ruler.');
#
# PHP BRNL
$in = '%%';
assert(strpos($pp->brnl()->processLine($in), 'br/'), 'Test activated brnl filter.');
#
# Stats
echo "Done!\n";
