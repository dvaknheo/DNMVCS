<?php
namespace DNMVCS;
trait DNWrapper
{
	protected static $objects=[];
	protected $_object_wrapping;
	protected function _wrap_the_object($object)
	{
		$this->_object_wrapping=$object;
	}
	protected function _call_the_object($method,$args)
	{
		return call_user_func_array([$this->_object_wrapping,$method],$args);
	}

	public static function W($object=null)
	{
		$caller=get_called_class();
		if($object==null){
			return self::$objects[$caller];
		}
		$self=new $caller();
		$self->_wrap_the_object($object);
		self::$objects[$caller]=$self;
		return $self;
	}
	public function __set($name,$value){
		$this->_object_wrapping->$name=$value;
	}
	public function __get($name){
		return $this->_object_wrapping->$name;
	}
}

//use with DNSingleton
trait DNStaticCall
{
	use DNSingleton;
	//remark ，method do not public
	public static function __callStatic($method, $params)
    {
		$classname=get_called_class();
        $class=$classname::G();
		return ([$class, $method])(...$params);
    }
}
trait DNSimpleSingleton
{
	protected static $_instances=[];
	public static function G($object=null)
	{
		if($object){
			self::$_instances[static::class]=$object;
			return $object;
		}
		$me=self::$_instances[static::class]??null;
		if(null===$me){
			$me=new static();
			self::$_instances[static::class]=$me;
		}
		return $me;
	}
}

class DNFuncionModifer
{
	protected $FunctionMap=[];
	public static function __callStatic($method, $params)
    {
		$temp=self::$FunctionMap[$method]??null;
		if(null==$temp){
			return ($method)(...$params);
		}
		list($func,$header,$footer)=$temp;
		if(null!==$header){($header)(...$params);}
		if(null!==$func){
			$ret=($func)(...$params);
		}else{
			$ret=($method)(...$params);
		}
		if(null!==$footer){($footer)(...$params);}
		return $ret;
    }
	public static function Assign($functionName,$callback=null,$header=null,$footer=null)
	{
		if(null===$callback && null===$header && null===$footer){
			unset(self::$FunctionMap[$functionName]);
			return;
		}
		self::$FunctionMap[$functionName]=[$callback,$header,$footer];
		
	}
}
function _HTTP_REQUEST($k)
{
	if(class_exists('\DNMVCS\SuperGlobal\REQUEST' ,false)){
		return SuperGlobal\REQUEST::Get($k);
	}
	return $_REQUEST[$k]??null;
}
function _url_by_key($url,$key_for_simple_route)
{
	$path=parse_url(DNRoute::G()->_SERVER('REQUEST_URI'),PHP_URL_PATH);
	$path_info=DNRoute::G()->_SERVER('PATH_INFO');
	if(strlen($path_info)){
		$path=substr($path,0,0-strlen($path_info));
	}
	if($url===null || $url==='' || $url==='/'){return $path;}
	$c=parse_url($url,PHP_URL_PATH);
	$q=parse_url($url,PHP_URL_QUERY);
	
	$q=$q?'&'.$q:'';
	$url=$path.'?'.$key_for_simple_route.'='.$c.$q;
	return $url;
}
class SimpleRoute extends DNRoute 
{
	public $options;
	protected $key_for_simple_route='_r';
	
	public function _URL($url=null,$innerCall=false)
	{
		if(!$innerCall && $this->onURL){return ($this->onURL)($url,true);}
		return _url_by_key($url,$this->key_for_simple_route);
	}
	public function init($options)
	{
		parent::init($options);
		$this->key_for_simple_route=$options['ext']['key_for_simple_route'];
		
		$path_info=_HTTP_REQUEST($this->key_for_simple_route)??'';
		$path_info=ltrim($path_info,'/');
		$this->path_info=$path_info;
	}
}
class SimpleRouteHook
{
	use DNSingleton;

