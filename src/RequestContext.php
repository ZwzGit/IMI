<?php
namespace Imi;

use Imi\Util\Coroutine;
use Imi\Server\Base;
use Imi\Bean\Container;

abstract class RequestContext
{
    private static $context = [];

    /**
     * 为当前请求创建上下文
     * @return void
     */
    public static function create()
    {
        if('cli' === PHP_SAPI)
        {
            $coID = Coroutine::getuid();
        }
        else
        {
            $coID = 1;
        }
        if(!isset(static::$context[$coID]))
        {
            static::$context[$coID] = [];
        }
        else
        {
            throw new \RuntimeException('Create context failed, cannot create a duplicate context');
        }
    }

    /**
     * 销毁当前请求的上下文
     * @return void
     */
    public static function destroy()
    {
        if('cli' === PHP_SAPI)
        {
            $coID = Coroutine::getuid();
        }
        else
        {
            $coID = 1;
        }
        if(isset(static::$context[$coID]))
        {
            unset(static::$context[$coID]);
        }
        else
        {
            throw new \RuntimeException('Destroy context failed, current context is not found');
        }
    }

    /**
     * 判断当前请求上下文是否存在
     * @return boolean
     */
    public static function exsits()
    {
        if('cli' === PHP_SAPI)
        {
            $coID = Coroutine::getuid();
        }
        else
        {
            $coID = 1;
        }
        return isset(static::$context[$coID]);
    }

    /**
     * 获取上下文数据
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if('cli' === PHP_SAPI)
        {
            $coID = Coroutine::getuid();
        }
        else
        {
            $coID = 1;
        }
        if(!isset(static::$context[$coID]))
        {
            throw new \RuntimeException('get context data failed, current context is not found');
        }
        if(isset(static::$context[$coID][$name]))
        {
            return static::$context[$coID][$name];
        }
        else
        {
            return $default;
        }
    }

    /**
     * 设置上下文数据
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function set($name, $value)
    {
        if('cli' === PHP_SAPI)
        {
            $coID = Coroutine::getuid();
        }
        else
        {
            $coID = 1;
        }
        if(!isset(static::$context[$coID]))
        {
            throw new \RuntimeException('set context data failed, current context is not found');
        }
        static::$context[$coID][$name] = $value;
    }

    /**
     * 获取当前上下文
     * @return array
     */
    public static function getContext()
    {
        if('cli' === PHP_SAPI)
        {
            $coID = Coroutine::getuid();
        }
        else
        {
            $coID = 1;
        }
        if(!isset(static::$context[$coID]))
        {
            throw new \RuntimeException('get context failed, current context is not found');
        }
        return static::$context[$coID];
    }

    /**
     * 获取当前的服务器对象
     * @return \Imi\Server\Base|null
     */
    public static function getServer()
    {
        return static::get('server');
    }

    /**
     * 在当前服务器上下文中获取Bean对象
     * @param string $name
     * @return mixed
     */
    public static function getServerBean($name, ...$params)
    {
        if('cli' === PHP_SAPI)
        {
            return static::getServer()->getBean($name, ...$params);
        }
        else
        {
            return App::getBean($name, ...$params);
        }
    }

    /**
     * 在当前请求上下文中获取Bean对象
     * @param string $name
     * @return mixed
     */
    public static function getBean($name, ...$params)
    {
        $container = static::get('container');
        if(null === $container)
        {
            $container = new Container;
            static::set('container', $container);
        }
        return $container->get($name, ...$params);
    }

}