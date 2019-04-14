<?php
namespace DNMVCS;

// use DNAutoLoader
// use DNRoute
// use DNView
trait DNMVCS_Instance
{
    protected $dynamicClasses=[];
    protected $dynamicClassesInited=false;
    
    public function getBootInstances()
    {
        $ret=[
            DNAutoLoader::class => DNAutoLoader::G(),
            DNMVCS::class => DNMVCS::G(),
        ];
        $ret[static::class]=$this;
        return $ret;
    }
    protected function initDynamicClasses()
    {
        $this->dynamicClasses=[
            DNRoute::class,   	// for bindServerData,and $this->path_info ,and so on
            DNView::class,   	// for assign
        ];
    }
    public function getDynamicClasses()
    {
        if ($this->dynamicClassesInited) {
            $this->dynamicClassesInited=true;
            $this->initDynamicClasses();
        }
        return $this->dynamicClasses;
    }
    public function addDynamicClass($class)
    {
        if ($this->dynamicClassesInited) {
            $this->dynamicClassesInited=true;
            $this->initDynamicClasses();
        }
        return $this->dynamicClasses[]=$class;
    }
}
