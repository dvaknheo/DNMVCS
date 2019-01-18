# 第一章 DNMVCS 介绍
## DNMVCS 是什么
一个 PHP Web 简单框架 
* 主要特点：比通常的 Model Controller View 多了 Service 。拟补了 常见 Web 框架少的缺层。
这个缺层导致了很糟糕的境地。你会发现很多人在 Contorller 里写一堆代码，或者在 Model 里写一堆代码。
使得网站开发者专注于业务逻辑。
* 耦合松散，扩展灵活方便，魔改容易
* 无第三方依赖，你不必担心第三方依赖改动而大费周折。
* 无 composer 环境也可运行，同时支持 composer 。
* 桩代码极小，你不必在一堆杂乱代码中开始，而是像普通库那样引用
* 不仅仅支持全站路由，还支持局部路径路由和非 PATH_INFO 路由,不需要配服务器也能用
* 小就是性能。（不过也上千行代码了）
* 为偷懒者写的。最少只需要引用一个文件，不做一大堆外部依赖。
* 替代 Codeiginter 这个PHP4 时代的框架，只限于新工程。
* 和 Swoole 整合实现高性能 web 服务器。代码基本不用改就兼容 swoole4 。
## 关于 Servivce 层
MVC 结构的时候，你们业务逻辑放在哪里？
新手放在 Controller ，后来的放到 Model ，后来觉得 Model 和数据库混一起太乱， 搞个 DAO 层吧。
可是 一般的 PHP 框架不提供这个功能。
所以，Service 按业务走，Model 层按数据库走，这就是 DNMVCS 的理念。
DNMVCS 的最大意义是思想，只要思想在，什么框架你都可以用
你可以不用 DNMVCS 实现 Controller-Service-Model 架构。
只要有这个思想就是理念成功了。
## 理解 DNMVCS 的原则
DNMVCS 层级关系图

```
		   /-> View
Controller --> Service ---------------------------------> Model   
					  \               \              /
					   \-> LibService --> ExModel --/
```
* Controller 按 URL 入口走 调用 view 和service
* Service 按业务走 ,调用 model 和其他第三方代码。
* Model 按数据库表走，基本上只实现和当前表相关的操作。
* View 按页面走
* 不建议 Model 抛异常
1. 如果 Service 相互调用怎么办?
添加后缀为 LibService 用于 Service 共享调用，不对外，如MyLibService
2. 如果跨表怎么办?，三种解决方案
	1. 在主表里附加，其他表估计用不到的情况。
	2. 添加后缀为 ExModel 用于表示这个 Model 是多个表的，如 UserExModel。
	3. 或者单独和数据库不一致如取名 UserAndPlayerRelationModel

## DNMVCS 做了什么

* 简单可扩展灵活的路由方式
	* 全站 PATH_INFO 模式
	* 局部 PATH_INFO 模式
	* GET 参数的路由模式
	* 路由表的路由模式

* 简单的视图
	* PHP 本身就是模版
	* 轻松处理页眉页脚
* 扩展接管默认错误处理
* 简单的配置类
	* setting 就是一个数组， config 就是动态配置
* 简单的加载类 
* 简单可扩充的数据库管理类
	* 支持主从(手动)
	* 可扩充
	* 轻松整合 Medoo
* 所有这些仅仅是在主类里耦合。
* Swoole http 服务器。
## DNMVCS 不做什么
* ORM ，和各种屏蔽 sql 的行为，根据日志查 sql 方便多了。 自己简单封装了 pdo 。你也可以使用自己的DB类。 你也可以用第三方ORM
* 模板引擎，PHP本身就是模板引擎。
* Widget ， 和 MVC 分离违背。
* 接管替代默认的POST，GET，SESSION 。系统提供给你就用，不要折腾这些。 *除非为了支持 swoole*
## DNMVCS 还要做什么
* 范例，例子还太简单了
* 更多的杀手级应用
## DNMVCS 的 缺点
1. 不优雅。万恶之源。 
2. 调用堆栈层级太少，不够 Java 。
3. 虽然实现了标准的 PSR-4 规范实现，但是还给懒鬼们开了后门。
4. 错误报告页面很丑陋。 想华丽自己写一个。不用 IDE 的直接看就懂。
5. 没有中间件。 重写 Controller 啊，要什么中间件。
6. 没有强大的全局依赖注入容器，只有万能的 G 函数。
7. 没有灵活强大的 AOP ，只有万能的 G 函数。
8. 这框架什么都没做啊。 居然只支持 PHP7 。

## 还有什么要说的

使用它，鼓励我，让我有写下去的动力
## 目录
如果你感兴趣，下面是本文档的所有目录

[toc]

# 第二章 DNMVCS 入门
## 安装
### composer 安装
```
composer create-project dnmvcs-project
```
设置  public 为 web 目录.
在浏览器中打开主页
得到欢迎页
```
Hello DNMVCS

Time Now is 2018-06-14T22:16:38+08:00
```

以上是简单例子，要查看 dnmvcs 有什么和能干什么，可以用下面的工程

```
composer create-project dnmvcs-fulltest
```
这个工程，里面有全部的测试样例。 *持续施工中*

如果从外部引用，你需要
```
composer require dnmvcs-framework
```
### 其他方式安装
compser 安装的原理是把 template 目录复制到工程目录，并更改一些代码。
你可以手动安装 DNMVCS
1. 下载 DNMVCS。
2. 把 web 目录设置为 template/public 目录。
4. 浏览器中打开主页出现相似欢迎页就表示基本成功
只不过顶行会多个警告提示
```
Don't run the template file directly
```
这警告提示请勿把 template 目录直接作为工程目录。

不推荐直接在 dnmvcs 目录里开始工程。

而是应该单独把 dnmvcs 放在独立的目录里，调整 public/index.php 的 require 语句指向 DNMVCS/DNMVCS.php

修改 config/setting.php ，如果少了就会会有提示：
```
DNMVCS Fatal: no setting file[【配置文件的完整路径】]!,change setting.sample.php to setting.php !
```
原因
*DNMVCS并非一定要外置设置文件，有选项可改为使用内置设置选项。满足单一文件模式的爱好*
### swoole 方式运行
如果你安装了 swoole 4.2.0 以及以上扩展。

在 工程的 bin 目录下运行命令行
```
php start_server.php
```
打开 127.0.0.1:9528 即可得到相同欢迎页面

### 后续的工作和可能省略的。

还有哪些没检查的？ 服务器配置 PATH_INFO 对了没有。 数据库也没配置和检查。

开始学习吧！
## 术语约定
$options 我们术语称为 DNMVCS 选项。和 setting.php 设置， config.php 配置 区分开来。

* options 选项，代码里的设置
* setting 设置，敏感信息
* config 配置，非敏感信息

文档约定我们直接省略 DNMVCS 的命名空间。

## 目录结构

推荐的工程的目录结构
```
+---app                 // psr-4 标准的自动加载目录
|   +---Base            // 基类放在这里
|   |      App.php      // 默认框架入口文件
|   +---Controller      // 路由控制器
|   |       Main.php    // 默认控制器入口文件
|   +---Model           // 模型放在里
|   |       TestModel.php   // 测试 Model 
|   \---Service         // 服务放在这里
|           TestService.php //测试 Service
+---bin                 //
|       start_server.php //启动 swoole
+---config              // 配置文件 放这里
|       config.php      // 配置，目前是空数组
|       setting.php     // 设置，敏感文件，不放在版本管理里
|       setting.sample.php      // 设置，去除敏感信息的模板
+---lib                 // 手动加载的文件放这里(非必要)
|       ForImport.php   //用于测试导入文件
+---view                // 视图文件放这里
|   |   main.php        // 视图文件
|   \---_sys            // 系统错误视图文件放这里
|           error-404.php  // 404 
|           error-500.php  // 500 出错了
|           error-debug.php // 调试的时候显示的视图
|           error-exception.php // 出异常了，和 500 不同是 这里是未处理的异常。
\---public              // 网站目录放这里
		index.php       // 主页面
```
工程的目录结构并非不可变更。
config,view 目录可以通过选项（ $options['path_config'],$options['path_view']）调整（如调到 app 目录下）。
lib 目录可以不要（如果你没用到 DNMVCS::Import）。
## 代码解读

