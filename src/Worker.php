<?php
namespace Imi;

use Imi\Event\Event;
use Imi\Server\Event\Param\WorkerStartEventParam;

abstract class Worker
{
    /**
     * 当前进程的WorkerID
     *
     * @var int
     */
    private static $workerID;

    /**
     * 是否初始化完毕
     *
     * @return boolean
     */
    private static $isInited = false;

    /**
     * IMI.MAIN_SERVER.WORKER.START.APP 事件执行完毕
     *
     * @var boolean
     */
    private static $workerStartAppComplete = false;

    /**
     * 获取当前 worker 进程的 ID
     * 注意，不是进程ID
     *
     * @return int|null
     */
    public static function getWorkerID()
    {
        if(null === static::$workerID)
        {
            $main = ServerManage::getServer('main');
            if($main)
            {
                $workerID = $main->getSwooleServer()->worker_id;
                if($workerID > -1)
                {
                    static::$workerID = $workerID;
                }
            }
        }
        return static::$workerID;
    }

    /**
     * 是否初始化完毕
     *
     * @return boolean
     */
    public static function isInited()
    {
        return static::$isInited;
    }

    /**
     * 初始化完毕
     *
     * @return void
     */
    public static function inited()
    {
        static::$isInited = true;
        $mainServer = ServerManage::getServer('main');
        static::workerStartApp($mainServer, $mainServer);
    }

    /**
     * 触发 IMI.MAIN_SERVER.WORKER.START.APP 事件
     *
     * @param \Imi\Server\Base $server
     * @param mixed $target
     * @return void
     */
    public static function workerStartApp($server, $target)
    {
        if(!static::$workerStartAppComplete)
        {
            // 触发项目的workerstart事件
            Event::trigger('IMI.MAIN_SERVER.WORKER.START.APP', [
                'server'    => $server,
                'workerID'  => static::$workerID,
            ], $target, WorkerStartEventParam::class);
            static::$workerStartAppComplete = true;
        }
    }

    /**
     * 是否 IMI.MAIN_SERVER.WORKER.START.APP 事件执行完毕
     *
     * @return boolean
     */
    public static function isWorkerStartAppComplete()
    {
        return static::$workerStartAppComplete;
    }

    /**
     * 是否为 task 进程
     *
     * @return boolean
     */
    public static function isTask()
    {
        return ServerManage::getServer('main')->getSwooleServer()->taskworker;
    }
}