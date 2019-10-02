<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookDirectoryMode;
use DNMVCS\Core\Route;
use DNMVCS\Core\SuperGlobal;

class RouteHookDirectoryModeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookDirectoryMode::class);
        
        $base_path=\GetClassTestPath(RouteHookDirectoryMode::class);
        $route_options=[
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookDirectoryModeTesttMain',

        ];
        Route::G(new Route())->init($route_options);
        $options=[
                'mode_dir_basepath'=>$base_path,
                'mode_dir_index_file'=>'',
                'mode_dir_use_path_info'=>true,
                'mode_dir_key_for_module'=>true,
                'mode_dir_key_for_action'=>true,
        ];
        RouteHookDirectoryMode::G()->init($options, $context=null);
        RouteHookDirectoryMode::G()->init($options, Route::G());
        
        SuperGlobal::G()->_SERVER['REQUEST_URI']='';
        SuperGlobal::G()->_SERVER['PATH_INFO']='';
        
        Route::G()->bindServerData([
            'DOCUMENT_ROOT'=>rtrim($base_path,'/'),
            'PATH_INFO'=>'Missed',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        
        SuperGlobal::G()->_SERVER['REQUEST_URI']='';
        SuperGlobal::G()->_SERVER['PATH_INFO']='';
echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n";
        SuperGlobal::G()->_SERVER['DOCUMENT_ROOT']=rtrim($base_path,'/');
        echo RouteHookDirectoryMode::G()->onURL("/ix");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("m");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("m/index");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("m/foo");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("a/b/c");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("a/b/index");
        echo PHP_EOL;

echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n";

        
        \MyCodeCoverage::G()->end(RouteHookDirectoryMode::class);
        $this->assertTrue(true);
        /*
        RouteHookDirectoryMode::G()->init($options=[], $context=null);
        RouteHookDirectoryMode::G()->adjustPathinfo($path_info, $document_root);
        RouteHookDirectoryMode::G()->onURL($url=null);
        RouteHookDirectoryMode::G()->hook($route);
        //*/
    }
}
class RouteHookDirectoryModeTesttMain
{    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }
}