::/public/index.php  入口 PHP 文件,内容如下

```php
<?php
require(__DIR__.'/../vendor/autoload.php');

$path=realpath(__DIR__.'/..');
$options=[
	'path'=>$path,
];
// if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ echo "<div>Don't run the template file directly </div>\n"; }
// if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ $options['setting_file_basename']=''; }
\DNMVCS\DNMVCS::RunQuickly($options);
// \DNMVCS\DNMVCS::G()->init($options)->run();
```
被注释掉部分 和 实际调用部分实际相同。是个链式调用。
\DNMVCS\DNMVCS::G(); 单例模式。 
\DNMVCS\DNMVCS 主类，在后面有好多其他方法详细介绍。
这些方法背后是不同的你可以改写的类。

init($options);初始化，这部分入口选项见后面章节【 DNMVCS 配置和选项】详细介绍。

### 设置文件

默认情况下会读取 ::/config/setting.php 里的设置。
你可以用过 setting_file_basename='' 使得不读取设置文件。
工程的设置文件样例 setting.sample.php 。
```php
<?php
// copy me to "setting.php"
return [
	'is_dev'=>true,
	'database_list'=>[[
		'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
		'username'=>'???',
		'password'=>'???',
	],],
];
```
关于 is_dev ，这个标记用于判断是否在开发状态，影响 DNMVCS::Developing();
database_list，配置多个数据库。

## 开始自己的代码
我们要显示当前时间。以 /about/foo 为例，使用无命名空间模式，这样能省掉一些代码。
用 :: 表示工程目录
### View 视图
先做出要显示的样子。
::/view/about/foo.php
```php
<!doctype html><html><body>
<h1>test</h1>
<div><?=$var ?></div>
</body></html>
```
### Controller控制器
写 /about/foo 控制器对应的内容

::/app/Controller/about.php
```php
<?php
class DNController
{
	public function foo()
	{
		$data=[];
		$data['var']=MiscService::G()->foo();
		\DNMVCS\DNMVCS::Show($data);
	}
}
```
非 swoole 模式下，控制器可以不用和路由一样的名称，用默认的 DNController

在控制器里，我们调用了 MiscService 这个服务。
MiscService 调用 NoDB_MiscModel 的实现。此外，我们要调整 返回值的内容
我们用 DNSingleton单例。

### Service 服务
业务逻辑层，调用不定个数的 Model

::/app/Service/MiscService.php
```php
<?php
class MiscService
{
	use \DNMVCS\DNSingleton;
	public function foo()
	{
		$time=NoDB_MiscModel::G()->getTime();
		$ret='Now is '.$time;
		return $ret;
	}
}
```
### Model 模型

完成 NoDB_MiscModel
Model 类是实现基本功能的
这里用 NoDB_ 表示和没使用到数据库

::/app/Model/NoDB_MiscModel.php
```php
<?php
class NoDB_MiscModel
{
	use \DNMVCS\DNSingleton;
	public function getTime()
	{
		return DATE(DATE_ATOM);
	}
}
```
### 结果
访问 about/foo
```html
<!doctype html><html><body>
<h1>test</h1>
<div>Now is 2018-11-27T10:14:13+08:00</div>
</body></html>
```
### 附加
在初始化里我们要做其他事情。
根据 base_class 选项，我们有。

::/app/Base/App.php 
```php
<?php
namespace MY\Base;
class App extends \DNMVCS\DNMVCS
{
	public function init($options=array())
	{
		// switch  me , DNView::G(MYView::G());
		parent::init($options);
		// switch  me , $this->initView(DNView::G(MYView::G());
		return $this;
	}
}
```
可以在这个类做覆盖 DNMVCS 类的事。


这就是 DNMVCS 下的简单流程了。其他开发类似。

这个例子在fulltest 里有
## 理解路由和控制器
DNMVCS 的控制器有点像 CodeInigter，不需要继承什么，就这么简单。
1. 按名字切分

	甚至连名字都不用，用默认的 DNController 就够了。
	而且支持子命名空间多级目录。如果开启简单模式，也可用 __ 双下划线代替 \ 切分。
2. 处理同名
	Swoole 兼容不可以用这种偷懒模式)
	DNController 重名了怎么办，比如我要相互引用？ 
	1. 那是你不应该这么做，
	2. 你也可以采取名称对应的类，而不偷懒啊啊。

3. DNMVCS 还支持路由映射。 

	正则用 ~
	要指定 GET/POST 在最前面加http 方法.

	\DNMVCS\DNMVCS::G()->assignRoute('GET ~article/(\d+)','article->get');

	*用->表示类调用而不是静态调用*
	DNMVCS 支持 Paramter，你可以在设置里关掉。

	Parameter 切片会直接传递进 方法里作为参数。

	路由表里，用正则切分会传递进方法，不管是否开启 enable_paramters
	
4. 不用 PATH_INFO
	比如 路由不用 path_info 用 $_GET['_r'] 等，很简单的。
	$options['ext']['key_for_action']='_r' 开启 _GET 模式路由
	如果你想加其他功能，可以 添加钩子， 继承 DNRoute 自行扩展类。  两种方式灵活扩展

run() 方法开始使用路由。 如果你不想要路由。只想要特定结构的目录， 不调用 run 就可以了。
比如只想要 db 类等等。
## 常见任务：URL 地址
如果不是全站 PATH_INFO 模式， web 框架获取某个 URL 地址是常见任务。
DNMVCS::URL($url) 函数就是用于这个任务。
使得你不必关系是用在 /index.php 或者 /somefolder/index.php 里用 PATH_INFO 。
DNMVCS::URL('about/foo') 都会得到正确的 URL 地址。

*进阶，接管 URL 函数  .*
## 常见任务：View 和 View 的包含

DNMVCS::Show($data,$view=null) 用于 View 的显示， $view 为空的时候，会根据当前 URL 获得相关 view 文件。
当要在 View 里包含的时候，用 DNMVCS::ShowData($view,$data=null); $data 为 null 的时候，会把当前view 数据带过去。

*进阶，接管 View .*

## 常见任务：读取配置和设置
DNMVCS::Setting($key) 用于读取 config/setting.php 的 $key 。
DNMVCS::Config($key,$basename='config')用于读取 config/$basename.php  $key 。
DNMVCS::LoadConfig($basename='config')用于载入 config/$basename.php 的内容。
设置是敏感信息。而配置是非敏感
*进阶，更多配置和设置相关 .*
## 常见任务： URL 重写
$options['rewrite_map'] 用于重写 url . 以 ~ 开始的表示正则，同时省略 / 必须转义的。 用 $ 代替 \ 捕获。
$options['route_map'] ,用于 回调式路由， 除了  :: 表示类的静态方法，还 -> 符号表示的是类的动态方法。
key  可以加 GET POST 方法。
## 常见任务：重写错误页面

错误页面在 ::view/_sys/ 目录下 里。你可以修改相应的错误页面方法。
比如 404 是 view/404.php 。
你可以更改 DNMVCS 的报错页面。
无错误页面模式，会自己显示默认错误。
你也可以修改 $options['error_404'] 指向一个回调函数来处理 404 错误，其他错误类似。

*进阶 错误管理.*
## 常见任务： 使用数据库
使用数据库，在 DNMVCS 设置里正确设置
```php
return [
'database_list'=>[[
		'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
		'username'=>'???',
		'password'=>'???',
	],],
];
```
database_list 是个数组，包含多个数据库配置
然后在用到的地方调用 DNMVCS::DB($tag=null) 得到的就是 DNDB 对象，用来做各种数据库操作。
$tag 对应 $setting['database_list'][$tag]。

你不必担心每次框架初始化会连接数据库。只有第一次调用 DNMVCS::DB() 的时候，才进行数据库类的创建。

DB 的使用方法，看后面的参考。
示例如下
```php
$sql="select 1+? as t";
$ret=DNMVCS::DB()->fetch($sql,2);
var_dump($ret);
```

进阶内容

