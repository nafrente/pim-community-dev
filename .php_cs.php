<?php

$branch     = getenv('TRAVIS_BRANCH');
$phpVersion = getenv('TRAVIS_PHP_VERSION');

printf('Current branch inspected : %s' . PHP_EOL, $branch);

$finder = \Symfony\CS\Finder\DefaultFinder::create()->files();
$fixers = require __DIR__ . '/.php_cs-fixers.php';

if (is_numeric(getenv('TRAVIS_PULL_REQUEST'))) {
    $commitRange = str_replace('...', '..', getenv('TRAVIS_COMMIT_RANGE'));
    printf('Commit range = %s' . PHP_EOL, $commitRange);
    exec('git diff ' . $commitRange . ' --name-only --diff-filter=AMR | grep -v ^spec/', $diff);
} else {
    exec('git show --name-only --oneline --pretty="format:" --diff-filter=AMR | grep -v ^spec/', $diff);
    $diff = array_filter($diff);
}

foreach ($diff as $filename) {
    printf('Parsed file : %s' .PHP_EOL, $filename);
}

$finder->append($diff);

return \Symfony\CS\Config\Config::create()
    ->fixers($fixers)
    ->finder($finder);