	public $key_for_simple_route='_r';
	protected $onURL=null;
	public function onURL($url=null,$innerCall=false)
	{
		if(!$innerCall && $this->onURL){return ($this->onURL)($url,true);}
		return _url_by_key($url,$this->key_for_simple_route);
	}
	public function hook($route)
	{
		$route->setURLHandler([$this,'onURL']);

		$path_info=_HTTP_REQUEST($this->key_for_simple_route)??'';
		$path_info=ltrim($path_info,'/');
		$route->path_info=$path_info;
		$route->calling_path=$path_info;
	}
}
class StrictService
{
	use DNSingleton;
	public static function G($object=null)
	{
		$object=self::_before_instance($object);
		////
		global $_DNSingleton_Custumer_G;
		if($_DNSingleton_Custumer_G){
			return  ($_DNSingleton_Custumer_G)($object,static::class);
		}
		if($object){
			self::$_instances[static::class]=$object;
			return $object;
		}
		$me=self::$_instances[static::class]??null;
		if(null===$me){
			$me=new static();
			self::$_instances[static::class]=$me;
		}
		return $me;
	}
	
	public static function _before_instance($object)
	{
		if(!DNMVCS::G()->isDev){return $object;}
		$class=get_called_class();
		list($_0,$_1,$caller)=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
		$caller_class=$caller['class'];
		$namespace=DNMVCS::G()->options['namespace'];
		if(substr($class,0,0-strlen("LibService"))=="LibService"){
			do{
				if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){break;}
				if(substr($caller_class,0,0-strlen("Service"))=="Service"){break;}
				DNMVCS::ThrowOn(true,"LibService Must Call By Serivce");
			}while(false);
		}else{
			do{
				if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){
					DNMVCS::ThrowOn(true,"Service Can not call Service");
				}
				if(substr($caller_class,0,strlen("Service"))=="Service"){
					DNMVCS::ThrowOn(true,"Service Can not call Service");
				}
				if(substr($caller_class,0,strlen("\\$namespace\\Model\\"))=="\\$namespace\\Model\\"){
					DNMVCS::ThrowOn(true,"Service Can not call by Model");
				}
				if(substr($caller_class,0,strlen("Model"))=="Model"){
					DNMVCS::ThrowOn(true,"Service Can not call by Model");
				}	
				
			}while(false);
		}
		return $object;
	}	
}
class StrictModel
{
	use DNSingleton;
	public static function _before_instance($object)
	{
		
		if(!DNMVCS::G()->isDev){return $object;}
		list($_0,$_1,$caller)=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
		$caller_class=$caller['class'];
		$namespace=DNMVCS::G()->options['namespace'];
		do{
			if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){break;}
			if(substr($caller_class,0,0-strlen("Service"))=="Service"){break;}
			if(substr($caller_class,0,0-strlen("ExModel"))=="ExModel"){break;}
			DNMVCS::ThrowOn(true,"Model Can Only call by Service or ExModel!");
		}while(false);
		return $object;
	}
}