DB 类仅仅是简单的封装 PDO ，作为主程序员，可能要重新调整。
DNMVCS 的默认数据库是 DB ,DB 功能很小，兼容 Medoo 这个数据库类。

你可以用更习惯的类。
*第七章将详细讲解数据库*
## 常见任务： 跳转
* DNMVCS::ExitJson($data) 输出 json 。
* DNMVCS::ExitRedirect($url) 用于 302 跳转。
* DNMVCS::ExitRouteTo($url) 相当于 302 跳转到 DNMVCS::URL($url);
* DNMVCS::Exit404 显示404页面。

## 常见任务： HTML 编码辅助函数
* DNMVCS::H($str)   Html编码. 更专业的有 Zend\Escaper。
* DNMVCS::RecordsetH 对一个 RecordSet 加 html 编码
* DNMVCS::RecordsetURL  对  RecordSet 加 url 转换
*进阶：把 html 编码替换成 Zend\Escaper .*
## 常见任务： 抛异常
DNMVCS::ThrowOn($flag,$message,$code);
等价于 if(!$flag){throw new DNException($message,$code)}
这是 DNMVCS 应用常见的操作。

## 常见任务： 和其他框架的整合
DNMVCS 整合其他框架： 
```php
<?php
	$options['error_404']=function(){};
	$flag=DNMVCS::G()->init($options)->run();
	if($flag){ return; }
	// 后面是其他框架代码
```
原理是由其他框架去处理 404。
其他框架整合 DNMVCS ,则在相应处理 404 的地方开始

## 总结常见任务
把这些常见任务完成了， DNMVCS 的静态函数就都看完了。

从难度低到高，大概是这样的级别以实现目的
1. 使用默认选项实现目的 
2. 只改选项实现目的
3. 调用 DNMVCS 类的静态方法实现目的
4. 调用 DNMVCS 类的动态方法实现目的
6. 调用扩展类，组件类的动态方法实现目的
7. 继承接管特定类实现目的
8. 魔改，硬改 DNMVCS 的代码实现目的


# 第三章 DNMVCS 配置和选项

这里是关于 DNMVCS 能用的选项介绍

	init($options) 方法的参数是可配置的，默认设置是分三个类别的组合。
	多余的缩进里的选项是不建议修改的。
	下面是默认的配置详解，为以下几个数组的合并

```php
const DNAutoLoader::DEFAULT_OPTIONS=[
	'path'=>null,                       // 共享基本路径配置
	'namespace'=>'MY',                  // 默认的命名空间，你可以自定义工程的命名空间
		'path_namespace'=>'app', 	    // 命名空间根目录
	'with_no_namespace_mode'=>true,     // 简单模式，无命名空间直接 controller, service,model
		'path_no_namespace_mode'=>'app', // 简单模式的基本目录
	'skip_system_autoload'=>false,      // 如果是 composer 加载，会使用 composer 来加载系统库
	'skip_app_autoload'=>false,         // 在工程的 composer.json 你指定了app 的 namespace 后设置为 true
];
```
autoload 自动加载相关的选项

```php
const DNMVCS::DEFAULT_OPTIONS=[
	'path'=>null,                       // 共享基本路径配置
	'namespace'=>'MY',                  // 共享命名空间配置
	'base_class'=>'Base\App',           // override 重写 系统入口类代替 DNMVCS 类。 \ 开头表示绝对 namespace
		'path_view'=>'view',            // 视图目录，或许会有人改到 app/View
		'path_config'=>'config',        // 配置目录，或许会有人改到 app/View
		'path_lib'=>'lib',              // 用于手动导入 DNMVCS::Import() 的类的目录
	'setting'=>[],        				// 设置，设置文件里填写的将会覆盖这一选项
	'all_config'=>[],        			// 配置，每个配置用 key  分割。
		'setting_file_basename'=>'setting',        // 设置的文件名，如果为'' 则不读取设置文件
	'is_dev'=>false,					// 是否在开发状态，设置文件里填写的将会覆盖这一选项

	'rewrite_map'=>[],                  // url 重写列表, 如果不为空则使用到扩展的 DNMVCSExt
	'route_map'=>[],                    // 映射模式的 列表, 如果不为空则使用到扩展的 DNMVCSExt
		'error_404'=>'_sys/error-404',  // 404 错误处理，传入字符串表示用的 view,如果传入 callable 则用 callback,view 优先
		'error_500'=>'_sys/error-500',  // 500 代码有语法错误等的页面，和 404 的内容一样。和前面类似
		'error_exception'=>'_sys/error-exception',  // 默认的异常处理。和前面类似
		'error_debug'=>'_sys/error_debug',          // 调试模式下出错的处理。和前面类似

		'db_create_handler' =>'',		// 创建DB 的回调 默认用 DB::class
		'db_close_handler' =>'', 		// 关闭DB 类的回调。
	'database_list'=>[],				// 数据库列表

	'ext'=>[],                          //默认不使用扩展，如果不为空则使用到扩展的 DNMVCSExt
	'swoole'=>[],                       // swoole_mode 模式的选项，在 swoole 这章里介绍。
];
```
	关于 base_class 选项。
	你可以写 DNMVCS 的子类 用这个子类来替换DNMVCS 的入口。留空或类不存在为使用默认DNMVCS 详情见后面。
	ext 会加载 DNMVCSExt 实现一些扩展性的功能。后面章节会说明。

```php
const DNRoute::DEFAULT_OPTIONS=[
	'path'=>null,                       // 共享基本路径配置
	'namespace'=>'MY',                  // 共享命名空间配置
	'with_no_namespace_mode'=>true,     // 简单模式，无命名空间直接 controller, service,model
	'prefix_no_namespace_mode'=>''      // 无命名空间模式时候的类名前缀
	'enable_paramters'=>false,          // 支持切片模式
	'enable_post_prefix'=>true,         // 把 POST 的 映射为 do_$action 方法
	'prefix_post'=>'do_',               // 把 POST 的 映射为 do_$action 方法
		'path_controller'=>'app/Controller',        //controller 的目录
		'namespace_controller'=>'MY\Controller',    //controller 的命名空间 MY\Controller
		'default_controller_class'=>'DNController', //默认 controller 名字为 DNController
		'disable_default_class_outside'=>false,     // 屏蔽  Main/index  第二访问模式
];
```

	这段是和路由相关的。namespace 和 with_no_namespace_mode 选项也会影响路由。
	enable_paramters 切片模式。 使得 foo->a() 也支持 foo/a/b/c 这样的路由，而不是 404。
	enable_post_prefix 默认把 POST 的方法映射到 do_$action 这样处理起来方便些。
	default_controller_class 可以设置为空

## 全部默认选项
```php
const DEFAULT_OPTIONS=[
			'path'=>null,
			
			'namespace'=>'MY',
			'path_namespace'=>'app',
			
			'with_no_namespace_mode'=>true,
			'path_no_namespace_mode'=>'app',

			'skip_system_autoload'=>false,
			'skip_app_autoload'=>false,

			'enable_paramters'=>false,
			'prefix_no_namespace_mode'=>'',
			'path_controller'=>'app/Controller',
			'namespace_controller'=>'Controller',
			'default_controller_class'=>'DNController',
			
			'enable_post_prefix'=>true,
			'prefix_post' => 'do_',
			'disable_default_class_outside'=>false,
			
			'base_class'=>'Base\App',
			'path_view'=>'view',
			'path_config'=>'config',
			'path_lib'=>'lib',
			
			'is_dev'=>false,
			'all_config'=>[],
			'setting'=>[],
			'setting_file_basename'=>'setting',
			
			'db_create_handler'=>'',
			'db_close_handler'=>'',
			
			'rewrite_map'=>[],
			'route_map'=>[],
			
			'error_404'=>'_sys/error-404',
			'error_500'=>'_sys/error-500',
			'error_exception'=>'_sys/error-exception',
			'error_debug'=>'_sys/error_debug',
			
			'ext'=>[],
			'swoole'=>[],
		];
```
# 第四章 DNMVCS 核心类
这一章节是说明 DNMVCS.php 里的 DNMVCS 核心类，和附属组件类。
主要的说明文档
## 基本方法
static G($object=null)

	G 单例函数是整个系统最有趣的地方。
	传入 $object 将替代默认的单例。
	使得调用形式不变，但实现方式变更
	比 PHP-DI简洁，后面的文档 会有详细介绍
