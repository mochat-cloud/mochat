<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Logic;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\StdoutLoggerInterface;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\App\WorkContact\Contract\WorkUnionidExternalUseridMappingContract;

class ExternalUseridConvertToUnionidLogic
{
    /**
     * @var WorkUnionidExternalUseridMappingContract
     */
    protected $workUnionidExternalUseridMappingService;

    /**
     * @Inject
     * @var WeWorkFactory
     */
    protected $weWorkFactory;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var int 企业id
     */
    protected $corpId;

    /**
     * @var string externalUserid
     */
    protected $externalUserid;

    /**
     * @var string chatId
     */
    protected $chatId;

    /**
     * @param int $corpId 企业id
     * @param string $externalUserid 外部联系人ID
     * @param string $chatId 微信群id
     * @return mixed|string
     */
    public function handle(int $corpId, string $externalUserid, string $chatId)
    {
        $this->workUnionidExternalUseridMappingService = make(WorkUnionidExternalUseridMappingContract::class, [$corpId]);

        $this->corpId = $corpId;
        $this->externalUserid = $externalUserid;
        $this->chatId = $chatId;

        return $this->externalUseridConvertToUnionid();
    }

    /**
     * external_userid转换unionid
     * @return mixed|string
     */
    protected function ExternalUseridConvertToUnionid()
    {
        //先查映射表
        $mapping = $this->getMappingByExternalUserid();
        if ($mapping) {
            return $mapping['unionid'];
        }
        //再调微信接口
        $wxResult = $this->wxQueryPendingId();
        if (!empty($wxResult['result'])) {
            foreach ($wxResult['result'] as $row) {
                if ($row['external_userid'] == $this->externalUserid) {
                    //有对应的记录时更新映射表
                    $mapping = $this->getMappingByPendingId($row['pending_id']);
                    if ($mapping) {
                        $this->updateMappingById($mapping['id'], ['external_userid' => $row['external_userid']]);
                        return $mapping['unionid'];
                    }
                }
            }
        }
        return '';
    }

    /**
     * 查询微信接口
     */
    protected function wxQueryPendingId()
    {
        $query = [
            'external_userid' => [$this->externalUserid],
            'chat_id' => $this->chatId,
        ];
        $weWorkApp = $this->weWorkFactory->getUserApp($this->corpId);
        $wxResult = $weWorkApp->user->httpPostJson('/cgi-bin/idconvert/batch/external_userid_to_pending_id', $query);
        if ($wxResult['errcode'] !== 0) {
            $this->logger->error('查询external_userid失败, ' . $wxResult['errmsg'] . ' ' . $this->externalUserid);
            return [];
        }
        return $wxResult;
    }

    /**
     * 查询映射记录
     * @return array
     */
    protected function getMappingByExternalUserid()
    {
        return $this->workUnionidExternalUseridMappingService->getWorkMappingByCorpExternalUserid($this->corpId, $this->externalUserid, ['id', 'unionid']);
    }

    /**
     * 查询映射记录
     * @return array
     */
    protected function getMappingByPendingId(string $pendingId)
    {
        return $this->workUnionidExternalUseridMappingService->getWorkMappingByCorpPendingId($this->corpId, $pendingId, ['id', 'unionid']);
    }

    /**
     * 更新映射记录表
     * @param int $id
     * @param array $data
     * @return int
     */
    protected function updateMappingById(int $id, array $data)
    {
        return $this->workUnionidExternalUseridMappingService->updateWorkMappingById($id, $data);
    }
}