<?php

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

//check requirements
if (version_compare(PHP_VERSION, '7.3.0', '<')) {
	echo 'Adamant require Php to be at least 7.3.0. you have  ' . PHP_VERSION . "\n";
	exit();
}

//load and install composer
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

flush();

//create required project
echo shell_exec('php composer.phar create-project ti-ssi/adamant-frontend frontend --repository-url ./package-frontend.json -s dev --remove-vcs');
flush();
echo shell_exec('php composer.phar create-project ti-ssi/adamant-api api --repository-url ./package-api.json -s dev --remove-vcs');
flush();

//run npm
echo shell_exec("npm --prefix ./frontend install ./frontend --cache ./");
flush();
echo shell_exec("npm --prefix ./frontend run dev --cache ./");
flush();


//write root htaccess file
file_put_contents('.htaccess', "<IfModule mod_rewrite.c>
	<IfModule mod_negotiation.c>
		Options +FollowSymLinks -MultiViews -Indexes
	</IfModule>
	RewriteEngine On

	RewriteRule ^api/(.*)$ /api/public/$1 [R=301,L]

	RewriteRule ^admin/(.*)$ /admin/public/$1 [R=301,L]

	RewriteCond %{REQUEST_URI} !^/frontend
	RewriteCond %{REQUEST_URI} !^/api
	RewriteCond %{REQUEST_URI} !^/admin
	RewriteRule ^(.*)$ /frontend/public/$1 [L]
</IfModule>

");

echo 'Installation ended';