init($options=[])
	初始化，这是最经常子类化完成自己功能的方法。
	你可以扩展这个类，添加工程里的其他初始化。
run()

	开始路由，执行。这个方法拆分出来是为了特定需求, 比如只是为了加载一些类。
	比如 swoole 下不同协程的运行。
	如果404 则返回false;其他返回 true
static SG()
	SuperGlobal 的缩写
	返回 DNSuperGlobal 对象
	你可以 DNMVCS::SG()->_GET得到的就是 swoole 也可用的 $_GET 数组。
	类似的还有 _GET,_POST,_REQUEST,_SERVER，_ENV,_COOKIE,_SESSION
	注意 GLOBALS 数组不可用。
## 运行模式

RunQuickly($options=[])

	DNMVCS::RunQuickly ($options) 相当于 DNMVCS::G()->init($options)->run();
RunOneFileMode($optionss=[],$init_function=null)

	单一文件模式，不需要其他文件，设置内容请放在
	$options['setting'] 里
	$init_function 用于初始化之后，run 前调用
RunWithoutPathInfo()

	不需要 PathInfo 的模式。用 _r 来表示 Path_Info
RunAsServer($server_options,$dn_options,$server=null)

	运行 swoole http 服务器
### 单文件模式
```php
\DNMVCS\DNMVCS::RunOneFileMode([]);
```
不想依赖这么多，一个文件解决？可以。
### 不用 PATH_INFO 的模式

```php
\DNMVCS\DNMVCS::RunWithoutPathInfo([]);
``` 
用 _r 来做 path_info

## 常用静态方法

这些方法因为太常用，所以静态化了。
包括 视图view,路由，数据库，配置 ，

Show($data=[]],$view=null)

	显示视图 
	视图的文件在 ::view 目录底下。你可以通过选项 path_view 调整
	为什么数据放前面，DN::Show(get_defined_vars());把 controller 的变量都整合进来，并用默认路径作为 view 文件。
	实质调用 DNView::G()->_Show
ShowBlock($view,$data=null)

	展示一块 view ，用于 View 里嵌套其他 View 或调试的场合。
	展示view不理会页眉页脚，也不做展示的后处理，如关闭数据库。
	注意这里是 $view 在前面， $data 在后面，和 show 函数不一致哦。
	如果 $data===null 那么会继承上级的 view 数据
	实质调用 DNView::G()->_ShowBlock
DB($tag=null)

	数据库
	数据库管理类 DNManager 里配置的数据库类
	实质调用 DBManager::G()->_DB();
DB_W()

	返回写入用的的数据库 $database_list[0] 配置的数据库
	默认和 DB() 函数一样
	实质调用 DBManager::G()->_DB_W();
DB_R()

	返回写入用的的数据库 $database_list[1] 配置的数据库
	实质调用 DBManager::G()->_DB_R();
URL($url)

	获得调整路由后的url地址 
	当你重写 DNRoute 类后，你可能需要重写这个方法来展示
	比如 simple_route_key 开启后， URL('class/method?foo=bar') 
	将会是 ?r=class/method&foo=bar ，而不是 /class/method?foo=bar
	如果是 / 开始的 URL ，将是从网站根目录开始。

	实质调用 DNRoute::G()->_URL();
Parameters()

	获得路径切片 
	当用正则匹配路由的时候，匹配结果放在这里。
	如果开启了 eanbale_parameter 匹配选项也会在这里。
	这会使得 /about/foo/123/456 路由调用方法为 => about->foo(123,456);

	实质调用 DNRoute::G()->_Parameters();
Setting($key)

	读取设置
	设置在 ::/config/setting.php 里，php 格式
	配置非敏感信息，放在版本管理中，设置是敏感信息，不保存在版本管理中
	实质调用 DNConfig::G()->_Setting();
Config($key,$file_basename='config')

	读取配置 
	配置放在 config/$file_basename.php 里，php 格式
	配置是放在非敏感信息，放在版本管理中
	实质调用 DNConfig::G()->_Config();
LoadConfig($file_basename)

	加载其他配置
	如果很多配置文件，手动加载其他配置
	实质调用 DNConfig::G()->LoadConfig();
ExitJson($ret)

	打印 json_encode($ret) 并且退出。
	这里的 json 为人眼友好模式。

	实质调用 DNMVCSExt::G()->_ExitJson();
ExitRedirect($url)

	跳转到另一个url 并且退出。
	实质调用 DNMVCSExt::G()->_ExitRedirect();
ExitRouteTo($url)

	跳转到 URL()函数包裹的 url。
	应用到 DNMVCSExt::G()->ExitRedirect(); 和 DNRoute::G()->URL();
	高级开发者注意，这是静态方法里处理的，子类化需要注意
Exit404()

	404 退出， 实质调用DNMVCS::G()->onShow404. 后 exit.
ThrowOn($flag,$message,$code=0);

	如果 flag 成立则抛出 DNException 异常。
	减少代码量。如果没这个函数，你要写
	if($flag){throw new DNException($message,$code);}
	折腾
	如果是你自己的异常类 ，可以 use DNThrowQuickly 实现 ThrowOn 静态方法。
DI($name,$object=null)

	你们想要的 container。如果 $object 不为null 是写，否则是读。
	实质调用 DNMVCSExt::G()->_DI();
Import($file)

	手动导入默认lib 目录下的包含文件
	实质调用 static::G()->_Import();
## 状态判定
Developing()

	判断是否在开发状态。默认读设置里的 is_dev ，
InSwoole()

	判断是否在Swoole环境
IsRunning

	判断是否已经开始运行。
	实质调用 DNRuntimeState::G()->isRunning();

## 取代系统函数
和系统同名的静态函数，用于替换系统函数，以适应  swoole 环境

session_start(array $options=[])

	session 会话函数
	实质调用 DNSuperGlobal::G()->_StartSession();
session_destroy()

	实质调用 DNSuperGlobal::G()->_DestroySession();
session_set_save_handler

	这个函数只实现了 SessionInterface 的参数调用，没实现单独的调用
	实质调用 DNSuperGlobal::G()->_DestroySession();
header

	同系统的 header 方法
	注意判断了非 web 状态下不使用
	实际调用 static::G()->_header()
setcookie
	同系统的 setcookie 方法
	注意判断了非 web 状态下不使用
	实际调用 static::G()->_setcookie()
exit_sytesm($code=0)

	代替 exit();
	实际调用 static::G()->exit_sytesm()
## 独立杂项静态方法
这几个方法独立，为了方便操作，放在这里。

H(&$str)

	html 编码 这个函数常用，所以缩写。H 函数还支持 数组
	实际调用 static::G()->_H()
RecordsetH(&$data,$cols=[])

	给 sql 查询返回数组 html 编码
	$cols 指定 要转码的列名
	实际调用 static::G()->_H()
RecordsetURL(&$data,$cols_map=[]) 

	给 sql 返回数组 加url 比如  url_edit=>"edit/{id}",则该行添加 url_edit =>DN::URL("edit/".$data[]['id']) 等类似。
	实际调用 static::G()->_H()
HasInclude($file)

	判断文件是否已经包含
## 非静态方法
这里的方法偶尔会用到，所以没静态化 。
assign 系列函数，都有两个模式 func(\$map)，和 func(\$key,\$value) 模式方便大量导入。

assignRoute($route,$callback=null)

	给路由加回调。
	关于回调模式的路由。详细情况看之前介绍
	和在 options['route'] 添加数据一样
assignRewrite($old_url,$new_url=null)

	rewrite  重写 path_info
	不区分 request method , 重写后可以用 ? query 参数
	~ 开始表示是正则 ,为了简单用 / 代替普通正则的 \/
	替换的url ，用 $1 $2 表示参数

getRouteCallingMethod()

	获得路由中正在调用的方法。
	用于控制器里判断方法以便于权限管理。
	也适用于重写URL后判断是否是直接访问

	实质调用 DNRoute::G()->getRouteCallingMethod
