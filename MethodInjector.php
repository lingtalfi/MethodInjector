<?php


namespace MethodInjector;


use Bat\ClassTool;
use Bat\FileTool;
use MethodInjector\Method\Method;

class MethodInjector
{

    public static function create()
    {
        return new static();
    }


    public function getMethodsList($className, $methodFilter = null)
    {
        $ret = [];
        $r = new \ReflectionClass($className);
        if (null === $methodFilter) {
            $methodFilter = \ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC;
        }
        $methods = $r->getMethods($methodFilter);
        foreach ($methods as $method) {
            $ret[] = $method->getName();
        }
        return $ret;
    }

    /**
     * @return Method
     */
    public function getMethodByName($className, $methodName)
    {
        $c = ClassTool::getMethodContent($className, $methodName);
        $o = new Method();
        $o->setContent($c);
        $o->setName($methodName);
        return $o;
    }


    /**
     *
     */
    public function hasMethod($method, $className, $methodFilter = null)
    {
        $list = $this->getMethodsList($className, $methodFilter);
        foreach ($list as $methodName) {
            if ($method === $methodName) {
                return true;
            }
        }
        return false;
    }

    public function appendMethod(Method $method, $className)
    {
        $r = new \ReflectionClass($className);
        $file = $r->getFileName();
        list($a, $b) = FileTool::split($file, $r->getEndLine());

        $s = $a;
        $s .= PHP_EOL;
        $s .= "\t";
        $s .= $method->getContent();
        $s .= PHP_EOL;
        $s .= $b;
        file_put_contents($file, $s);
    }


    public function removeMethod(Method $method, $className)
    {
        try {

            $r = new \ReflectionMethod($className, $method->getName());
            $file = $r->getFileName();
            $start = $r->getStartLine();
            $end = $r->getEndLine();


            header("Content-Type: text/plain");
            list($a, $b) = FileTool::cut($file, $start, $end);
            $s = $a;
            $s .= PHP_EOL;
            $s .= $b;
            file_put_contents($file, $s);
        } catch (\ReflectionException $e) {
            // method not found in container
        }
    }


}