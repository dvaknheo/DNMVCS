<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\Console;
use DuckPhp\Component\DefaultCommand;
use DuckPhp\HttpServer\HttpServer;
use DuckPhp\Component\Installer;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class ConsoleTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Console::class);
        
        $_SERVER['argv']=[];
        $options=[];
        Console::G()->init(['cli_enable'=>false],App::G());
        
        Console::G(new Console())->init($options,App::G());
        Console::G()->app();
        App::G()->run();
        
        Console::G()->init(['cli_enable'=>true,'cli_mode' => 'hook',],App::G());
        
        Console::G(new Console())->init($options,App::G());
        App::G()->run();
        
        Console::G()->init($options,App::G());
        Console::G()->getCliParameters();
        
        Console::G()->regCommandClass(Console_Command::class,"test");
        $_SERVER['argv']=[
            '-','test:foo',
        ];
        App::G()->run();
        $_SERVER['argv']=[
            '-','test:foo','arg1','--pa',"--l","a","--az","a","b"
        ];
        App::G()->run();
        $_SERVER['argv']=[
            '-','test:foo','arg1','arg2','--pa',"--l","a","--az","a","b"
        ];
        App::G()->run();
        $_SERVER['argv']=[
            '-','test:foo2','arg1','arg2','--a1',"--a2","a","--a3","a","b"
        ];
        App::G()->run();

        $_SERVER['argv']=[
            '-','test:foo2','--a1=aaa',"--a2","a","--a3","a","b"
        ];
        App::G()->run();
        try{
            $_SERVER['argv']=[
                '-','test:foo3',
            ];
            App::G()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        try{
            $_SERVER['argv']=[
                '-','foo',
            ];
            App::G()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        try{
            $_SERVER['argv']=[
                '-','foo:foo',
            ];
            App::G()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        
        
        //*/
        Console::G(new Console())->init([],Console_App::G());
        $_SERVER['argv']=[
            '-','list',
        ];
        Console::G()->regCommandClass(Console_Command::class,"aa");
        Console::G()->regCommandClass(Console_Command2::class,"aa");
        Console_App::G()->run();
        $_SERVER['argv']=[
            '-','call',str_replace('\\','/',Console_Command2::class).'@command_foo4','A1'
        ];
        Console_App::G()->run();
        
        
        try{
            $_SERVER['argv']=[
                '-','test',
            ];
           Console::G(new Console())->init(['cli_default_command_class'=>''])->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        
        //*/
        
        $t=\MyCodeCoverage::G();
        define('__SINGLETONEX_REPALACER',ConsoleParent::class.'::CreateObject');
        \MyCodeCoverage::G($t);
        ConsoleParent::G()->isInited();
        echo "zzzzzzzzzzzzzzzzzzzzzzzz";
        
        \MyCodeCoverage::G()->end();
    }
}
class Console_App extends App
{
    /** overrid test*/
    public function command_test()
    {
        return true;
    }
}


class Console_Command extends ComponentBase
{
    public function command_foo()
    {
    }
    /**
     * desc1
    */
    public function command_foo2($a1,$a2,$a3,$a4='aa')
    {
    
    }
    /**
     * desc2
    */
    public function command_foo3($a1)
    {
    
    }
}
class Console_Command2 extends Console_Command
{
    /**
     * desc2
    */
    public function command_foo4($a1)
    {
    
    }
}
class ConsoleParent extends Console
{
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new $class));
        return $_instance[$class];
    }
}