addRouteHook($hook,$prepend=false,$once=true)

	下钩子扩展 route 方法
	实质调用 DNRoute::G()->addRouteHook
setViewWrapper($head_file=null,$foot_file=null)

	给输出 view 加页眉页脚 
	view 里的变量和页眉页脚的域是一样的。
	页眉页脚的变量和 view 页面是同域的。
	有时候你需要 setViewWrapper(null,null) 清理页眉页脚

	实质调用 DNView::G()->setViewWrapper
assignViewData($key,$value=null)

	给 view 分配数据，
	这函数用于控制器构造函数添加统一视图数据
	实质调用 DNView::G()->assignViewData
assignExceptionHandler($classes,$callback=null)

	分配特定异常回调。
	用于控制器里控制特定错误类型。
	实质调用 DNExceptionManager::G()->assignExceptionHandler
setMultiExceptionHandler(array $classes,$callback)

	多个特定异常回调用于多个异常统一到同一个回调的情况。
	实质调用 DNExceptionManager::G()->setMultiExceptionHandler
setDefaultExceptionHandler($calllback)

	接管默认的异常处理，所有异常都归回调管，而不是显示 500 页面。
	用于控制器里控制特定错误类型。比如 api 调用
	实质调用 DNExceptionManager::G()->setDefaultExceptionHandler
assignPathNamespace($path,$namespace=null)
	
	分配自动加载的命名空间的目录。
	实质调用 DNAutoLoader::G()->assignPathNamespace
setBeforeRunHandler($before_run_handler)

	在run之前执行回调。 SwooleHttpServer 用到这个。
_header
	实现 header();
_setcookie
	setcookie();
_exit_system
	实现 exit();
## 事件方法
实现了默认事件回调的方法。扩展以展现不同事件的显示。

onBeforeShow()

	在输出 view 开始前处理.
	默认处理空模板为当前类和方法，默认关闭数据库。
	因为如果请求时间很长，页面数据量很大。没关闭数据库会导致连接被占用。
onShow404()

	404 回调。这里没传各种参数，需要的时候从外部获取。
onException($ex)

	发生未处理异常的处理函数。显示 exception 或 500 页面
onDevErrorHandler($errno, $errstr, $errfile, $errline)

	处理 Notice 错误。
## 组件初始化
初始化组件，供扩展组件时初始化用。

initConfiger(DNConfiger $configer)

	初始化配置。
	配置路径。
	$options['setting'],$options['all_configs'] 的数据会加入初始化
initView(DNView $view)

	初始化视图。 做了两件事
	配置路径
	绑定 onBeforeshow
initRoute(DNRoute $route)

	初始化路由 配置选项。
	绑定 onShow404
initDBManager(DNDBManger $dbm)

	初始化数据库管理器,skip_db 选项则跳过
	db_create_handler ，db_close_handler 用在这里。
	db_create_handler($config,$tag)
	db_close_handler($db,$tag)
initMisc()

	如果 选项  ext 启用 DNMVCSExt

# 第五章 DNMVCS 核心组件

## trait DNSingleton | 子类化和 G 方法
**很重要的一节**
```php
<?php
trait DNSingleton
	public static function G($object=null):object
```
G 函数，单例模式。

如果没有这个 G 方法 你可能会怎么写代码：
```php
(new MyClass())->foo();
```
绑定 DNSingleton 后，这么写
```php
MyClass::G()->foo();
```
另一个隐藏功能：
```php
MyBaseClass::G(new MyClass())->foo();
```
MyClass 把 MyBaseClass 的 foo 方法替换了。
接下来后面这样的代码，也是调用 MyClass 的 foo2.
```php
MyBaseClass::G()->foo2();
```
**注意:但是静态方法不替换，请注意这一点。**

为什么不是 GetInstance ? 因为太长，这个方法太经常用。

所以你可以扩展各种内部类以实现不同功能。

比如你要自己的路由方法.在 init 里。
```php
//MYMVCS::init
public function init($options=[])
{
	DNRoute::G(MYRoute::G());
	parent::init($options);
}
```
这样 MYRoute 就接管了 DNRoute 了。


DNView::G(AdminView::G());
这样 AdminView 就接管了 DNView 了。

G 函数的缺点：IDE 上没法做类型提示，这对一些人来说很重要。

service , model 上 用  static 函数代替 G 函数实例方式或许也是一种办法


组件在后续使用，记得初始化：

DNMVCS 一共有 5个组件初始化。 DNExceptionManager DNConfig DNView DNRoute

*因为 autoloader 不建议替换，所以没有 initAutoloader();*

下面就是个初始化 route 和 view 的例子。
```php
<?php
namespace MY\Base;
class App extends \DNMVCS\DNMVCS
{
	public function init($options=array())
	{
		\DNMVCS\DNRoute::G(Route::G());
		parent::init($options);
		$this->initView(\DNMVCS\DNView::G(View::G()));
		return $this;
	}
}
```
因为 MY\Base\Route 在初始化之前替换，所以不必再次初始化。
而 MY\Base\View 在初始化之后调用，所以需要手动初始化。
## trait DI
简单的容器包装

## DNException 异常类 | trait DNThrowQuickly
使用 trait DNThrowQuickly
```php
MyException::ThrowOn($flag,$message,$code);
```
等价于下面，少写了好多东西
```php
if($flag){throw new MyException($message,$code);}
```
注意到这会使得 debug_backtrace 调用堆栈不同。
你自己的异常类应该 use DNThrowQuickly 没必要继承 DNException。
原因是你应该只处理你自己熟悉的异常

## 异常管理 DNExceptionManager

	异常管理类一般不用接管。
init(callback $exception_handler,$dev_error_handler)

	初始化
setDefaultExceptionHandler($default_exception_handler)

	设置默认异常处理
assignExceptionHandler($class,$callback=null)

	//分配异常处理
setMultiExceptionHandler(array $classes,$callback)
	
	//
checkAndRunErrorHandlers($ex,$inDefault)

	这个函数比较特殊 ,一般你不会调用他，用于检查是不是错误处理已经被接管了。
## DNConfiger 配置类
init($path)

_Setting($key)
_Config($key,$file_basename='config')
_LoadConfig($file_basename='config')

 protected function loadFile($basename,$checkfile=true)

	DNConfiger 类获得配置设置
## DNView 视图类
init($path)
	
	初始化,设置路径
_Show($data=[],$view)

	显示视图
_ShowBlock($view,$data=null)

	显示内容
assignViewData($key,$value=null)

setBeforeShow($callback)
setViewWrapper($head_file,$foot_file)
protected function prepareFiles()
## DNRoute 路由类
这应该会被扩展,加上权限判断等设置
路由类是很强大扩展性很强的类。

_URL($url=null)

	获得 URL地址
defaultURLHandler($url=null)

	默认的 URL 地址
_Parameters()

	获得切片
init($options)

	初始化    
set404($callback)

	set404 设置404 回调
setURLHandler

	替换 URL()函数的实现。   
getURLHandler
	获得 URL()函数的实现。
addRouteHook($hook,$prepend=false)
	
	添加路由的hook,$prepend  在最前面加
	run()

getRouteCallingPath()

getRouteCallingClass()

getRouteCallingMethod()

	以上三组，是当前路径，当前类，当前方法。
	当前方法用于权限的判断。如跳过login 方法其他都要权限。
	当前类如果为空，说明是 rewrite 过来的。
	当前路径用于如果是切片的，找回未切片的路径。
protected getRouteHandleByFile

	//
protected getCurrentClassAndMethod

	//
protected getObecjectToCall($class_name)

	//
protected getMethodToCall($obj,$method)

	//
protected includeControllerFile

	以上是内部方法。
## DNDBManager 数据库管理类
init($database_config_list=[])

	初始化，在 DNMVCS::initDBManger() 中被调用。
setDBHandler($db_create_handler,$db_close_handler=null)

	安装DB类
	$db_create_handler($config,$tag):$db 返回 DB 实例。方便扩展.
	$db_close_handler($db,$tag) 关闭数据库
	使用场合，比如用自己公司的 DB 类，要在这里做一个封装。
