# 一般流程
[toc]
## 开发人员角色

DuckPHP 的使用者角色分为 `应用程序员`和`核心程序员`两种。

`应用程序员`负责日常 Curd 。作为应用程序员， 你不能引入 DuckPHP 的任何东西，就当 DuckPHP 命名空间不存在。

`核心程序员`才去研究 DuckPHP 类里的东西。

## 目录结构

DuckPHP 代码里的 template 目录就是我们的工程目录示例。也是工程桩代码。

在执行 `./vendor/bin/duckphp --create` 的时候，会把代码复制到工程目录。 并做一些改动。

```text
+---app                     // psr-4 标准的自动加载目录。
|   +---Base                // 基类放在这里
|   |   |   App.php         // 默认框架入口文件
|   |   |   BaseController.php  // 控制器基类
|   |   |   BaseModel.php   // 模型基类
|   |   |   BaseService.php // 服务基类
|   |   \---Helper
|   |           ControllerHelper.php    // 控制器助手类
|   |           ModelHelper.php     // 模型助手类
|   |           ServiceHelper.php   // 服务助手类
|   |           ViewHelper.php      // 视图助手类
|   +---Controller          // 控制器目录
|   |       Main.php        // 默认控制器
|   +---Model               // 模型放在里
|   |       TestModel.php   // 测试模型
|   \---Service             // 服务目录
|           TestService.php // 测试 Service
+---config                  // 配置文件放这里
|       config.php          // 配置，目前是空数组
|       setting.sample.php  // 设置，去除敏感信息的模板
+---view                    // 视图文件放这里，可调
|   |   main.php            // 视图文件
|   \---_sys                // 系统错误视图文件放这里
|           error-404.php   // 404 页面
|           error-500.php   // 500 页面
|           error-debug.php // 调试的时候显示的视图
+---public                  // 网站目录
|       index.php           // 主页，入口页
\---start_server.php    // 启动 Htttp 服务
```
这个目录结构里，`应用程序员`只能写 `app/Controller`,`app/Model`,`app/Service`,`view` 这四个目录。
有时候需要去读 `app/Base/Helper` 目录下的的类。其他则是`核心程序员`的活。

app 目录，就是放 MY 开始命名空间的东西了。 app 目录可以在选项里设置成其他名字
命名空间 MY 是 可调的。比如调整成 MyProject ,TheBigOneProject  等。
可以用 `./vendor/bin/duckphp --create --namespace TheBigOneProject` 调整。

文件都不复杂。基本都是空类或空继承类，便于不同处理。
这些结构能精简么？
可以，你可以一个目录都不要。

Base/App.php 这个类继承 DuckPhp\App 类，工程的入口流程会在这里进行，这里是`核心程序员`重点了解的类。



BaseController, BaseModel, BaseService 是你自己要改的基类，基本只实现了单例模式。
ContrllorHelper,ModelHelper,ServiceHelper 如果你一个人偷懒，直接用 APP 类也行  



#### 如何精简目录
* 移除 app/Base/Helper/ 目录,如果你直接用 App::* 替代 M,V,C,S 助手类。
* 移除 app/Base/BaseController.php 如果你的 Controller 和默认的一样不需要基本类。
* 移除 app/Base/BaseModel.php 如果你的 Model 用的全静态方法。
* 移除 app/Base/BaseService.php 如果你的 Service 不需要 G 方法。
* 移除 bin/start_server.php 如果你使用外部 http 服务器
* 移除 config/ 目录,在启动选项里加 'skip_setting_file'=>true ，如果你不需要 config/setting.php，
    并有自己的配置方案
* 移除 view/\_sys  目录 你需要设置启动选项里 'error\_404','error\_500'。
* 移除 view 目录如果你不需要 view ，如 API 项目。
* 移除 TestService.php ， TestModel.php  测试用的东西

----


## 工程完整架构图


对应上面的文件结构，你的工程应该是这么架构。

![arch_full](arch_full.gv.svg)

文字版
```text
           /-> View-->ViewHelper
Controller --> Service ------------------------------ ---> Model
         \         \   \               \  /                  \
          \         \   \-> LibService ----> ExModel----------->ModelHelper
           \         \             \                
            \         ---------------->ServiceHelper
             \-->ControllerHelper
```

同级之间的东西不能相互调用

* 写 Model 你可能要引入 Base\Helper\ModelHelper 助手类别名为 M 。
* 写 Serivce 你可能要引入 Base\Helper\SerivceHelper 助手类别名为 S 。
* 写 Controller 你可能要引入 Base\Helper\ControllerHelper 助手类别名为 C 。
* 写 View 你可能要引入 Base\Helper\ViewHelper 助手类别名为 V 。
* 不能交叉引入其他层级的助手类。如果需要交叉，那么你就是错的。
* 小工程可以用直接使用入口类 MY\Base\App 类，这包含了上述类的公用方法。
* ContrllorHelper,ModelHelper,ServiceHelper,ViewHelper 如果你一个人偷懒，直接用 APP 类也行  
* Service 按业务逻辑走， Model 按数据库表名走
* LibService 其实是特殊的 Service 用于其他 Service 调用
* ExModel 是特殊 Model 表示多个表混合调用。