class StrictDBManager // extends DNDBManager 这里不需要了 需要多测试
{
	use DNWrapper;
	// bug ? _get ,_set ?
	public function __call($method,$args)
	{
		if(in_array($method,['_DB','_DB_W','_DB_R'])){
			$this->checkPermission();
		}
		return ($this->obj->$method)(...$args);
	}
	protected function checkPermission()
	{
		if(!DNMVCS::G()->isDev){return;}
		list($_0,$_1,$_2,$caller,$bak)=$backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,5);
		$caller_class=$caller['class'];
		$namespace=DNMVCS::G()->options['namespace'];
		$default_controller_class=DNMVCS::G()->options['default_controller_class'];
		do{
			if($caller_class==$default_controller_class){
				DNMVCS::ThrowOn(true,"DB Can not Call By Controller");
			}
			if(substr($caller_class,0,strlen("\\$namespace\\Controller\\"))=="\\$namespace\\Controller\\"){
				DNMVCS::ThrowOn(true,"DB Can not Call By Controller");
			}
			
			if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){
				DNMVCS::ThrowOn(true,"DB Can not Call By Service");
			}
			if(substr($caller_class,0-strlen("Service"))=="Service"){
				DNMVCS::ThrowOn(true,"DB Can not Call By Service");
			}
		}while(false);
	}
}
class DBExt extends DNDB
{
	//Warnning, escape the key by yourself
	protected function quote_array($array)
	{
		$this->check_connect();
		$a=array();
		foreach($array as $k =>$v){
			$a[]=$k.'='.$this->pdo->quote($v);
		}
		return implode(',',$a);
	}
	public function find($table_name,$id,$key='id')
	{
		$sql="select {$table_name} from terms where {$key}=? limit 1";
		return $this->fetch($sql,$id);
	}
	
	public function insert($table_name,$data,$return_last_id=true)
	{
		$sql="insert into {$table_name} set ".$this->quote_array($data);
		$ret=$this->execQuick($sql);
		if(!$return_last_id){return $ret;}
		$ret=$this->pdo->lastInsertId();
		return $ret;
	}
	public function delete($table,$id,$key='id')
	{
		throw new Exception("DNMVCS Fatal : override me to delete");
		$sql="delete from {$table_name} where {$key}=? limit 1";
		return $this->execQuick($sql,$id);
	}
	
	public function update($table_name,$id,$data,$key='id')
	{
		if($data[$key]){unset($data[$key]);}
		$frag=$this->quote_array($data);
		$sql="update {$table_name} set ".$frag." where {$key}=?";
		$ret=$this->execQuick($sql,$id);
		return $ret;
	}
}


class API
{
	protected static function GetTypeFilter()
	{
		return [
			'boolean'=>FILTER_VALIDATE_BOOLEAN  ,
			'bool'=>FILTER_VALIDATE_BOOLEAN  ,
			'int'=>FILTER_VALIDATE_INT,
			'float'=>FILTER_VALIDATE_FLOAT,
			'string'=>FILTER_SANITIZE_STRING,
		];
	}
	public static function Call($class,$method,$input)
	{
		$f=self::GetTypeFilter();
		$reflect = new ReflectionMethod($class,$method);
		
		$params=$reflect->getParameters();
		$args=array();
		foreach ($params as $i => $param) {
			$name=$param->getName();
			if(isset($input[$name])){
				$type=$param->getType();
				if(null!==$type){
					$type=''.$type;
					if(in_array($type,array_keys($f))){
						$flag=filter_var($input[$name],$f[$type],FILTER_NULL_ON_FAILURE);
						DNMVCS::ThrowOn($flag===null,"Type Unmatch: {$name}",-1);
					}
					
				}
				$args[]=$input[$name];
				continue;
			}else if($param->isDefaultValueAvailable()){
				$args[]=$param->getDefaultValue();
			}else{
				DNMVCS::ThrowOn(true,"Need Parameter: {$name}",-2);
			}
			
		}
		
		$ret=$reflect->invokeArgs(new $service(), $args);
		return $ret;
	}
}
class MedooSimpleInstaller
{
	public static function CreateDBInstance($db_config)
	{
		$dsn=$db_config['dsn'];
		list($driver,$dsn)=explode(':',$dsn);
		$dsn=rtrim($dsn,';');
		$a=explode(';',$dsn);
		$dsn_array['driver']=$driver;
		foreach($a as $v){
			list($key,$value)=explode('=',$v);
			$dsn_array[$key]=$value;
		}
		$db_config['dsn']=$dsn_array;
		$db_config['database_type']='mysql';
		
		return new Medoo($db_config);
	}
	public static function CloseDBInstance($db)
	{
		$db->close();
	}
}
class MyArgsAssoc
{
	protected static function GetCalledAssocByTrace($trace)
	{
		list($top,$_)=$trace;
		if($top['object']){
			$reflect=new ReflectionMethod($top['object'],$top['function']);
		}else{
			$reflect=new ReflectionFunction($top['function']);
		}
		$params=$reflect->getParameters();
		$names=array();
		foreach($params as $v){
			$names[]=$v->getName();
		}
		return $names;
	}
	
