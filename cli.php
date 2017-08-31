<?php
/**
 * cli命令行模式
 * $_SERVER['argv'][0]指的是D:\WWW\gold_lock\cli.php，路径
 * $_SERVER['argv'][1]指的是home/index/tt，要访问的模块
 * $_SERVER['argv'][2]指的是要传的参数 ,需要更多可以加
 * 
 * php D:\WWW\gold_lock\cli.php home/index/tt ddd
 * php D:\WWW\gold_lock\cli.php home/grab/push
 * php D:\WWW\gold_lock\cli.php home/index/lastrevenue
 * 
 * /usr/local/php56/bin/php /data/httpd/gold_lock/cli.php home/index/weekrank
 * @author Jeff
 */

// 检测PHP环境
//if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

//普通模式，解决官方分组不支持cli的问题
if(php_sapi_name() === 'cli'){
	$depr = '/';
	$path   = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';
	if(!empty($path)) {
		$params = explode($depr,trim($path,$depr));
	}
	
	!empty($params)?$_GET['g']=array_shift($params):"";
	!empty($params)?$_GET['m']=array_shift($params):"";
	!empty($params)?$_GET['a']=array_shift($params):"";
	if(count($params)>1) {
		// 解析剩余参数 并采用GET方式获取
		preg_replace('@(\w+),([^,\/]+)@e', '$_GET[\'\\1\']="\\2";', implode(',',$params));
	}
}

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);

// 定义应用目录
define('APP_PATH', dirname(__FILE__) . '/Cli/');

//采用CLI运行模式运行 
define('MODE_NAME', 'cli');
//加载极光推送
require 'vendor/autoload.php';

define('TMPL_CACHE_ON', false);
define('HTML_CACHE_ON', false);
define('ACTION_CACHE_ON', false);

// 引入ThinkPHP入口文件
require dirname(__FILE__) . '/ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单