* 图上没显示特殊的 AppHelper

助手类教程在这里 [助手类教程](tutorial-helper.md)，基本上，看完助手类教程，`应用程序员`就可以开干了。

## 入口文件和选项

我们看 Web 的入口文件 public/index.php

```php
<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
$path = realpath(__DIR__.'/..');
$namespace = rtrim('MY\\', '\\');                    // @DUCKPHP_NAMESPACE
////[[[[
$options =
array(
    // 省略一堆注释性配置
);
////]]]]
$options['path'] = $path;
$options['namespace'] = $namespace;
$options['error_404'] = '_sys/error_404';
$options['error_500'] = '_sys/error_500';
$options['error_debug'] = '_sys/error_debug';

$options['is_debug'] = true;                  // @DUCKPHP_DELETE
$options['skip_setting_file'] = true;                 // @DUCKPHP_DELETE
echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE


\DuckPhp\App::RunQuickly($options, function () {
});
```

入口类前面部分是处理头文件的。
然后处理直接 copy 代码提示，不要直接运行。
起作用的主要就这句话

```php
\DuckPHP\App::RunQuickly($options, function () {
});
```
相当于 \DuckPHP\App::G()->init($options)->run(); 第二个参数的回调用于 init 之后执行。

init, run 分两步走的模式。

最后留了 dump 选项的语句。

注意到  // @ 的注释，这些特殊注解，他们用于安装脚本。共有4个注解

+ // @DUCKPHP_DELETE 模板引入后删除
+ // @DUCKPHP_HEADFILE 头文件调整
+ // @DUCKPHP_NAMESPACE 调整命名空间
+ // @DUCKPHP_KEEP_IN_FULL 在view  里，如果是 --full 选项则保留。

我们引用代码的时候，省略了一堆注释，这些注释，就是选项

专门有个章节说明这些选项开关的使用方法。 请阅读

DuckPHP 只要更改选项就能实现很多强大的功能变化。
如果这些选项都不能满足你，那就启用扩展吧，这样有更多的选项能用，
如果连这都不行，那么，就自己写扩展吧。

### 使用 DuckPHP 的扩展

DuckPHP 扩展的加载是通过选项里添加
$options['ext']数组实现的

    扩展映射 ,$ext_class => $options。
    
    $ext_class 为扩展的类名，如果找不到扩展类则不启用。
    
    $ext_class 满足组件接口。在初始化的时候会被调用。
    $ext_class->init(array $options,$context=null);
    
    如果 $options 为  false 则不启用，
    如果 $options 为 true ，则会把当前 $options 传递进去。

DuckPHP/Core 的其他组件如 Configer, Route, View, AutoLoader 默认都在这调用

## 核心开发者重写的入口类。
`app/Base/App.php` 对应的 MY\Base\App 类就是入口了。
模板文件提供
```php
<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Base;

use DuckPhp\App as DuckPhp_App;

class App extends DuckPhp_App
{
    public function onInit()
    {
        // your code here
        $ret = parent::onInit();
        // your code here
        return $ret;
    }
    protected function onRun()
    {
        // your code here
        return parent::onRun();
    }
}
```
onInit 方法，会在初始化后进行
onRun 方法，会在运行期间运行。

## 高级说明

## 请求流程和生命周期
index.php 就只执行了

DuckPHP\App::RunQuickly($options, $callback) 

发生了什么

等价于 DuckPHP\App::G()->init($options)->run();

init 为初始化阶段 ，run 为运行阶段。$callback 在init() 之后执行

init 初始化阶段
    处理是否是插件模式
    处理自动加载  AutoLoader::G()->init($options, $this)->run();
    处理异常管理 ExceptionManager::G()->init($exception_options, $this)->run();
    如果有子类，切入子类继续 checkOverride() 
    调整补齐选项 initOptions()
    
    【重要】 onInit()，可 override 处理这里了。
    默认的 onInit
        初始化 Configer
        从 Configer 再设置 是否调试状态和平台 reloadFlags();
        初始化 View
        设置为已载入 View ，用于发生异常时候的显示。
        初始化 Route
        初始化扩展 initExtentions()
    初始化阶段就结束了。

run() 运行阶段

    处理 setBeforeRunHandler() 引入的 beforeRunHandlers
    * onRun ，可 override 处理这里了。
    重制 RuntimeState 并设置为开始
    绑定路由
    ** 开始路由处理 Route::G()->run();
    如果返回 404 则 On404() 处理 404
    clear 清理
        如果没显示，而且还有 beforeShowHandlers() 处理（用于处理 DB 关闭等
        设置 RuntimeState 为结束

   路由流程

