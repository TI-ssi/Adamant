<?php

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

	$postBody = json_decode(file_get_contents("php://input"), true);

	switch($postBody['step']){
		case 'requirement':
			//check Php
			$response['phpVer'] = PHP_VERSION;
			$response['phpVerOk'] = true;
			if (version_compare(PHP_VERSION, '7.3.0', '<')) {
				$response['phpVerOk'] = false;
			}

			//check Node
			$response['nodeVer'] = ltrim(shell_exec('node -v'), 'v');
			$response['nodeVerOk'] = true;
        		if(version_compare($response['nodeVer'], '10.0.0','<')){
				$response['nodeVerOk'] = false;
				$response['npmVerOk'] = false;
				$response['npmVer'] = 'Need NodeJs';

			}

			if($response['nodeVerOk']){
				$response['npmVer'] = shell_exec('npm -v');
				$response['npmVerOk'] = true;
                        	if(version_compare($response['npmVer'], '6.0.0','<')){
					$response['npmVerOk'] = false;
				}
			}

		break;

		case 'composer-install':
		     $response['composerOk'] = true;
		     if(isset($postBody['force']) && $postBody['force']) unlink('composer.phar');
		     if(file_exists('composer.phar')){
			$response['log'] ='A composer.phar file is already present';
		     }else{
			//load and install composer
			$expected = file_get_contents("https://composer.github.io/installer.sig");
			copy('https://getcomposer.org/installer', 'composer-setup.php');

			$actual=hash_file('sha384', 'composer-setup.php');

			if ( $actual === $expected){ 
				putenv('COMPOSER_HOME='.__DIR__);
				$response['log'] = shell_exec('php composer-setup.php --quiet');
				$response['log'] .= ' Composer installed.';
			} else {
			        $response['composerOk'] = false;
			}

			unlink('composer-setup.php');
			unlink('keys.dev.pub');
			unlink('keys.tags.pub');

		    }



		break;

		default:
			$response = ['log' => 'Something is missing'];
	}

	echo json_encode($response);
}else{

//frontend page
?>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
		<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
	</head>
	<style>
		body{padding:1em;}
		#vApp{
			border:solid gray 3px;
			border-radius:20px;
			text-align:center;
		}

		.requirement > div{ margin:0 0.5em; padding: 1em 0; border-radius:20px;}
	</style>
	<body>
		<div id="vApp" class="container">
			<h1>Adamant</h1>
			<h3>Installation setup</h3>
			<div v-if="step==0" class="d-flex flex-row justify-content-around requirement">
			    <div class="col" v-bind:class="{'bg-success':phpVerOk, 'bg-danger':!phpVerOk}">
				<b>Php</b></br>
				7.3.0</br>
				<span v-html="phpVer"></span>
			    </div>
			    <div class="col" v-bind:class="{'bg-success':nodeVerOk, 'bg-danger':!nodeVerOk}">
				<b>NodeJs</b></br>
				10.0.0</br>
				<span v-html="nodeVer"></span>
			    </div>
			    <div class="col" v-bind:class="{'bg-success':npmVerOk, 'bg-danger':!npmVerOk}">
				<b>Npm</b></br>
				6.0.0</br>
				<span v-html="npmVer"></span>
			    </div>
			</div>
			<div v-if="step==1" class="row" >
			     <h6 class="col-12 bg-light text-left py-2">#1 Composer</h6>
			     <button class="btn btn-danger" v-on:click="composer(true)">Force install</button>
			</div>
			<div v-if="log" class="row">
			     <div class="offset-1 col-10 text-left">
			     	  <pre>{{log}}</pre>
			     </div>
			</div>

			<div class="d-inline-flex justify-content-center my-2">
				<button class="btn btn-primary" v-if="phpVerOk && nodeVerOk && npmVerOk" v-on:click="next()">{{ step == 0 ? btnText[0] : btnText[1] }}</button>
				<button class="btn btn-warning" v-else v-on:click="getRequirement()">Recheck</button>
			</div>
		</div>

		<script>
			var app = new Vue({
				el:   '#vApp',
				data: {
					phpVer:'&nbsp;',
					phpVerOk:false,
					nodeVer:'&nbsp;',
					nodeVerOk:false,
					npmVer:'&nbsp;',
					npmVerOk:false,
					btnText: ['Begin installation', 'Next'],
					step:0,
					log:''
  				},
  				methods:{
					getRequirement:function(){
						axios
      						.post('./install.php',{'step':'requirement'})
      						.then(response => {
							this.phpVer = response.data.phpVer;
							this.phpVerOk = response.data.phpVerOk;
							this.nodeVer = response.data.nodeVer;
							this.nodeVerOk = response.data.nodeVerOk;
							this.npmVer = response.data.npmVer;
							this.npmVerOk = response.data.npmVerOk;
						})
					},
					composer:function(force =false){
						axios
      						.post('./install.php',{'step':'composer-install', 'force':force})
      						.then(response => {
							this.log = response.data.log;
							this.composerOk = response.data.composerOk;
						})
					},
					next:function(){
						this.step++;
						if(this.step==1){this.composer();}
					}
  				},
  				mounted () {
					this.getRequirement();
				}
			})
		</script>
	</body>
</html>
<?php
}
