<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Core;

use DuckPhp\Core\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;

trait AppPluginTrait
{
    //public $plugin_options = [] => in parent
    protected $plugin_options_default = [
        'plugin_path_namespace' => null,
        'plugin_namespace' => null,
        
        'plugin_routehook_position' => 'append-outter',
        
        'plugin_path_conifg' => 'config',
        'plugin_path_view' => 'view',
        
        'plugin_search_config' => false,
        'plugin_files_config' => [],
        'plugin_use_helper' => true,
    ];

    protected $path_view_override = '';
    protected $path_config_override = '';
    
    protected $plugin_context_class = '';
    protected $plugin_before_run_handler = null;
    protected $plugin_route = null;
    // protected componentClassMap=[] => in parent
    
    public function pluginModeInit(array $options, object $context = null)
    {
        $this->plugin_options = array_replace_recursive($this->plugin_options_default, $this->plugin_options);
        $this->onPluginModePrepare();
        $this->pluginModeInitOptions($options);
        $this->pluginModeInitVars($context);
        
        $ext_config_files = [];
        foreach ($this->plugin_options['plugin_files_config'] as $name) {
            $file = $this->path_config_override.$name.'.php';
            $ext_config_files[$name] = $file;
        }
        if (!empty($ext_config_files)) {
            Configer::G()->assignExtConfigFile($ext_config_files);
        }
        Route::G()->addRouteHook([static::class,'PluginModeRouteHook'], $this->plugin_options['plugin_routehook_position']);
        
        $this->onPluginModeInit();
        
        return $this;
    }
    //for override
    protected function onPluginModePrepare()
    {
        ////
    }
    //for override
    protected function onPluginModeInit()
    {
        ////
    }
    //for override
    protected function onPluginModeRun()
    {
        ////
    }
    public function pluginModeBeforeRun($handler)
    {
        $this->plugin_before_run_handler = $handler;
    }
    public static function PluginModeRouteHook($path_info)
    {
        return static::G()->_PluginModeRouteHook($path_info);
    }
    /////
    protected function pluginModeInitOptions($options)
    {
        $this->plugin_options = array_intersect_key(array_replace_recursive($this->plugin_options, $options) ?? [], $this->plugin_options);
        $class = static::class;
        
        if (!isset($this->plugin_options['plugin_namespace']) || !isset($this->plugin_options['plugin_path_namespace'])) {
            $t = explode('\\', $class);
            $t_class = array_pop($t);
            $t_base = array_pop($t);
            $namespace = implode('\\', $t);
            if (!isset($this->plugin_options['plugin_namespace'])) {
                $this->plugin_options['plugin_namespace'] = $namespace;
            }
            if (!isset($this->plugin_options['plugin_path_namespace'])) {
                $myfile = (new \ReflectionClass($class))->getFileName();
                $path = substr($myfile, 0, -strlen($t_class) - strlen($t_base) - 5); //5='/.php';
                $this->plugin_options['plugin_path_namespace'] = $path;
            }
        }
    }
    protected function pluginModeInitVars($context)
    {
        $this->plugin_context_class = get_class($context);
        $setting_file = $context->options['setting_file'] ?? 'setting';
        
        $this->path_view_override = rtrim($this->plugin_options['plugin_path_namespace'].$this->plugin_options['plugin_path_view'], '/').'/';
        $this->path_config_override = rtrim($this->plugin_options['plugin_path_namespace'].$this->plugin_options['plugin_path_conifg'], '/').'/';

        if ($this->plugin_options['plugin_search_config']) {
            $this->plugin_options['plugin_files_config'] = $this->pluginModeSearchAllPluginFile($this->path_config_override, $setting_file);
        }
    }
    protected function pluginModeSearchAllPluginFile($path, $setting_file = '')
    {
        $setting_file = !empty($setting_file)?$path.$setting_file.'.php':'';
        $flags = \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS ;
        $directory = new \RecursiveDirectoryIterator($path, $flags);
        $it = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($it, '/^.+\.php$/i', \RecursiveRegexIterator::MATCH);
        foreach ($regex as $k => $_) {
            if ($k === $setting_file) {
                continue;
            }
            if (substr($k, -strlen('.sample.php')) === '.sample.php') {
                continue;
            }
            $k = substr($regex->getSubPathName(), 0, -4);
            $ret[] = $k;
        }
        return $ret;
    }

    protected function _PluginModeRouteHook($path_info)
    {
        if ($this->plugin_options['plugin_use_helper'] && $this->componentClassMap) {
            $this->pluginModeCloneHelpers();
        }
        
        View::G()->setOverridePath($this->path_view_override);
        
        // route
        $route = new Route();
        $this->plugin_route = $route;
        $options['namespace'] = $this->plugin_options['plugin_namespace'];
        $route->init($options)->bindServerData(SuperGlobal::G()->_SERVER);
        $route->setPathInfo($path_info);
        
        if ($this->plugin_before_run_handler) {
            ($this->plugin_before_run_handler)();
        }
        $this->onPluginModeRun();
        
        $flag = $route->defaultRunRouteCallback($path_info);
        return $flag;
    }
    protected function pluginModeCloneHelpers()
    {
        $a = explode('\\', get_class($this));
        array_pop($a);
        $namespace = ltrim(implode('\\', $a).'\\', '\\');
        
        $this->plugin_context_class::G()->cloneHelpers($namespace);
    }
    public function pluginModeGetRoute()
    {
        return $this->plugin_route;
    }
}