setBeforeGetDBHandler($before_get_db_handler)
	
	设置 在 DB()函数前执行 $before_get_db_handler($tag)
_DB($tag=null)

	返回 DB 实例，如果 tag -null 则用 0 号数据库。
_DB_W()
	
	返回写入用的数据库 
_DB_R()

	返回读取用的数据库
closeAllDB()

	关闭所有数据，依次调用 $db_close_handler
	在 DNMVCS::onBeforeShow  显示输出前被调用。
## DNAutoLoader 加载类

DNAutoLoader 不建议扩展。因为你要有新类进来才有能处理加载关系，不如自己再加个加载类呢。
DNAutoLoader 做了防多次加载和多次初始化。

	init($options)
	run()
	assignPathNamespace()
## DNRuntimeState 状态类
用于运行时状态的保存
## DNSuperGlobal 超全局变量
$_GET ,$_POST 在兼容 Swoole 环境下，变成 ,DNSuperGlobal::G()->_GET ,DNSuperGlobal::G()->_POST
*我也想缩短，但实在没法再短了。.*
# 第六章 DNMVCSExt 扩展类和附属组件
	DNMVCS 的选项 $options['ext'] 不为空数组就 引入DNMVCSExt 扩展类
	配置字段 ext 数组有数据的时候，会进入高级模式。自动使用扩展文件
	这些功能，用于，1 单一文件解决问题，2 多工程配置，3 使用更好的 db

## 扩展选项
```php
const DEFAULT_OPTIONS_EX=[
	'key_for_action'=>'_r', //act 这个选项，不用 path_info 了，我们用 $_REQUEST['act']，
	'key_for_module'=>'',   // 用于前缀，适用于多模块。
	
	'use_function_view'=>false,   //不用 view 文件了，我们用 view_$xx 来表示view
		'function_view_head'=>'view_header', // 页眉函数
		'function_view_foot'=>'view_footer', // 页脚函数
	'use_function_dispatch'=>false, //路由上不用 DNController->$xx 了，直接 action_$xx
	'use_common_configer'=>false,  //额外配置文件，多工程共享配置用
		'fullpath_project_share_common'=>'',  //配合上面的使用， 公共文件会被本工程覆盖
	'use_common_autoloader'=>false,  // 额外 loader ，多工程共享配置用
		'fullpath_config_common'=>'',  //配合上面的使用， 公共文件会被本工程覆盖
	'use_strict_db'=>false, // 严格模式
	'session_auto_start' =>false, //自动开启 session 
	'session_name'=>'DNSESSION',
	'use_facade'=>false,  // 你们要的门面函数 facade
	'facade_map'=>[],
];
```
## 不通过 path_info 的路由
key_for_module key_for_action
## 严格模式

我想让 DB 只能被 Model , ExModel 调用。Model 只能被 ExModel,Service 调用 。 LibService 只能被Service 调用  Service只能被 Controller 调用

可以,你的 Service  继承 StrictService. Model 继承 StrictModel ,扩展配置里开启 use_strict_db

严格模式下那些 **新手** 就不能乱来了。

## 门面 Facade
use_facade
facade_map

怎么使用
项目里
```php
use \MY\Facade\Service\TestService;  //Facade 放在$options['namespace']后面。
TestService::foo() =>  \My\TestService::G()->foo();
```
如果有
```php
facade_map=[
	'MY\Service\TestService' =>'MY\Service\DebugService' 
]

TestService::foo() =>  \MY\Service\DebugService::G()->foo();

```
高级一点，自己接管 FacadeBase 类 实现 getFacadeCallback 方法;

为什么不作为框架的默认行为。 主要考虑性能因数，而且自由，无依赖性

## 多工程配置。

```php
'fullpath_config_common'=>'',  
	//DNConfiger::G(ProjectCommonConfiger::G()); 	设置和配置会先读取相应的文件，合并到同目录来
'fullpath_project_share_common'=>''     // 通用类文件目录，用于多工程
	//调用ProjectCommonAutoloader::G()->init(DNMVCS::G()->options)->run();
//	只处理了 CommonService 和 CommonModel 而且是无命名空间的。
```
*待完善*

## 主类 DNMVCSExt

	public function afterInit($dn)
	public function checkDBPermission() // 用于在 use_strict_db 检查权限
	public function _RecordsetUrl(&$data,$cols_map=[])
	public function _RecordsetH(&$data,$cols=[])
	public function _ExitJson($ret)
	public function _ExitRedirect($url,$only_in_site=true)
	publlic function dealMapAndRewrite($route);
## RouteHookMapAndRewrite
	实现 route_map 和 rewrite_map 的类，会被系统加载
## SimpleRouteHook
	实现 path_info 的类
### ProjectCommonAutoloader
	实现通用文件加载
### ProjectCommonConfiger
	实现通用配置加载
### FunctionDispatcher
	函数方式的 controller
### FunctionView
	函数方式的 view
# 第七章 数据库
## 总说
DNMVCS 系统的数据库处理部分四个文件。
DBInterface.php DB.php DBAdvance.php MedooDB.php

interface DBInterface 是希望大家都遵守的标准。但不是强制要求。
trait DBAdvance 是 额外常用的 DB 功能。
class DB 是系统默认的 数据库类 trait DBAdvance 是 DB 类的扩充 
class MedooDB 是 Medoo 类基础上的一个封装 实现了 DBInterface 同时使用 DBAdvance 扩充。
使用 MedooDB 必须引入第三方库 Medoo

## DB.php
DNMVCS 自带了一个简单的 DB 类。
DN::DB()得到的就是这个 DB 类。
DB 的配置在 setting.sample.php 里有。
DB 简单实现的一个数据库类。封装了 PDO， 和 Medoo 兼容，也少了 Medoo 的很多功能。
下面主要说 DB 类的用法
```
close()
	关闭数据库
getPDO() //DB

public function quote($string)
	转码,如果是数组，则值部分会转码。
public function fetchAll($sql,...$args)
public function fetch($sql,...$args)
public function fetchColumn($sql,...$args)
	这三个是动态参数，直接查询
	获得的是数组
	（有时候还是觉得直接用 object $v->id 之类方便多了,你可以在 pdo 里调整。
public function execQuick($sql,...$args)
	执行 pdo 结果，获得 PDOStatement 为什么不用 exec ? 因为  Medoo用了。
	返回  PDOStatement 对象
public function  rowCount()
	获得结果行数
public function init($config)
	初始化
public static function CreateDBInstance($db_config)
	用于创建DB类
public static function CloseDBInstance($db)
	关闭DB类

```
## DBInterface.php
DBInterface 是希望其他DB类也遵守的接口。
## MedooDB.php
MedooDB 是 Medoo 的一个简单扩展，和 DB 接口一致。
因为 MedooDB 对 Medoo 有依赖关系，所以单独放在一个文件。
MedooDB 类的除了默认的 Medoo 方法，还扩展了 DB 类同名方法。


## 使用方法：
修改配置
```php
$options[
	'db_create_handler'=>[\DNMVCS\MedooDB::class,'CreateDBInstance'],
	'db_close_handler'=>[\DNMVCS\MedooDB::class,'CloseDBInstance'],
]
```
或者在你的 DNMVCS->init() 后面段加上下面代码，
使得 MedooDB 替换 DNDB
```php
\DNMVCS\DNDBManager::G()->setDBHandler(
	[\DNMVCS\MedooDB::class,'CreateDBInstance']
	[\DNMVCS\MedooDB::class,'CloseDBInstance']
);
```
# 第八章 Swoole 整合指南
## 概述
这章分两部分 SwooleHttpServer 和 DNSwooleHttpServer

SwooleHttpServer 是设计成几乎和 DNMVCS 无关的Swoole 框架。
DNSwooleHttpServer extends SwooleHttpServer 则是和 DNMVCS整合的 Swoole Http 服务器

DNMVCS 整合到 DNSwooleServer

```php
<?php

\DNMVCS\DNMVCS::RunAsServer($server_options,$dn_options,$server=null);
// 展开模式 \DNMVCS\DNSwooleServer::G()->init($server_options,$server)->bindDN($dn_options)->run();
## SwooleHttpServer
```

