<?php

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
echo PHP_VERSION;
if (version_compare(PHP_VERSION, '7.3.0', '<')) {
	echo 'Adamant require Php to be at least 7.3.0. you have  ' . PHP_VERSION . "\n";
	exit();
}




$expected = file_get_contents("https://composer.github.io/installer.sig");

copy('https://getcomposer.org/installer', 'composer-setup.php');

$actual=hash_file('sha384', 'composer-setup.php');


if ( $actual === $expected){ 
	echo 'Installer verified';
	putenv('COMPOSER_HOME='.__DIR__);
	echo shell_exec('php composer-setup.php --quiet');
} else {
	echo 'Installer corrupt';
	unlink('composer-setup.php');
}
echo PHP_EOL;
unlink('composer-setup.php');
unlink('keys.dev.pub');
unlink('keys.tags.pub');

echo shell_exec('php composer.phar create-project ti-ssi/adamant-frontend frontend --repository-url ./package.json -s dev');

echo 'Installation ended';
