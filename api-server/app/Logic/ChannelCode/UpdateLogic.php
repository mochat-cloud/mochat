<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\ChannelCode;

use App\Constants\BusinessLog\Event;
use App\Constants\ChannelCode\Status;
use App\Contract\BusinessLogServiceInterface;
use App\Contract\ChannelCodeServiceInterface;
use App\Logic\ChannelCode\Traits\ChannelCodeTrait;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 编辑渠道码.
 *
 * Class UpdateLogic
 */
class UpdateLogic
{
    use ChannelCodeTrait;

    /**
     * 渠道码表.
     * @Inject
     * @var ChannelCodeServiceInterface
     */
    private $channelCode;

    /**
     * @Inject
     * @var BusinessLogServiceInterface
     */
    private $businessLog;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * 参数.
     * @var array
     */
    private $params;

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array
     */
    public function handle(array $params)
    {
        $this->params = $params;

        //校验引流成员每个时间段配置的企业成员人数 （微信[联系我]中每个联系方式最多配置100个使用成员（包含部门展开后的成员））
        $this->checkEmployee();

        //查询二维码微信配置id
        $info = $this->channelCode->getChannelCodeById(
            (int) $this->params['channelCodeId'],
            ['wx_config_id']
        );
        if (empty($info)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '查询不到渠道码信息');
        }

        //开启事务
        Db::beginTransaction();
        try {
            //编辑渠道码
            $this->updateChannelCode();
            //记录日志
            $this->recordLog();

            Db::commit();
        } catch (\Throwable $ex) {
            Db::rollBack();
            $this->logger->error('编辑渠道码失败', $params);
        }

        //更新二维码
        $this->handleQrCode(
            $this->params['drainageEmployee'],
            (int) $this->params['baseInfo']['autoAddFriend'],
            (int) user()['corpIds'][0],
            (int) $this->params['channelCodeId'],
            $info['wxConfigId']
        );

        return [];
    }

    /**
     * 编辑渠道码
     */
    private function updateChannelCode()
    {
        //编辑参数
        $data = [
            'group_id'          => $this->params['baseInfo']['groupId'],
            'auto_add_friend'   => $this->params['baseInfo']['autoAddFriend'],
            'tags'              => json_encode($this->params['baseInfo']['tags']),
            'type'              => $this->params['drainageEmployee']['type'],
            'drainage_employee' => json_encode($this->params['drainageEmployee']),
            'welcome_message'   => json_encode($this->params['welcomeMessage']),
        ];

        $res = $this->channelCode->updateChannelCodeById((int) $this->params['channelCodeId'], $data);
        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '编辑渠道码失败');
        }
    }

    /**
     * 记录日志.
     */
    private function recordLog()
    {
        $params = [
            'channelCodeId'    => $this->params['channelCodeId'],
            'baseInfo'         => $this->params['baseInfo'],
            'drainageEmployee' => $this->params['drainageEmployee'],
            'welcomeMessage'   => $this->params['welcomeMessage'],
        ];

        //记录日志表
        $data = [
            'business_id'  => $this->params['channelCodeId'],
            'params'       => json_encode($params),
            'event'        => Event::CHANNEL_CODE_UPDATE,
            'operation_id' => user()['workEmployeeId'],
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        $logRes = $this->businessLog->createBusinessLog($data);
        if (! is_int($logRes)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '编辑渠道码日志记录失败');
        }
    }

    /**
     * 校验每个时间段配置的企业成员人数.
     * @return array
     */
    private function checkEmployee()
    {
        //单人类型不需要校验
        if ($this->params['drainageEmployee']['type'] == 1) {
            return [];
        }

        //校验特殊时期配置人数
        if (! empty($this->params['drainageEmployee']['specialPeriod']['detail']) && $this->params['drainageEmployee']['specialPeriod']['status'] == Status::OPEN) {
            foreach ($this->params['drainageEmployee']['specialPeriod']['detail'] as &$value) {
                foreach ($value['timeSlot'] as &$v) {
                    if (count($v['employeeId']) > 100) {
                        throw new CommonException(ErrorCode::SERVER_ERROR, '特殊时期内每个时间段最多配置100个使用成员');
                    }
                }
                unset($v);
            }
            unset($value);
        }
        //校验企业成员配置人数
        if (! empty($this->params['drainageEmployee']['employees'])) {
            foreach ($this->params['drainageEmployee']['employees'] as &$val) {
                foreach ($val['timeSlot'] as &$v) {
                    if (count($v['employeeId']) > 100) {
                        throw new CommonException(ErrorCode::SERVER_ERROR, '特殊时期内每个时间段最多配置100个使用成员');
                    }
                }
                unset($v);
            }
            unset($val);
        }
        //处理员工添加上限
        if ($this->params['drainageEmployee']['addMax']['status'] == Status::OPEN) {
            if (count($this->params['drainageEmployee']['addMax']['spareEmployeeIds']) > 100) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '备用员工最多配置100个使用成员');
            }
        }
    }
}
