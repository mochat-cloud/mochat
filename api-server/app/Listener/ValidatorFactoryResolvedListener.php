<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;

/**
 * 验证规则扩展
 * 扩展写法: 以后缀为Rule的方法即可
 * Class ValidatorFactoryResolvedListener.
 */
class ValidatorFactoryResolvedListener implements ListenerInterface
{
    /**
     * @var ValidatorFactoryInterface
     */
    protected $validatorFactory;

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /*  @var ValidatorFactoryInterface $validatorFactory */
        $this->validatorFactory = $event->validatorFactory;

        ## 获取所有规则方法
        $ruleMethods = get_class_methods($this);
        array_walk($ruleMethods, function ($item, $key) {
            strpos($item, 'Rule') !== false && $this->{$item}();
        });
    }

    /**
     * 手机号验证
     */
    protected function phoneRule(): void
    {
        // 注册了 phone 验证器
        $this->validatorFactory->extend('phone', function ($attribute, $value, $parameters, $validator) {
            return (bool) preg_match('/^1((34[0-8]\d{7})|((3[0-3|5-9])|(4[5-7|9])|(5[0-3|5-9])|(66)|(7[2-3|5-8])|(8[0-9])|(9[1|8|9]))\d{8})$/', $value);
        });
        // 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符这里扩展了 :phone 占位符
        $this->validatorFactory->replacer('phone', function ($message, $attribute, $rule, $parameters) {
            $message === 'validation.phone' && $message = ':phone格式错误';
            return str_replace(':phone', $attribute, $message);
        });
    }

    /**
     * 汉字验证
     */
    protected function chineseRule(): void
    {
        // 注册了 chinese 验证器
        $this->validatorFactory->extend('chinese', function ($attribute, $value, $parameters, $validator) {
            return (bool) preg_match('/[\x{4e00}-\x{9fa5}]+/u', $value);
        });
        // 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符这里扩展了 :chinese 占位符
        $this->validatorFactory->replacer('chinese', function ($message, $attribute, $rule, $parameters) {
            $message === 'validation.chinese' && $message = ':chinese必须为汉字';
            return str_replace(':chinese', $attribute, $message);
        });
    }

    /**
     * 字母.数字.汉字验证
     */
    protected function alphaNumChineseRule(): void
    {
        // 注册了 alpha_num_chinese 验证器
        $this->validatorFactory->extend('alpha_num_chinese', function ($attribute, $value, $parameters, $validator) {
            return (bool) preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', $value);
        });
        // 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符这里扩展了 :alpha_num_chinese 占位符
        $this->validatorFactory->replacer('alpha_num_chinese', function ($message, $attribute, $rule, $parameters) {
            $message === 'validation.alpha_num_chinese' && $message = ':alpha_num_chinese必须为汉字、字母、数字';
            return str_replace(':alpha_num_chinese', $attribute, $message);
        });
    }
}
