<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    //@override
    public $options = [
        'simple_auth_check_installed' => true,
        'simple_auth_table_prefix' => '',
        'simple_auth_session_prefix' => '',
    ];
    public function __construct()
    {
        parent::__construct();
    }
    protected function onBeforeRun()
    {
        $this->checkInstall();
    }
    protected function checkInstall()
    {
        if($this->options['simple_auth_check_installed'] && !Installer::G()->init([],$this)->isInstalled()){
            throw new \ErrorException("`SimpleAuth` need install. run install command first. e.g. :`php auth.php SimpleAuth:install`\n");
        }
    }
    //////////////////////
    public function command_install()
    {
        echo "welcome to Use SimplAuth installer  --force  to force install\n";
        $parameters =  static::Parameter();
        if(count($parameters)==1 || ($parameters['help'] ?? null)){
            // echo "--force  to force install ;";
            //return;
        }
        echo $this->install($parameters);
        echo "Done \n";
    }
    public function install($parameters)
    {
        $options = [
            'force' => $parameters['force']?? false,
            'path' => $this->getPath(),
            
            'sql_dump_prefix' => '',
            'sql_dump_inlucde_tables' => [ 'Users'],        
            'sql_dump_install_replace_prefix' => true,
            'sql_dump_install_new_prefix' => $this->options['simple_auth_table_prefix'],
            'sql_dump_install_drop_old_table' => $parameters['force']?? false,
        ];
        Installer::G()->init($options,$this);
        
        echo Installer::G()->run();
    }
    public function getPath()
    {
        return $this->options['path'];
    }
    public function getTablePrefix()
    {
        return $this->options['simple_auth_table_prefix'];
    }
    public function getSessionPrefix()
    {
        return $this->options['simple_auth_session_prefix'];
    }
    
    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
