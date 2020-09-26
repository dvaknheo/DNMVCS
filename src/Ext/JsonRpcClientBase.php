<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Ext\JsonRpcExt;

class JsonRpcClientBase extends ComponentBase
{
    public $_base_class = null;

    public function __construct()
    {
    }
    public function __call($method, $arguments)
    {
        $this->_base_class = $this->_base_class?$this->_base_class:JsonRpcExt::G()->getRealClass($this);
        $ret = JsonRpcExt::G()->callRPC($this->_base_class, $method, $arguments);
        return $ret;
    }
    public function init(array $options, ?object $context = null)
    {
        if ($this->_base_class) {
            return $this->_base_class->init($options, $context);
        }
        return parent::init($options, $context);
    }
    public function isInited(): bool
    {
        if ($this->_base_class) {
            return $this->_base_class->isInited();
        }
        return parent::isInited();
    }
}
