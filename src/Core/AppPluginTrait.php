<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\Configer;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;

trait AppPluginTrait
{
    // public $plugin_options = [] => in parent
    
    public $onPluginModePrepare;
    public $onPluginModeInit;
    public $onPluginModeBeforeRun;
    public $onPluginModeRun;
    
    protected $path_view_override = '';
    protected $path_config_override = '';
    
    protected $plugin_context_class = '';
    protected $plugin_route_old = null;
    protected $plugin_view_old = null;
    
    public function pluginModeInit(array $options, object $context = null)
    {
        $plugin_options_default = [
            'plugin_path_namespace' => null,
            'plugin_namespace' => null,
            
            'plugin_routehook_position' => 'append-outter',
            
            'plugin_path_conifg' => 'config',
            'plugin_path_view' => 'view',
            
            'plugin_search_config' => false,
            'plugin_injected_helper_map' => '',
            'plugin_files_config' => [],
            'plugin_url_prefix' => '',
            'plugin_view_options' => [],
            'plugin_route_options' => [],
        ];
        
        
        $this->plugin_options = array_merge($plugin_options_default, $this->plugin_options ?? []);
        $this->plugin_options['plugin_files_config'] = $this->plugin_options['plugin_files_config'] ?? [];
        
        $this->onPluginModePrepare();
        $this->pluginModeInitOptions($options);
        $this->pluginModeInitVars($context);
        
        // initConfig
        $ext_config_files = [];
        foreach ($this->plugin_options['plugin_files_config'] as $name) {
            $file = $this->path_config_override.$name.'.php';
            $ext_config_files[$name] = $file;
        }
        if (!empty($ext_config_files)) {
            Configer::G()->assignExtConfigFile($ext_config_files);
        }
        //clone Helper
        if ($this->plugin_options['plugin_injected_helper_map']) {
            $this->plugin_context_class::G()->cloneHelpers($this->plugin_options['plugin_namespace'], $this->plugin_options['plugin_injected_helper_map']);
        }
        
        Route::G()->addRouteHook([static::class,'PluginModeRouteHook'], $this->plugin_options['plugin_routehook_position']);
        
        $this->onPluginModeInit();
        
        return $this;
    }
    //for override
    protected function onPluginModePrepare()
    {
        if ($this->onPluginModePrepare) {
            return ($this->onPluginModePrepare)();
        }
    }
    //for override
    protected function onPluginModeInit()
    {
        if ($this->onPluginModeInit) {
            return ($this->onPluginModeInit)();
        }
    }
    //for override
    protected function onPluginModeBeforeRun()
    {
        if ($this->onPluginModeBeforeRun) {
            return ($this->onPluginModeBeforeRun)();
        }
    }
    //for override
    public function onPluginModeRun()
    {
        if ($this->onPluginModeRun) {
            return ($this->onPluginModeRun)();
        }
    }
    public static function PluginModeRouteHook($path_info)
    {
        return static::G()->_PluginModeRouteHook($path_info);
    }
    /////
    protected function pluginModeInitOptions($options)
    {
        $this->plugin_options = array_intersect_key(array_replace_recursive($this->plugin_options, $options), $this->plugin_options);
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
        
        $this->path_view_override = rtrim($this->plugin_options['plugin_path_namespace'].$this->plugin_options['plugin_path_view'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $this->path_config_override = rtrim($this->plugin_options['plugin_path_namespace'].$this->plugin_options['plugin_path_conifg'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if ($this->plugin_options['plugin_search_config']) {
            $this->plugin_options['plugin_files_config'] = $this->pluginModeSearchAllPluginFile($this->path_config_override, $setting_file);
        }
    }
    protected function pluginModeSearchAllPluginFile($path, $setting_file = '')
    {
        $setting_file = !empty($setting_file) ? $path.$setting_file . '.php' : '';
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
    protected function getPluginModePathInfo($path_info)
    {
        $ret = $path_info;
        if ($this->plugin_options['plugin_url_prefix'] ?? false) {
            $prefix = '/'.trim($this->plugin_options['plugin_url_prefix'], '/').'/';
            $l = strlen($prefix);
            if (substr($path_info, 0, $l) !== $prefix) {
                return false;
            }
            $ret = substr($path_info, $l - 1);
        }
        return $ret;
    }
    protected function _PluginModeRouteHook($path_info)
    {
        $path_info = $this->getPluginModePathInfo($path_info);
        if ($path_info === false) {
            return false;
        }
        $this->pluginModeBeforeRun();
        
        $route = Route::G();
        $route->setPathInfo($path_info);
        $route->setUrlHandler([static::class,'OnPluginModeUrl']); //context->url?
        
        $this->onPluginModeBeforeRun();
        
        $callback = $route->defaultGetRouteCallback($path_info);
        if (null === $callback) {
            $this->pluginModeClear();
            return false;
        }
        $this->onPluginModeRun();
        ($callback)();
        
        $this->pluginModeClear();
        return true;
    }
    protected function pluginModeReplaceComponent()
    {
        View::G(new View());
        Route::G(new Route());
    }
    protected function getPathView($path_view, $namespace)
    {
        $path_view = $path_view === '' ? '': rtrim($path_view, DIRECTORY_SEPARATOR) .DIRECTORY_SEPARATOR;
        $path_view .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        
        return $path_view;
    }
    protected function pluginModeBeforeRun()
    {
        $this->plugin_view_old = View::G();
        $this->plugin_route_old = Route::G();
        $this->pluginModeReplaceComponent();
        
        $view_options = $this->options['plugin_view_options'];
        $view_options['path'] = $this->plugin_view_old->options['path'] ?? '';
        $view_options['path_view'] = $this->getPathView($this->plugin_view_old->options['path_view'] ?? '', $this->plugin_options['plugin_namespace']);
        
        $view_options['path_view_override'] = rtrim($this->plugin_options['plugin_path_namespace'].$this->plugin_options['plugin_path_view'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        
        $route_options = $this->options['plugin_route_options'];
        $route_options['namespace'] = $this->plugin_options['plugin_namespace'];
        
        View::G()->init($view_options)->reset();
        Route::G()->init($route_options)->reset();
    }
    protected function pluginModeClear()
    {
        View::G($this->plugin_view_old);
        Route::G($this->plugin_route_old);
        $this->plugin_view_old = null;
        $this->plugin_route_old = null;
    }
    public static function OnPluginModeUrl($url)
    {
        return static::G()->_OnPluginModeUrl($url);
    }
    public function _OnPluginModeUrl($url)
    {
        $prefix = trim($this->plugin_options['plugin_url_prefix'], '/');
        $url = $prefix.$url;
        
        return Route::G()->defaultUrlHandler($url);
    }
    public function pluginModeGetOldRoute()
    {
        return $this->plugin_route_old;
    }
    public function pluginModeGetOldView()
    {
        return $this->plugin_route_old;
    }
}
