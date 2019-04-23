<?php
namespace DNMVCS\InnerExt;

use DNMVCS\Basic\SingletonEx;
use \Exception;

class Strict
{
    use SingletonEx;
    
    const MAX_TRACE_LEVEL=10;
    protected $dn_class=null;
    public function init($options=[], $context=null)
    {
        if ($context) {
            $this->initContext($options, $context);
        }
    }
    protected function initContext($options=[], $context=null)
    {
        $this->dn_class=get_class($context);
    }
    public static function OnCheckStrictDB($object)
    {
        return static::G()->_OnCheckStrictDB($object);
    }
    ///////////////////////////////////////////////////////////
    protected function getCaller($object)
    {
        $caller_class='';
        $base_class=is_string($object)?$object:get_class($object);
        $backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, static::MAX_TRACE_LEVEL);
        foreach ($backtrace as $i=>$v) {
            if ($v['class']===$base_class) {
                $caller_class=$backtrace[$i+1]['class']??'';
                if ($this->dn_class && (is_subclass_of($this->dn_class, $caller_class) || $this->dn_class===$caller_class)) {
                    $caller_class=$backtrace[$i+2]['class']??'';
                }
                break;
            }
        }
        if (!$caller_class) {
            throw new Exception(true, "Too Nest ,can't not found caller");
        }
        return $caller_class;
    }
    protected function checkEnv()
    {
        $dn=$this->dn_class;
        if (!$dn::Developing()) {
            return false;
        }
        return true;
    }
    public function _OnCheckStrictDB($object)
    {
        return $this->checkStrictComponent($object, 'DB');
    }
    public function checkStrictComponent($object, $component_name)
    {
        if (!$this->checkEnv()) {
            return;
        }
        
        $caller_class=$this->getCaller($object);
        
        $dn=$this->dn_class;
        $namespace=$dn::G()->options['namespace'];
        $namespace_service=$namespace."\\Service\\";
        
        $namespace_controller=$namespace."\\Controller\\"; // TODO
        $controller_base_class=$dn::G()->options['controller_base_class']??'';
        
        do {
            if (substr($caller_class, 0, strlen($namespace_controller))==$namespace_controller) {
                throw new Exception(true, "DB Can not Call By Controller");
            }
            if ($controller_base_class && (is_subclass_of($caller_class, $controller_base_class) || $caller_class===$controller_base_class)) {
                throw new Exception(true, "DB Can not Call By Controller");
            }
            if (substr($caller_class, 0, strlen($namespace_service))===$namespace_service) {
                throw new Exception(true, "DB Can not Call By Service");
            }
            if (substr($caller_class, 0-strlen("Service"))=="Service") {
                throw new Exception(true, "DB Can not Call By Service");
            }
        } while (false);
    }
    public function checkStrictModel($object)
    {
        if (!$this->checkEnv()) {
            return;
        }
        $caller_class=$this->getCaller($object);
        $dn=$this->dn_class;
        
        $namespace=$dn::G()->options['namespace'];
        $namespace_service=$namespace."\\Service\\";
        
        do {
            if (substr($caller_class, 0, strlen($namespace_service))===$namespace_service) {
                break;
            }
            if (substr($caller_class, 0, 0-strlen("Service"))=="Service") {
                break;
            }
            if (substr($caller_class, 0, 0-strlen("ExModel"))=="ExModel") {
                break;
            }
            throw new Exception("Model Can Only call by Service or ExModel!");
        } while (false);
    }
    public function checkStrictSerice($object)
    {
        if (!$this->checkEnv()) {
            return;
        }
        $caller_class=$this->getCaller($object);
        $dn=$this->dn_class;
        
        $namespace=$dn::G()->options['namespace'];
        $namespace_service=$namespace."\\Service\\";
        
        if (substr($caller_class, 0, 0-strlen("BatchService"))==="BatchService") {
            return;
        }
        if (substr($caller_class, 0, 0-strlen("LibService"))==="LibService") {
            do {
                if (substr($caller_class, 0, strlen($namespace_service))===$namespace_service) {
                    break;
                }
                if (substr($caller_class, 0, 0-strlen("Service"))==="Service") {
                    break;
                }
                throw new Exception(true, "LibService Must Call By Serivce($caller_class)");
            } while (false);
        } else {
            do {
                if (substr($caller_class, 0, strlen($namespace_service))===$namespace_service) {
                    throw new Exception(true, "Service Can not call Service($caller_class)");
                }
                if (substr($caller_class, 0, strlen("Service"))==="Service") {
                    throw new Exception(true, "Service Can not call Service($caller_class)");
                }
                if (substr($caller_class, 0, strlen($namespace_model))===$namespace_model) {
                    throw new Exception(true, "Service Can not call by Model($caller_class)");
                }
                if (substr($caller_class, 0, strlen("Model"))==="Model") {
                    throw new Exception(true, "Service Can not call by Model($caller_class)");
                }
            } while (false);
        }
    }
    public function checkStrictParentCaller($object, $parent_class)
    {
        if (!$this->checkEnv()) {
            return;
        }
        $caller_class=$this->getCaller($object);
        $dn=$this->dn_class;
        
        $class=get_class($object);
        $flag=(is_subclass_of($caller_class, $parent_class) || $caller_class===$parent_class)?true:false;
        throw new Exception(!$flag, " checkStrictParentCaller Fail:Class [$class] Must By Calss [$parent_class]");
    }
}