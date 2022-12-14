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
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\App\WorkContact\Contract\WorkUnionidExternalUseridMappingContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

class UnionidConvertToExternalUseridLogic
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
     * @var int 企业id
     */
    protected $corpId;

    /**
     * @var string unionid
     */
    protected $unionid;

    /**
     * @var string openid
     */
    protected $openid;

    /**
     * @var int 小程序或公众号的主体类型, 0 主体是企业, 1主体是服务商
     */
    protected $subjectType;


    public function handle(int $corpId, string $unionid, string $openid, int $subjectType = 1)
    {
        $this->workUnionidExternalUseridMappingService = make(WorkUnionidExternalUseridMappingContract::class, [$corpId]);

        $this->corpId = $corpId;
        $this->unionid = $unionid;
        $this->openid = $openid;
        $this->subjectType = $subjectType;

        return $this->unionidConvertToExternalUserid();
    }

    /**
     * unionid转换为external_userid
     * @return mixed|string
     */
    protected function unionidConvertToExternalUserid()
    {
        //先查映射表
        $mapping = $this->getMapping();
        $externalUserid = $mapping['external_userid'] ?? '';
        //表中没有记录就调微信接口查询
        if (!$externalUserid) {
            $wxResult = $this->wxQueryExternalUserid();
            //将查询记录写入数据库
            $this->createOrUpdateMapping($wxResult, (int)($mapping['id'] ?? 0));
            $externalUserid = $wxResult['external_userid'] ?? '';
        }
        return $externalUserid;
    }

    /**
     * 查询映射记录
     * @return array
     */
    protected function getMapping()
    {
        return $this->workUnionidExternalUseridMappingService->getWorkMappingByCorpUnionidOpenidSubjectType($this->corpId, $this->unionid, $this->openid, $this->subjectType, ['id', 'external_userid']);
    }

    /**
     * 查询微信接口
     */
    protected function wxQueryExternalUserid()
    {
        $query = [
            'unionid' => $this->unionid,
            'openid' => $this->openid,
            'subject_type' => $this->subjectType,
        ];
        $weWorkApp = $this->weWorkFactory->getUserApp($this->corpId);
        $wxResult = $weWorkApp->user->httpPostJson('/cgi-bin/idconvert/unionid_to_external_userid', $query);
        if ($wxResult['errcode'] !== 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, $wxResult['errmsg']);
        }
        return $wxResult;
    }

    /**
     * 添加or更新映射表
     * @param array $wxResult
     * @param int $id
     */
    protected function createOrUpdateMapping(array $wxResult, int $id = 0)
    {
        if ($id) {
            if (!empty($wxResult['external_userid'])) {
                $mapping['external_userid'] = $wxResult['external_userid'];
            }
            if (!empty($wxResult['pending_id'])) {
                $mapping['pending_id'] = $wxResult['pending_id'];
            }
            if (!empty($mapping)) {
                $this->workUnionidExternalUseridMappingService->updateWorkMappingById($id, $mapping);
            }
        } else {
            $mapping = [
                'corp_id' => $this->corpId,
                'unionid' => $this->unionid,
                'openid' => $this->openid,
                'subject_type' => $this->subjectType,
                'external_userid' => $wxResult['external_userid'] ?? '',
                'pending_id' => $wxResult['pending_id'] ?? '',
            ];
            $this->workUnionidExternalUseridMappingService->createWorkMapping($mapping);
        }
    }
}