### 核心基本选项
```php
const DEFAULT_OPTIONS=[
    //// basic ////
    'path'=>null,               // 基本目录, 其他目录依赖的基础目录，自动处理 “/”。
    'namespace'=>'MY',          // 工程的 autoload 的命名空间
    'path_namespace'=>'app',    // 工程对应的命名空间 目录
    
    'skip_app_autoload'=>false, // 如果你用compose.json 设置加载 app 目录，改为 true;
    
    //// properties ////
    'override_class'=>'Base\App',   
                                // 基类，后面详细说明
    'is_debug'=>false,          // 是否是在开发状态
    'platform'=>'',             //  配置平台标志，Platform 函数得到的是这个
    'use_flag_by_setting'=>true,   // 从设置文件里重新加载 is_debug,platform 选项
    'enable_cache_classes_in_cli'=>true, 
                                // 命令行下缓存 类数据
    'skip_view_notice_error'=>true,
                                // view 视图里忽略 notice 错误。
    'skip_404_handler'=>false,  // 404 由外部处理。
    'ext'=>[],                  // 扩展
    
    //// error handler ////
    'error_404'=>'_sys/error-404',
                                // 404 页面
    'error_500'=>'_sys/error-500',
                                // 错误页面
    'error_exception'=>'_sys/error-exception',  
                                // 异常页面
    'error_debug'=>'_sys/error-debug',
                                // 调试页面

];
```

**还有众多其他组件的配置，这里不一一展示。**

##### 基本选项
'skip_setting_file'=> false,

    新手之一最容易犯的错就是，没把这项设置为 true.
    这个选项的作用是跳过读取 setting.php  敏感文件。
    为什么要这么设置， 防止传代码上去而没传设置文件。
    造成后面的错误。
'path'=>null,

    基本路径，其他配置会用到这个基本路径。
'namespace' =>'MY',

    工程的 autoload 的命名空间，和很多框架限定只能用 App 作为命名空间不同，DuckPHP 允许你用不同的命名空间
'path_namespace'=>'app',

    默认的 psr-4 的工程路径配合 skip_app_autoload  使用。
'skip_app_autoload'=>false

    跳过应用的加载，如果你使用composer.json 来加载你的工程命名空间，你可以打开这个选项。
'override_class'=>'Base\App',

**重要选项**

    基于 namespace ,如果这个选项的类存在，则在init()的时候会切换到这个类完成后续初始化，并返回这个类的实例。
    注意到 app/Base/App.php 这个文件的类 MY\Base\App extends DuckPHP\App;
    如果以  \ 开头则是绝对 命名空间
'is_debug'=>false,

    配置是否在调试状态。
'platform'=>'',

    配置开发平台 * 设置文件的  platform 会覆盖
'skip_view_notice_error'=>true,

    view 视图里忽略 notice 错误。
'use_flag_by_setting'=>true,

    从设置里重载 is_debug 和 platform
'skip_404_handler'=>false,

    不处理404，用于你想在流程之外处理404的情况
##### 错误处理

error_* 选项为 null 用默认，为 callable 是回调，为string 则是调用视图。

'error_debug'=>'_sys/error-debug',

    is_debug 打开情况下，显示 Notice 错误
'error_404'=>'_sys/error-404'

    404 页面
'error_500'=>'_sys/error-500'

    500 页面，异常页面都会在这里

##### Tip 虚拟接口 组件类

组件类满足以下虚拟接口

```
interface ComponentInterface
{
    public $options;/* array() */;
    public static function G():this;
    public init(array $options, $contetxt=null):this;
}
```
为什么是虚拟接口？因为你不必 impelement .

DuckPHP 的扩展都放在 DuckPHP\\Ext 命名空间里
下面按字母顺序介绍这些扩展的作用
按选项，说明，公开方法，一一介绍。

SingletonEx 可变单例

\*Helper 是各种快捷方法。


这些组件 都可以在 onInit 里通过类似方法替换
```php
Route::G(MyRoute::G());
View::G(MyView::G());
Configer::G(MyConfiger::G());
RuntimeState::G(MyRuntimeState::G());
```

例外的是 AutoLoader 和 ExceptionManager 。 这两个是在插件系统启动之前启动
所以你需要：
```php
AutoLoader::G()->clear();
AutoLoader::G(MyAutoLoader::G())->init($this->options,$this);

ExceptionManager::G()->clear();
ExceptionManager::G(MyExceptionManager::G())->init($this->options,$this);
```
如何替换组件。

注意的是核心组件都在 onInit 之前初始化了，所以你要自己初始化。
* 为什么核心组件都在 onInit 之前初始化。

为了 onInit 使用方便

* 为什么 Core 里面的都是 App::Foo(); 而 Ext 里面的都是 App::G()::Foo();
因为 Core 里的扩展都是在 DuckPHP\Core\App 下的。

Core 下面的扩展不会单独拿出来用， 
如果你扩展了该方面的类，最好也是让用户通过 App 或者 MVCS 组件来使用他们。



