<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\View;

class CallableView extends View
{
    public $options=[
            'callable_view_head'=>null,
            'callable_view_foot'=>null,
            'callable_view_class'=>null,
            'callable_view_prefix'=>null,
            'callable_view_skip_replace'=>false,
        ];
    public function init($options=[], $context=null)
    {
        $ret=parent::init($options, $context);
        $this->options=array_intersect_key(array_merge($this->options, $options), $this->options);
        if (!$this->options['callable_view_skip_replace']) {
            View::G(static::G());
        }
        return $ret;
    }
    protected function viewToCallback($func)
    {
        $ret=null;
        $func = str_replace('/', '_', $this->options['callable_view_prefix'].$func);
        $ret=($this->options['callable_view_class'])?[$this->options['callable_view_class'],$func]:$func;
        if (!is_callable($ret)) {
            return null;
        }
        return $ret;
    }
    
    public function _Show($data = [], $view)
    {
        $callback=$this->viewToCallback($view);
        if (null===$callback) {
            parent::_Show($data, $view);
            return;
        }
        $header=$this->viewToCallback($this->options['callable_view_head']?:$this->head_file);
        $footer=$this->viewToCallback($this->options['callable_view_foot']?:$this->foot_file);
        if (null!==$header) {
            ($header)($data);
        }
        ($callback)($data);
        if (null!==$footer) {
            ($footer)($data);
        }
    }
    public function _ShowBlock($view, $data = null)
    {
        $func=$this->viewToCallback($view);
        if (null!==$func) {
            ($func)($data);
            return;
        }
        return parent::_Show($data, $view);
    }
}