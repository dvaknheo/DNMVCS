<?php
namespace tests\DuckPhp\SingletonEx;

use DuckPhp\SingletonEx\SingletonEx;
use DuckPhp\SingletonEx\SimpleReplacer;

class SimpleReplacerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        SingletonExObject3::G();

        \MyCodeCoverage::G()->begin(SimpleReplacer::class);
        $t=\MyCodeCoverage::G();
        SimpleReplacer::Replace();
        SimpleReplacer::Replace();
        \MyCodeCoverage::G($t);
        //SimpleReplacer::ReplaceSingletonEx();
        SingletonExObjectX::G(new SingletonExObjectX());
        SingletonExObjectX::G();
        SingletonExObject2::G();
        
        SimpleReplacer::$EnableCompactable=true;
        SingletonExObject3::G();
        SingletonExObject4::G();

        \MyCodeCoverage::G()->end();

    }
}
class SingletonExObjectX
{
    use \DuckPhp\SingletonEx\SingletonEx;
    
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new static));
        return $_instance[$class];
    }

}
class SingletonExObject2 extends SingletonExObjectX{}
class SingletonExObject3 extends SingletonExObjectX{}
class SingletonExObject4 extends SingletonExObjectX{}