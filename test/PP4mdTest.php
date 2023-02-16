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
assert($pp->processString("::") === '::', 'Check if :: keeps the same in default mode.');
#
# ASCII Rulers 
$in = '---';
assert($pp->format(OutputFormat::ASCII)->processString($in) === $in, 'Test deactivated ASCII ruler.');
assert($pp->rulers()->processString($in) !== $in, 'Test activated ASCII ruler.');
#
# PHP BRNL
$in = '%%';
assert(strpos($pp->brnl()->processString($in), 'br/'), 'Test activated brnl filter.');
#
# %cgx%6%
$in = '%cgx%1%';
$out = $pp->quicklinks()->processString($in);
assert(strpos($out, 'https://www.wechall.net/cgx') === 0, 'Test if %cgx% wechall.net link works.');
$in = '%wc%gizmore%';
$out = $pp->quicklinks()->processString($in);
assert(strpos($out, 'https://www.wechall.net/profile/gizmore') === 0, 'Test if %wc% profile link works.');
#
# Stats
echo "All tests passed!\n";