SwooleHttpServer 和 DNMVCS 主类主要关系是在 G 函数 的实现，如果没这个 G 函数，两者是完全独立的。
SwooleHttpServer  重写了 G 函数的实现，使得做到协程单例。
想要获得当前 的 request ,response 用 DNSwooleHttpServer::Request() ,Response（）；
还记得 _SERVER,_GET,_POST 超全局变量在 swoole 协程下无法使用么。用 SuperGloal类代替。


以下是 SwooleHttpServer 的介绍

和 DNMVCS 类似 SwooleHttpServer 的功能也基本由 SwooleHttpServer 实现。

## server_options 的选项

```php
const DEFAULT_OPTIONS=[
		'swoole_server'=>null,  // swoole_http_server 对象，留空，则用 host,port 创建
		'swoole_options'=>[],   //swoole_http_server 的配置，合并如 server
		
		'host'=>'0.0.0.0',      // IP
		'port'=>0,              //端口
		'http_handler_basepath'=>'',
		'http_handler_root'=>null,      // php 的目录和静态目录的不相同，留空
		'http_handler_file'=>null,      // 启动文件 留空将会使用 http_handler
		'http_handler'=>null,           // 启动方法，
		'http_exception_handler'=>null, // 异常处理方法,DNMVCS 已经占用  // http_handler_root 的异常也是这里处理
		
		//* websocket 在测试中。未稳定
		'websocket_open_handler'=>null,  //websocket 打开
		'websocket_handler'=>null,          //websocket 
		'websocket_exception_handler'=>null,    //websocket 异常处理
		'websocket_close_handler'=>null,        //websocket 关闭
];
```

## 三种模式
如果 http_handler 为空，有 http_handler_file 则直接 include  http_handler_file 运行，和 DNMVCS 系统无关

SwooleHttpServer 运行 DNMVCS 可以有三种模式
1. http_handler_root
	这和document_root 一样。读取php文件，然后运行的模式
2. http_handler_file
	这种模式是把 url 都转向 文件如 index.php 来处理
3. http_handler
	所有url请求都到这个函数处理。
	重点模式
	DNSwooleHttpServer->bindDN($dn_options) 就是把请求定到 DNMVCS 里处理。
	这模式和上面两种模式的区别，就是常驻内存,
	然后设置  http_handler http_exception_handler 为 DNMVCS  的 run, onException

DNSwooleHttpServer 可以让你用 echo 直接输出。

http_exception_handler，用于 单文件模式和目录模式，你可以在这里处理 404。

## class SwooleServer
## 基本方法
static G($object=null)

	G 函数，不多说
init($options=[])

	初始化，这是最经常子类化完成自己功能的方法。
	你可以扩展这个类，添加工程里的其他初始化。
run()

	运行
## 常用静态方法
和DNMVCS 常用静态方法一样，这也是常用的方法，所以提取出来
Server

	获得当前 swoole_server 对象
Request

	获得当前 swoole_request 对象
Response

	获得当前 swoole_response 对象
Frame
	获得当前  frame （websocket 生效 ）  
FD
	获得当前  fd  （websocket 生效）
IsClosing
	判断是否是关闭的包 （websocket 生效）
CloneInstance
	把静态单例克隆到当前协程。
	
## 系统封装方法
对应PHP手册的函数的全局函数的替代，因为相应的同名函数在 Swoole环境下不可用。
和 DNMVCS 的系统封装方法一样通用，
如果是在 DNSwooleServer 中，使用 DNMVCS 的相应静态函数，将会调用这里的实现。

header(string $string, bool $replace = true , int $http_status_code =0)

	header 函数
setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)

	设置 cookie
exit_system($code=0)

	退出系统，相应的是 exit ，swoole 里，直接 exit 也是可以的。

set_exception_handler(callable $exception_handler)
异常函数
register_shutdown_function(callable $callback,...$args)
退出关闭函数
session_start(array $options=[])

session_destroy()

session_set_save_handler(\SessionHandlerInterface $handler)

## 简单 HTTP 服务器
SwooleHttpServer 用的 trait SwooleHttpServer_SimpleHttpd .
单独使用这个 trait 你可以实现一个 http 服务器

	protected function onHttpRun($request,$response){throw new SwooleException("Impelement Me");}
	protected function onHttpException($ex){throw new SwooleException("Impelement Me");}
	protected function onHttpClean(){throw new SwooleException("Impelement Me");}
	
	public function onRequest($request,$response)
## 简单 HTTP 服务器
SwooleHttpServer 用的 trait SwooleHttpServer_WebSocket .
单独使用这个 trait 你可以实现一个 websocket 服务器

onRequest($request,$response)

	//
onOpen(swoole_websocket_server $server, swoole_http_request $request)

	//
onMessage($server,$frame)

	//
没有 OnClose 。
## 其他子类

## class SwooleCoroutineSingleton

用于协程单例

public static function GetInstance($class,$object)
public static function CreateInstance($class,$object=null)
public static function CloneInstance($class)
public static function DeleteInstance($class)
public static function ReplaceDefaultSingletonHandler()
public static function CleanUp()
public static function Dump()
public static function DumpString()
## SwooleException extends \Exception

	use DNThrowQuickly; 用于处理 Swoole 异常。
	public static function ThrowOn($flag,$message,$code=0)
	404 错误是用 code=404 那个
	没端口会报错

## class SwooleContext
	协程单例。Swoole 的运行信息
	public function initHttp($request,$response)
	public function initWebSocket($frame)
	public function cleanUp()
	public function onShutdown()
	public function regShutDown($call_data)
	public function isWebSocketClosing()
## SuperGlobalSuperGlobal
	SuperGlobalSuperGlobal 是 Swoole 下 SuperGlobal 类的实现。
	相关方法，和公开变量，参考见 DNSuperGlobal
## SwooleSession
	SwooleSession 是因为 Swoole 的 session 实现。
	SwooleSession 被 SuperGlobalSuperGlobal 调用， 调用 SwooleSessionHandler
## SwooleSessionHandler implements \SessionHandlerInterface
	如果你要实现自己的 SessionHandler ，用 SwooleServer::session_set_save_handler();替换这个类。 


## class DNSwooleHttpServer
	public function init($server_options,$server=null)
	public function run()



	把主进程单例复制到协程单例
## DNSwooleHttpServer

swoole 下， DNMVCS  入口选项 ['swoole'] 的选项
```php
const DEFAULT_DN_OPTIONS=[
		'not_empty'=>true,  			// 用于数组不空
		'db_reuse_size'=>0,             // 大于0表示复用数据库连接
		'db_reuse_timeout'=>5,          // 复用数据库连接超时秒数
		'use_http_handler_root'=>false,	// 复用 http_handler_root 404 后会从目录文件里载入
	];
```
	public function init($options,$server)
	public function onShow404()

	public function bindDN($dn_options)
	public static function RunWithServer($server_options,$dn_options=[],$server=null)

## class DBConnectPoolProxy
DB连接代理

	public function init($max_length=10,$timeout=5)
	public function setDBHandler($db_create_handler,$db_close_handler=null)
	public function onCreate($db_config,$tag)
	public function onClose($db,$tag)
	protected function getObject($queue,$queue_time,$db_config,$tag)
	protected function reuseObject($queue,$queue_time,$db)

# 第九章 DNMVCS 全部文件和类说明
这个章节说明 DNMVCS 的各个文件。
并在此把次要的类和文件展示出来
## 库文件说明
DNMVCS 的文件并没有遵守一个类一个文件的原则，而是一些主类文件里包含内部类。
特殊情况下或许会用到这些内部类。