	public static function GetMyArgsAssoc()
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		return self::GetCalledAssocByTrace($trace);
	}
	
	public static function CallWithMyArgsAssoc($callback)
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		$names=self::GetCalledAssocByTrace($trace);
		return ($callback)($names);
	}
}
class ProjectCommonAutoloader
{
	use DNSingleton;
	protected $path_common;
	public function init($options)
	{
		$this->path_common=isset($options['fullpath_project_share_common'])??'';
		return $this;
	}
	public function run()
	{
		spl_autoload_register(function($class){
			if(strpos($class,'\\')!==false){ return; }
			$path_common=$this->path_common;
			if(!$path_common);return;
			$flag=preg_match('/Common(Service|Model)$/',$class,$m);
			if(!$flag){return;}
			$file=$path_common.'/'.$class.'.php';
			if (!$file || !file_exists($file)) {return;}
			require $file;
		});
	}
}
class ProjectCommonConfiger extends DNConfiger
{
	public $fullpath_config_common;

	public function init($path,$options)
	{
		$this->fullpath_config_common=isset($options['fullpath_config_common'])??'';
		return parent::init($path,$options);
	}
	protected function loadFile($basename,$checkfile=true)
	{	
		$common_config=[];
		if($this->fullpath_config_common){
			$file=$this->fullpath_config_common.$basename.'.php';
			if(is_file($file)){
				$common_config=(function($file){return include($file);})($file);
			}
		}
		$ret=parent::loadFile($basename,$checkfile);
		$ret=array_merge($common_config,$ret);
		return $ret;
	}
	
}
class FunctionDispatcher 
{
	use DNSingleton;
	
	protected $path_info;
	public $post_prefix='do_';
	public $prefix='action_';
	public $default_callback='action_index';
	public function hook($route)
	{
		$this->path_info=$route->path_info;
		$route->callback=[$this,'runRoute'];
	}
	public function runRoute()
	{
		//TODO 和
		$post=(DNRoute::G()->_SERVER('REQUEST_METHOD')==='POST')?$this->post_prefix:'';
		$callback=$this->prefix.$post.$this->path_info;
		if(is_callable($callback)){
			($callback)();
		}else{
			if(is_callable($this->default_callback)){
				($this->default_callback)();
			}else{
				(DNRoute::G()->on404Handler)();
				return false;
			}
		}
		return true;;
	}
	
}
class FunctionView extends DNView
{
	public $prefix='view_';
	public $head_callback;
	public $foot_callback;
	
	private $callback;
	
