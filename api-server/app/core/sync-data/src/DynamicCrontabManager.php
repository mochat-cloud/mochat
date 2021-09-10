<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\SyncData;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Utils\ApplicationContext;
use MoChat\App\SyncData\Annotation\DynamicCrontab as DynamicCrontabAnnotation;
use Psr\SimpleCache\CacheInterface;

class DynamicCrontabManager
{
    /**
     * @var \Hyperf\Crontab\CrontabManager
     */
    protected $crontabManager;

    /**
     * @var \Hyperf\Contract\StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var \Hyperf\Contract\ConfigInterface
     */
    private $config;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(
        CrontabManager $crontabManager,
        StdoutLoggerInterface $logger,
        ConfigInterface $config,
        CacheInterface $cache
    ) {
        $this->crontabManager = $crontabManager;
        $this->logger = $logger;
        $this->config = $config;
        $this->cache = $cache;
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function register()
    {
        $crontabs = $this->parseCrontabs();
        $this->crontabRegister($crontabs);
    }

    public function schedule(DynamicCrontabAnnotation $annotation)
    {
        $callbackParams = $this->callSchedule($annotation->schedule);
        $lastCallbackParams = $this->getLastCallbackParams($annotation->name);
        $this->setCallbackParams($annotation->name, $callbackParams);
        [$addParams, $deleteParams] = $this->diffParams($callbackParams, $lastCallbackParams);

        $crontabs = [];
        if (! empty($addParams)) {
            foreach ($addParams as $addParam) {
                $crontabs[] = $this->buildCrontabByAnnotation($annotation, $addParam);
            }
        }

        if (! empty($deleteParams)) {
            foreach ($deleteParams as $deleteParam) {
                $crontabs[] = $this->buildCrontabByAnnotation($annotation, $deleteParam, true);
            }
        }

        $this->crontabRegister($crontabs);
    }

    protected function callSchedule(callable $schedule)
    {
        [$class, $method] = $schedule;
        if ($class && $method && class_exists($class) && method_exists($class, $method)) {
            $instance = make($class);
            return $instance->{$method}();
        }

        return [];
    }

    protected function crontabRegister(array $crontabs)
    {
        foreach ($crontabs as $crontab) {
            if ($crontab instanceof Crontab && $this->crontabManager->register($crontab)) {
                if ($this->config->get('debug.crontab', false)) {
                    $this->logger->debug(sprintf('Crontab %s have been registered.', $crontab->getName()));
                }
            }
        }
    }

    protected function diffParams(array $currentParams, array $lastParams): array
    {
        $add = array_diff($lastParams, $currentParams);
        $delete = array_diff($currentParams, $lastParams);
        return [$add, $delete];
    }

    protected function getLastCallbackParams(string $name)
    {
        $lastCallbackParams = $this->cache->get($this->getCacheKey($name));

        if (empty($lastCallbackParams)) {
            return [];
        }

        return $lastCallbackParams;
    }

    protected function setCallbackParams(string $name, array $params)
    {
        $this->cache->set($this->getCacheKey($name), $params, -1);
    }

    private function getCacheKey(string $name)
    {
        return sprintf('mochat:dynamicCrontab:%s', $name);
    }

    private function parseCrontabs(): array
    {
        $configCrontabs = $this->config->get('crontab.dynamic_crontab', []);
        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(DynamicCrontabAnnotation::class);
        $crontabs = [];
        foreach (array_merge($configCrontabs, $annotationCrontabs) as $crontab) {
            $dynamicCrontabs = [];
            if ($crontab instanceof DynamicCrontabAnnotation) {
                $dynamicCrontabs = $this->buildCrontabsByAnnotation($crontab);
            }
            if (empty($dynamicCrontabs)) {
                continue;
            }

            foreach ($dynamicCrontabs as $dynamicCrontab) {
                if ($dynamicCrontab instanceof Crontab) {
                    $crontabs[$crontab->getName()] = $dynamicCrontab;
                }
            }
        }
        return array_values($crontabs);
    }

    private function buildCrontabsByAnnotation(DynamicCrontabAnnotation $annotation): array
    {
        $crontabs = [$this->buildScheduleCrontabByAnnotation($annotation)];
        $callbackParams = $this->callSchedule($annotation->schedule);
        $this->setCallbackParams($annotation->name, $callbackParams);
        if (empty($callbackParams)) {
            return $crontabs;
        }

        foreach ($callbackParams as $callbackParam) {
            $crontabs[] = $this->buildCrontabByAnnotation($annotation, $callbackParam);
        }

        return $crontabs;
    }

    private function buildCrontabByAnnotation(DynamicCrontabAnnotation $annotation, $callbackParam, bool $isCancel = false): Crontab
    {
        $crontab = $this->buildBaseCrontab($annotation);

        if (isset($annotation->name)) {
            $name = $annotation->name;
            if (is_string($callbackParam) || is_numeric($callbackParam) || is_int($callbackParam)) {
                $name = $name . '-' . (string) $callbackParam;
            } else {
                $name = $name . '-' . sha1(serialize($callbackParam));
            }
            $crontab->setName($name);
        }

        if (isset($annotation->callback)) {
            $callback = $annotation->callback;
            $callback[2] = [$callbackParam];
            $crontab->setCallback($callback);
        }

        if ($isCancel) {
            $crontab->setEnable(false);
        }

        return $crontab;
    }

    private function buildScheduleCrontabByAnnotation(DynamicCrontabAnnotation $annotation): Crontab
    {
        $crontab = $this->buildBaseCrontab($annotation);

        isset($annotation->name) && $crontab->setName($annotation->name . '-schedule');
        $crontab->setCallback([$this, 'schedule', $annotation]);

        return $crontab;
    }

    private function buildBaseCrontab(DynamicCrontabAnnotation $annotation)
    {
        $crontab = new Crontab();
        isset($annotation->type) && $crontab->setType($annotation->type);
        isset($annotation->rule) && $crontab->setRule($annotation->rule);
        isset($annotation->scheduleRule) && $crontab->setRule($annotation->scheduleRule);
        isset($annotation->singleton) && $crontab->setSingleton($annotation->singleton);
        isset($annotation->mutexPool) && $crontab->setMutexPool($annotation->mutexPool);
        isset($annotation->mutexExpires) && $crontab->setMutexExpires($annotation->mutexExpires);
        isset($annotation->onOneServer) && $crontab->setOnOneServer($annotation->onOneServer);
        isset($annotation->memo) && $crontab->setMemo($annotation->memo);
        isset($annotation->enable) && $crontab->setEnable($this->resolveCrontabEnableMethod($annotation->enable));
        return $crontab;
    }

    /**
     * @param array|bool $enable
     */
    private function resolveCrontabEnableMethod($enable): bool
    {
        if (is_bool($enable)) {
            return $enable;
        }

        $className = reset($enable);
        $method = end($enable);

        try {
            $reflectionClass = ReflectionManager::reflectClass($className);
            $reflectionMethod = $reflectionClass->getMethod($method);

            if ($reflectionMethod->isPublic()) {
                if ($reflectionMethod->isStatic()) {
                    return $className::$method();
                }

                $container = ApplicationContext::getContainer();
                if ($container->has($className)) {
                    return $container->get($className)->{$method}();
                }
            }

            $this->logger->info('Crontab enable method is not public, skip register.');
        } catch (\ReflectionException $e) {
            $this->logger->error('Resolve crontab enable failed, skip register.');
        }

        return false;
    }
}