主要掌握的文件 DNMVCS.php DNSwooleHttpServer.php DNMVCSExt.php
DNSingleton DNDI, DNThrowQuickly 在 DNMVCS.php 里也有，这里是抽出来。用于特殊情况
Pager.php 只是为了完成简单的演示用的分页类，除非很偷懒，不建议用
Toolkit.php 是一些收集到的类 和系统无关
```
ComposerScripts.php     // 和 compose 相关的脚本，用于创建工程用
	ComposerScripts
DB.php
	DB implements 
		trait DB_Ext
DBInterface
	interface DBInterface
DNMVCS.php              // 主入口文件 DNMVCS 类，不引用其他文件。
	trait DNSingleton
	trait DNDI
	trait DNThrowQuickly
	DNMVCS
		DNException extends \Exception
		trait DNMVCS_Glue
		trait DNMVCS_Misc
		trait DNMVCS_Handler
		trait DNMVCS_SystemWrapper

		DNAutoLoader
		DNRoute
		DNView
		DNConfiger
		DNDBManager
		DNExceptionManager
		DNRuntimeState
		DNSuperGlobal
DNMVCSExt.php           // ext 主入口文件  只引用 DNMVCS 文件
	DNMVCSExt
		SimpleRouteHook
		ProjectCommonAutoloader
		ProjectCommonConfiger extends DNConfiger
		FunctionDispatcher
		FunctionView extends DNView
MedooDB.php             Medoo 数据库类的扩展
	MedooDB extends MedooFixed
		MedooFixed extends \Medoo\Medoo
DNSingleton.php         单例 trait 
	trait DNSingleton 
DNSwooleHttpServer.php  使用 Swoole 的 http 服务
	DNSwooleHttpServer
		trait DNSwooleHttpServer_Static
		trait DNSwooleHttpServer_GlobalFunc
		trait DNSwooleHttpServer_SimpleHttpd
		trait DNSwooleHttpServer_WebSocket

		SwooleContext
		DNSwooleException extends \Exception
		DBConnectPoolProxy
	CoroutineSingleton
	SwooleSessionHandler implements \SessionHandlerInterface
	SwooleSuperGlobal extends DNSuperGlobal
		SwooleSESSION
Pager.php               用于简单接口的分页类
	Pager               分页类
README.md               说明文档
RouteHookMapAndRewrite.php 用于 RouteHook 和 Rewrite
	RouteHookMapAndRewrite
StrictModel.php
	trait StrictModel
StrictService.php
	trait StrictService
ToolKit.php             一些工具，无引用
	Toolkit
	DNFuncionModifer
	DidderWrapper
	API
	MyArgsAssoc

composer.json           Composer 系统的 json 文件
template/               模板文件夹
```
主要关心的是 DNMVCS.php DNSwooleHttpServer.php

## DB.php
已经在前面介绍
## DBInterface.php
DBInterface 是希望其他DB类也遵守的接口。

## DNMVCS.php
DNMVCS 类和附属类的文件。已经在前面介绍
## DNMVCSExt.php
DNMVCSExt 类和附属类的文件，将在后面介绍。
相关 $options['ext'] 的配置 用到这个类和附属类

## DNSingleton.php
G 函数，单独提出来，为的是可能会从 DNSwooleHttpServer 开始的入口
## DNSwooleHttpServer.php
swoole 服务，后面章节详细介绍
## Pager.php
一个独立的分页类，目的是让 DEMO 有分页效果。
如果你有更好方案，建议不要使用它。
## StrictService.php
	你的 Service 继承这个类
	调试状态下，允许 service 调用 libservice 不允许 service 调用 service ,不允许 model 调用 service
## StrictModel.php
	你的 Model 继承这个类
	调试状态下，只允许 Service 或者 ExModel 调用 Model

## Tookit.php 未使用用于参考的工具箱类。
一些可能会用到的类，需要的时候把他们复制走。

### trait DNWrapper 
W($object);
	
	DNWrapper::W(MyOBj::G()); 包裹一个对象，在 __call 里做一些操作，然后调用 call_the_object($method,$args)
	未使用。
### trait DNStaticCall

	Facade 的trait 引用到 DNSingleton，由于 php7 的限制， protected funtion 才能 static call
	未使用
### MedooSimpleIntaller
	\DNMVCS\DNDBManager::G()->setDBHandler([MedooSimpleIntaller::class,'CreateDBInstance']， [MedooSimpleIntaller::class,'CloseDBInstance']);
	用于加载 medoo 类代替默认的 db 类，注意 medoo 类 不兼容默认 db 类
### API
	用于 api 服务快速调用 无引用
	public static function Call($class,$method,$input)  input 是关联数组
	protected static function GetTypeFilter() 重写这个方法限定你的类型
	在项目里未使用
### MyArgsAssoc
	GetMyArgsAssoc 获得当前函数的命名参数数组 无引用
	CallWithMyArgsAssoc($callback)  获得当前函数的命名参数数组并回调
	在项目里未使用
### DNFuncionModifer
	包裹函数，实现 aop


# 第十章 DNMVCS 进阶
## 总说
DNMVCS 系统 是用各自独立的类合起来的。
只有单例模式合在一起


DNMVCS 主类里一些函数，是调用其他类的实现。基本都可以用 G 方法替换

DNMVCS 的各子类都是独立的。现实中应该不会拿出来单用吧

DNDBManger 调用 DB 类，用于管理数据库

## DNMVCS 的代码流程讲解

大致用图表现如下
```
DN::init
	DNAutoloader->run  自动加载
	checkOverride 如果有子类，则 G函数替换为子类。
	initExceptionManager 初始化异常。
	initConfiger,initView,initRoute,initDBManager 初始化组件
	initMisc 
	
DN::run
	RouteAdvance->hook
	(DNRoute::run)
	(RouteHook)($this);
		 
	getRouteHandleByFile
	(DNRoute->callback)()


DNMVCS::DB

	DNDBManager -> DNDB::CreateDBInstence(),DNDB::CloseDBInstence()
```



# 第十一章 常见问题

- Session 要怎么处理 
	一般来说 Session 的处理，放在 SesionModel 里。

- 后台里，我要判断权限，只有几个公共方法才能无权限访问
	- 构造函数里获得 $method=DNMVCS::G()->getRouteCallingMethod(); 然后进行后处理
	
- 为什么不把 DNMVCS 里那些子功能类作为DNMVCS类的属性， 如 $this->View=DNView::G();
	- 静态方法里调用。 self::G()->View->_Show() 比 DNView::G()->_Show() 之类更麻烦。非静态方法里也就懒得加引用了
- 我用 static 方法不行么，不想用 G() 函数于 Model ,Service
	- 可以，Model可以用。不过不推荐 Service 用
	- 琢磨了一阵如何不改 static 调用强行塞  strict 模式，还是没找到方法，切换 namespace 代理的方式可以搞定，但还是要手工改代码.

!!!2018-09-30 12:09:57 已经想出来了，改 autoloader ，配合 class alias 。测试 DEMO已过，有空添加
!!!2018-10-02 21:10:09 失败，因为 alias 之后，还要调用原来的类。
!!!2019-01-15 19:01:42 用 autoload + eval 解决
	- DNStaticCall 由于 php7 的限制， protected funtion 才能 static call
- 思考：子域名作为子目录
	想把某个子目录作为域名独立出去。只改底层代码如何改
	或者 v1/api v2/api 等等
- error-exception 和 error-500 有什么不同
	error-500 是引入的文件有语法错误之类。 error-exception 是抛出了错误没处理，用 setExceptionHandle 可以自行处理。

- 为什么 DNView 等类不使用单独文件
	因为我想 DNMVCS.php 一个入口文件就能实现基本的功能， 同理 DNSwooleHttpServer 也作为一个入口
	但 DNSwooleHttpServer 碰到 SuperGlobal 的问题，和 Session 问题，DNSwooleHttpServer 和  DNMVCS 的交集。
	所以就把  SwooleSuperGlobal extends SuperGlobal SwooleSessionHandler implements \SessionHandlerInterface
# 尾声 DNMVCS 是怎么越做越复杂的

	一开始想解决的是 MVC 缺 service 层
	接下来是偷懒选项
	接下来是一个文件搞定
	接下来是为了组件可灵活替换
	接下来是为了默认的几个组件 内部组件用户不必知道，使用即可
	接下来是数据库管理，支持主从和可替换化
	接下来是要应付额外的高级功能,这在 DNMVCSExt 里
	接下来是为了支持 映射路由和重写路由，这在 DNMVCS

	接下来是支持 swoole 
	支持 swoole 需要 superglobal 选项。
	swoole 的 session还要单独写
	代码就这么多了。
	接下来是支持 composer
	接下来是双入口