	public function init($path)
	{
		$ret=parent::init($path);
		$options=DNMVCS::G()->options;
		$this->head_callback=$options['function_view_head']??'';
		$this->foot_callback=$options['function_view_foot']??'';
		return $ret;
	}
	protected function includeShowFiles()
	{
		extract($this->data);
		
		if($this->head_callback){
			if(is_callable($this->head_callback)){
				($this->head_callback)($this->data);
			}
		}else{
			if($this->head_file){
				$this->head_file=rtrim($this->head_file,'.php').'.php';
				include($this->path.$this->head_file);
			}
		}
		
		$this->callback=$this->prefix.$this->view;
		if(is_callable($this->callback)){
			($this->callback)($this->data);
		}else{
			include($this->view_file);
		}
		
		if($this->head_callback){
			if(is_callable($this->foot_callback)){
				($this->foot_callback)($this->data);
			}
		}else{
			if($this->foot_file){
				$this->foot_file=rtrim($this->foot_file,'.php').'.php';
				include($this->path.$this->foot_file);
			}
		}
	}
}
class RouteWithSuperGlobal extends DNRoute
{
	public function init($options)
	{
		parent::init($options);
		$this->path_info=$this->_SERVER('PATH_INFO')??'';
		$this->request_method=$this->_SERVER('REQUEST_METHOD')??'';
		$this->path_info=ltrim($this->path_info,'/');
		return $this;
	}
	public function _SERVER($key)
	{
		return  SuperGlobal\SERVER::Get($key);
	}
	public function _GET($key)
	{
		return  SuperGlobal\GET::Get($key);
	}
	public function _POST($key)
	{
		return  SuperGlobal\POST::Get($key);
	}
	public function _REQUEST($key)
	{
		return  SuperGlobal\REQUEST::Get($key);
	}
}
class AppExt
{
	use DNSingleton;
	const DEFAULT_OPTIONS_EX=[
			'setting_file_basename'=>'setting',
			'key_for_simple_route'=>null,
			
			'use_function_view'=>false,
				'function_view_head'=>'view_header',
				'function_view_foot'=>'view_footer',
			'use_function_dispatch'=>false,
			'use_common_configer'=>false,
				'fullpath_project_share_common'=>'',
			'use_common_autoloader'=>false,
				'fullpath_config_common'=>'',
			'use_ext_db'=>false,
			//TODO 'use_super_global'=>false,
		];
	protected $is_installed=false;
	public function installHook($dn)
	{
		if($this->is_installed){return;}
		$this->is_installed=true;
		$this->afterInit();
		DNMVCS::G()->addAppHook([$this,'hook']);
	}
	protected function afterInit()
	{
		$dn=DNMVCS::G();
		$ext_options=$dn->options['ext'];
		
		$options=array_merge(self::DEFAULT_OPTIONS_EX,$ext_options);
		
		if($options['use_common_autoloader']){
			ProjectCommonAutoloader::G()->init($options)->run();
		}
		
		if($options['use_common_configer']){
			$dn->initConfiger(DNConfiger::G(ProjectCommonConfiger::G()));
			$dn->isDev=DNConfiger::G()->_Setting('is_dev')??$dn->isDev;
			// 可能要调整测试状态
		}
		if($options['use_function_view']){
			$dn->initView(DNView::G(FunctionView::G()));
		}
		if($options['use_ext_db']){
			$options['db_class'] =DBExt::class;
			$dn->initDBManager(DNDBManager::G());
		}
		if($options['key_for_simple_route']){
			SimpleRouteHook::G()->key_for_simple_route=$options['key_for_simple_route'];
			DNRoute::G()->addRouteHook([SimpleRouteHook::G(),'hook']);
		}
		if($options['use_function_dispatch']){
			DNRoute::G()->addRouteHook([FunctionDispatcher::G(),'hook']);
		}
	}
	public function hook()
	{
		$ext_options=DNMVCS::G()->options['ext']??[];
		$rewriteMap=$ext_options['rewriteMap']??null;
		if($rewriteMap){
			RouteRewriteHook::G()->install($ext_options['rewriteMap']);
		}
		$routeMap=$ext_options['routeMap']??null;
		if($rewriteMap){
			RouteMapHook::G()->install($ext_options['routeMap']);
		}
	}
	public function cleanUp()
	{
		$route=DNRoute::G();
		$route->calling_path='';
		$route->calling_class='';
		$route->calling_method='';
		$route->callback=null;
		$route->routeHooks=[];
		
		$view=DNView::G();
		$view->data=[];
		$view->head_file=null;
		$view->foot_file=null;
		$view->view=null;
		error_reporting($this->error_reporting_old);
		//TODO ob_cleanUp();
	}
}
//mysqldump -uroot -p123456 DnSample -d --opt --skip-dump-date --skip-comments | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' >../data/database.sql

