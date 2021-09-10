<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\File;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactSop\Contract\ContactSopContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;

/**
 * Class StoreLogic.
 */
class StoreLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var ContactSopContract
     */
    protected $contactSopService;

    /**
     * @Inject
     * @var WorkUnassignedContract
     */
    protected $workUnassignedService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    protected $workContactTagService;

    /**
     * @Inject
     * @var WorkContactTagPivotContract
     */
    protected $workContactTagPivotService;

    /**
     * @var WorkMessageContract
     */
    protected $workMessageService;

    /**
     * @param $params
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\Flysystem\FileExistsException
     * @return bool
     */
    public function handle($params)
    {
        //处理setting字段
        $params['setting'] = json_decode($params['setting']);

        foreach ($params['setting'] as &$param) {
            //处理下时间
            //秒
//            $timer = 0;
//            if ($param->time->type == 1) {
//                //1时 30分
//                $timer += (int)$param->time->data->first * 3600;
//                $timer += (int)$param->time->data->last * 60;
//            } else {
//                //1天 11:30
//                $timer += (int)$param->time->data->first * 86400;
//                $tempTime = explode(':', $param->time->data->last);
//                $timer += (int)$tempTime[0] * 3600 + (int)$tempTime[0] * 60;
//                $timer += (int)$param->time->data->last * 60;
//            }
//            $param->time = $timer;

            //处理下图片
            foreach ($param->content as &$item) {
                if ($item->type == 'image') {
                    $imageTemp = File::uploadBase64Image($item->value, 'image/contactSop/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
                    ##EasyWeChat上传图片
                    $localFile = File::download(file_full_url($imageTemp), $imageTemp);
                    $imagePath = $this->wxApp($params['corpId'], 'contact')->media->uploadImg($localFile);
                    if ((int) $imagePath['errcode'] === 0) {
                        $item->value = $imagePath['url'];
                    } else {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '上传图片失败');
                    }
                }
            }
        }

        $params['setting'] = json_encode($params['setting']);

        $data = [
            'corp_id' => $params['corpId'],
            'creator_id' => $params['userId'],
            'name' => $params['name'],
            'setting' => $params['setting'],
            'employee_ids' => $params['employees'],
            'state' => 0,
            'contact_ids' => '[]',
        ];
        return $this->contactSopService->createContactSop($data) ? true : false;
    }
}
