<?php

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

session_start();
if(empty($_GET['step'])) $step = 1;
else $step = $_GET['step'];

echo "INSTALLATION STEP #$step <br /><br />".PHP_EOL;
switch($step){
case 1:
	$requirement = true;
	//check requirements
	if (version_compare(PHP_VERSION, '7.3.0', '<')) {
		echo 'Adamant require Php to be at least 7.3.0. you have  ' . PHP_VERSION . "\n";
		$requirement = false;
	}else{
		echo 'Php version : '.PHP_VERSION.PHP_EOL;
	}

	$node_ver = ltrim(shell_exec('node -v'), 'v');
	if(version_compare($node_ver, '10.0.0','<')){
		echo '<br />Adamant require Node at least at version 10. You have '.$node_ver;
		$requirement = false;
	}else{
		echo '<br />Node version : '.$node_ver.PHP_EOL;

		$npm_ver = shell_exec('npm -v');
		if(version_compare($npm_ver, '6.0.0','<')){
			echo '<br />Adamant require NPM at least at version 6. You have '.$npm_ver;
			$requirement = false;
		}else{
			echo '<br />NPM version : '.$npm_ver.PHP_EOL;
		}
	}

	if($requirement){
		echo '<br /><a href="install.php?step=2">Begin installation</a>';
	}else{
		echo '<br />You cannot install Adamant on you current system.';
	}

break;
case 2:
if(file_exists('composer.phar')){
echo 'Composer already installed';
}else{
//load and install composer
$expected = file_get_contents("https://composer.github.io/installer.sig");

copy('https://getcomposer.org/installer', 'composer-setup.php');

$actual=hash_file('sha384', 'composer-setup.php');

if ( $actual === $expected){ 
	echo 'Composer installer verified';
	putenv('COMPOSER_HOME='.__DIR__);
	echo shell_exec('php composer-setup.php --quiet');
} else {
	echo 'Composer Installer corrupt';
	unlink('composer-setup.php');
}
echo PHP_EOL;
unlink('composer-setup.php');
unlink('keys.dev.pub');
unlink('keys.tags.pub');

flush();
echo 'Composer installed';

}

echo '<br /><a href="install.php?step=3">Install frontend</a>';
echo '<br /><a href="install.php?step=4">or skip and Install Api</a>';
echo '<br /><a href="install.php?step=5">or skip both and Install Admin</a>';
echo '<br /><a href="install.php?step=6">Goto database setup</a>';

break;die();
case 3:
if(isset($_GET['delete'])) echo shell_exec('rm -rf ./frontend');
if(file_exists('frontend')){
	echo 'there is already a frontend folder';
	echo '<br /><a href="install.php?step=3&delete">Delete and retry? (youll lose everything in that folder)</a>';

	echo '<br /><a href="install.php?step=4">Skip and Install Api</a>';
	echo '<br /><a href="install.php?step=5">Skip both and Install Admin</a>';
	echo '<br /><a href="install.php?step=6">Goto database setup</a>';

}else{

	file_put_contents('package.json', '{
"package": { "name": "ti-ssi/adamant-frontend", "version": "0.1.0",
"source": {
            "url":
            "https://github.com/TI-ssi/Adamant-frontend.git",
            "type": "git", "reference": "master"
           }
        }
}');


	//create required project
	echo shell_exec('php composer.phar create-project ti-ssi/adamant-frontend frontend --repository-url ./package.json -s dev --remove-vcs');
	flush();
	unlink('package.json');
	//run npm
	echo shell_exec("npm --prefix ./frontend install ./frontend --cache ./");
	flush();

	echo '<br /><a href="install.php?step=4">Install Api</a>';
	echo '<br /><a href="install.php?step=5">or skip and Install Admin</a>';
	echo '<br /><a href="install.php?step=6">Goto database setup</a>';
}

break;
case 4:  //api
if(isset($_GET['delete'])) echo shell_exec('rm -rf ./api');
if(file_exists('api')){
	echo 'there is already an api folder';
	echo '<br /><a href="install.php?step=4&delete">Delete and retry? (youll lose everything in that folder)</a>';

	echo '<br /><a href="install.php?step=5">Skip and Install Admin</a>';
	echo '<br /><a href="install.php?step=6">Goto database setup</a>';

}else{
	file_put_contents('package.json', '{
"package": { "name": "ti-ssi/adamant-api", "version": "0.1.0",
"source": {
            "url":
            "https://github.com/TI-ssi/Adamant-api.git",
            "type": "git", "reference": "master"
           }
        }
}');


	echo shell_exec('php composer.phar create-project ti-ssi/adamant-api api --repository-url ./package.json -s dev --remove-vcs');
	flush();
	unlink('package.json');

	echo '<br /><a href="install.php?step=5">Install Admin</a>';
	echo '<br /><a href="install.php?step=6">Goto database setup</a>';
}
break;
 case 5: //admin
if(isset($_GET['delete'])) echo shell_exec('rm -rf ./admin');
if(file_exists('admin')){
	echo 'there is already an admin folder';
	echo '<br /><a href="install.php?step=5&delete">Delete and retry? (youll lose everything in that folder)</a>';

	echo '<br /><a href="install.php?step=6">Goto database setup</a>';

}else{
	file_put_contents('package.json', '{
"package": { "name": "ti-ssi/adamant-admin", "version": "0.1.0",
"source": {
            "url":
            "https://github.com/TI-ssi/Adamant-admin.git",
            "type": "git", "reference": "master"
           }
        }
}');


	echo shell_exec('php composer.phar create-project ti-ssi/adamant-admin admin --repository-url ./package.json -s dev --remove-vcs');
	flush();
	unlink('package.json');

	echo '<br /><a href="install.php?step=6">Goto database setup</a>';
}

break;
case 6: //bd
if(empty($_POST)){
?>
<form action="install.php?step=6" method="post">

<label><b>Hostname</b></label><br />
<input name="hostname" type="text"><br/>
<label><b>Database name</b></label><br />
<input name="database" type="text"><br />
<label><b>Username</b></label><br />
<input name="username" type="text"><br />
<label><b>Password</b></label><br />
<input name="password" type="text"><br />
<br />
<input type="submit">
</form>
<?php
}else{

file_put_contents('./api/.env', "APP_ENV=local
APP_DEBUG=false
DB_DEBUG=false

APP_KEY=".substr(bin2hex(random_bytes(32)),0,32)."
APP_TIMEZONE=UTC

DB_CONNECTION=mysql
DB_HOST={$_POST['hostname']}
DB_PORT=3306
DB_DATABASE={$_POST['database']}
DB_USERNAME={$_POST['username']}
DB_PASSWORD={$_POST['password']}

CACHE_DRIVER=file
QUEUE_DRIVER=sync

");


if(strpos(shell_exec('php api/artisan migrate'), 'SQLSTATE') !== FALSE) echo '<br /><a href="install.php?step=6">Retry</a>';
else{
	echo '<br /><a href="install.php?step=7">Configure Api</a>';
	shell_exec('php api/artisan passport:install --uuids --force');
	shell_exec('php api/artisan passport:client --password');
	$db = new mysqli($_POST['hostname'],
			 $_POST['username'],
			 $_POST['password'],
			 $_POST['database']
	);
	$result = $db->query("SELECT id, secret FROM oauth_clients WHERE password_client = 1");
	$credantials = $result->fetch_array(MYSQLI_ASSOC);

	$api_conf = "<?php
return [
	'default' => [
		'baseUrl' => '".(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://')."".$_SERVER['HTTP_HOST']."/api/',
		'clientToken' => '{$credantials['id']}',
        	'clientSecret' => '{$credantials['secret']}'
	],
];
";

	rename('frontend/config/api.php', 'frontend/config/old_api.php');
	file_put_contents('frontend/config/api.php', $api_conf);

}

}


break;
case 7: //api config



break;

case 8 : //htaccess



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

break;
}


