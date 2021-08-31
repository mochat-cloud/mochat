<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\SyncData\Annotation;

use Attribute;
use Hyperf\Crontab\Annotation\Crontab as CrontabAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class DynamicCrontab extends CrontabAnnotation
{
    /**
     * @var array|string
     */
    public $schedule = 'schedule';

    /**
     * @var string
     */
    public $scheduleRule;

    public function __construct(...$value)
    {
        parent::__construct(...$value);
        $this->bindMainProperty('scheduleRule', $value);
        if (! empty($this->scheduleRule)) {
            $this->scheduleRule = str_replace('\\', '', $this->scheduleRule);
        }
    }

    public function collectClass(string $className): void
    {
        $this->parseSchedule($className);
        parent::collectClass($className);
    }

    protected function parseSchedule(string $className): void
    {
        if (is_string($this->schedule)) {
            $this->schedule = [$className, $this->schedule];
        }
    }
}
