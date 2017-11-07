<?php

/* security */
$access_token = 'MmNmMGWERQeowTerEnljoERmMGIRTRYiEy=';
//ip地址为gitlab服务器请求地址
$access_ip = array({server});

/* get user token and ip address */
$client_token = $_REQUEST['token'];
$client_ip = $_SERVER['REMOTE_ADDR'];
//查询服务器运行的php-fpm用户和文件所属权限是否一致
//print_r($_SERVER);

//文件记录日志
/* create open log */
$fs = fopen('./webhook.log', 'a');
fwrite($fs, 'Request on ['.date("Y-m-d H:i:s").'] from ['.$client_ip.']'.PHP_EOL);

/* test token */
if ($client_token !== $access_token)
{
    echo "error 403";
    fwrite($fs, "Invalid token [{$client_token}]".PHP_EOL);
    exit(0);
}

/* test ip*/
if ( ! in_array($client_ip, $access_ip))
	{
    echo "error 503";
    fwrite($fs, "Invalid ip [{$client_ip}]".PHP_EOL);
    exit(0);
	} 

//git push 时触发的json数据，可参考gitlab中web_hooks介绍
/* get json data */
$json = file_get_contents('php://input');
$data = json_decode($json, true);

/* get branch */
$branch = $data["ref"];
fwrite($fs, '======================================================================='.PHP_EOL);
/* if you need get full json input */
//fwrite($fs, 'DATA: '.print_r($data, true).PHP_EOL);

/* branch filter */
if ($branch === 'refs/heads/master')
{
	/* if master branch*/
	fwrite($fs, 'BRANCH: '.print_r($branch, true).PHP_EOL);
	fwrite($fs, '======================================================================='.PHP_EOL);
	$fs and fclose($fs);
	/* then pull master */
	system("cd {projects} && git checkout master");
	system("git pull");
		
} else {
	/* if devel branch */
	fwrite($fs, 'BRANCH: '.print_r($branch, true).PHP_EOL);
	fwrite($fs, '======================================================================='.PHP_EOL);
	$fs and fclose($fs);
	/* pull devel branch */
	system("cd {projects} && git checkout develop");
	system("git pull origin develop:develop");
}
?>
