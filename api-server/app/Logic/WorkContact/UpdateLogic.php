<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkContact;

use App\Constants\WorkContact\Event;
use App\Contract\ContactEmployeeTrackServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\QueueService\WorkContact\UpdateContactTagApply;
use App\QueueService\WorkContact\UpdateRemarkApply;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 修改客户详情基本信息.
 *
 * Class UpdateLogic
 */
class UpdateLogic
{
    /**
     * @var UpdateRemarkApply
     */
    protected $service;

    /**
     * @var UpdateContactTagApply
     */
    protected $contactTagService;

    /**
     * 客户表.
     * @Inject
     * @var WorkContactServiceInterface
     */
    private $contact;

    /**
     * 员工表.
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    private $employee;

    /**
     * 员工 - 客户关联.
     * @Inject
     * @var WorkContactEmployeeServiceInterface
     */
    private $contactEmployee;

    /**
     * 标签表.
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTag;

    /**
     * 客户 - 标签关联表.
     * @Inject
     * @var WorkContactTagPivotServiceInterface
     */
    private $contactTagPivot;

    /**
     * 互动轨迹表.
     * @Inject
     * @var ContactEmployeeTrackServiceInterface
     */
    private $track;

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

        if (isset($this->params['tag'])) {
            //修改标签并记录轨迹
            $this->updateTag();
        }

        $data = [];
        //修改备注
        if (isset($this->params['remark'])) {
            //查询有没有员工跟该客户的对应关系
            $contactEmployee = $this->contactEmployee->getWorkContactEmployeeByOtherId($this->params['employeeId'], $this->params['contactId']);
            if (empty($contactEmployee)) {
                return [];
            }
            //如果有就修改备注
            $data['remark'] = $this->params['remark'];
            $res            = $this->contactEmployee
                ->updateWorkContactEmployeeByOtherIds($this->params['employeeId'], $this->params['contactId'], $data);

            if (! is_int($res)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '备注修改失败');
            }

            $content = '修改用户资料：备注';
            //记录轨迹
            $this->recordTrack($content, Event::INFO);
        }
        //修改描述
        if (isset($this->params['description'])) {
            //查询有没有员工跟该客户的对应关系
            $contactEmployee = $this->contactEmployee->getWorkContactEmployeeByOtherId($this->params['employeeId'], $this->params['contactId']);
            if (empty($contactEmployee)) {
                return [];
            }

            //如果有修改描述
            $data['description'] = $this->params['description'];
            $res                 = $this->contactEmployee
                ->updateWorkContactEmployeeByOtherIds($this->params['employeeId'], $this->params['contactId'], $data);

            if (! is_int($res)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '描述修改失败');
            }

            $content = '修改用户资料：描述';
            //记录轨迹
            $this->recordTrack($content, Event::INFO);
        }
        if (! empty($data)) {
            //将客户备注信息同步到企业微信
            $this->synRemark($data);
        }

        //修改客户编号
        if (isset($this->params['businessNo'])) {
            $res = $this->contact->updateWorkContactById((int) $this->params['contactId'], ['business_no' => $this->params['businessNo']]);

            if (! is_int($res)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '客户编号修改失败');
            }

            $content = '修改用户资料：客户编号';
            //记录轨迹
            $this->recordTrack($content, Event::INFO);
        }

        return [];
    }

    /**
     * 修改标签.
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function updateTag()
    {
        $addWxTag = [];
        // $removeWxTag = [];

        //查询员工对客户所打标签
        $contactTagPivot = $this->contactTagPivot->getWorkContactTagPivotsByOtherId($this->params['contactId'], $this->params['employeeId'], ['contact_tag_id']);

        //若客户已有标签
        if (! empty($contactTagPivot)) {
            //----------- 以下注释代码为取消客户已有标签 由于暂不支持取消 所以先注释 ---------

//            if (empty($this->params['tag'])) {  //若参数是将标签全部取消
//                //需要同步移除的标签微信id
//                $tagInfo = $this->getWorkContactTag($alreadyTagIds);
//                $removeWxTag = $tagInfo['wxTagIds'];
//
//                //删除客户标签
//                $res = $this->contactTagPivot->deleteWorkContactTagPivotsByTagId($alreadyTagIds);
//                if (! is_int($res)) {
//                    throw new CommonException(ErrorCode::SERVER_ERROR, '标签删除失败');
//                }
//            } else { //若不为空 则比对客户标签
//
//            }

//            //取差集 需要删掉的标签
//            $tagDiffOne = array_diff($alreadyTagIds, $currentTagIds);
//            if (! empty($tagDiffOne)) {
//                //需要同步移除的标签微信id
//                $tagInfo = $this->getWorkContactTag($tagDiffOne);
//                $removeWxTag = $tagInfo['wxTagIds'];
//                //删除本地客户标签
//                $res = $this->contactTagPivot->deleteWorkContactTagPivotsByTagId($tagDiffOne);
//
//                if (! is_int($res)) {
//                    throw new CommonException(ErrorCode::SERVER_ERROR, '标签删除失败');
//                }
//            }

            //已有标签id
            $alreadyTagIds = array_column($contactTagPivot, 'contactTagId');
            //本次修改标签id
            $currentTagIds = $this->params['tag'];
            //本次修改的标签id与已有标签id取差集 则为本次需要添加的标签
            $tagDiffTwo = array_diff($currentTagIds, $alreadyTagIds);

            if (! empty($tagDiffTwo)) {
                $data = [];
                foreach ($tagDiffTwo as $val) {
                    $data[] = [
                        'contact_id'     => $this->params['contactId'],
                        'employee_id'    => $this->params['employeeId'],
                        'contact_tag_id' => $val,
                    ];
                }

                //查询标签信息
                $tagInfo = $this->getWorkContactTag($tagDiffTwo);
                //需要同步添加的微信标签
                $addWxTag = $tagInfo['wxTagIds'];
                //标签名称
                $addTagName = $tagInfo['tagName'];

                //添加本地客户标签
                $res = $this->contactTagPivot->createWorkContactTagPivots($data);

                if ($res != true) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户标签失败');
                }
            }
        } else {  //若客户没有标签 直接添加
            //查询标签信息
            $tagInfo = $this->getWorkContactTag($this->params['tag']);
            //需要同步添加的微信标签id
            $addWxTag = $tagInfo['wxTagIds'];
            //标签名称
            $addTagName = $tagInfo['tagName'];

            $data = [];
            foreach ($this->params['tag'] as $val) {
                $data[] = [
                    'contact_id'     => $this->params['contactId'],
                    'employee_id'    => $this->params['employeeId'],
                    'contact_tag_id' => $val,
                ];
            }
            //添加客户标签
            $res = $this->contactTagPivot->createWorkContactTagPivots($data);

            if ($res != true) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加客户标签失败');
            }
        }

        //同步到企业微信
        $tagData = [];
        if (! empty($addWxTag)) {
            $tagData['add_tag'] = $addWxTag;
        }

        //移除标签（暂不做）
//        if (! empty($removeWxTag)) {
//            $tagData['remove_tag'] = $removeWxTag;
//        }

        if (! empty($tagData)) {
            //同步到企业微信
            $this->synContactTag($tagData);
        }

        //记录标签轨迹
        if (! empty($addTagName)) {
            $content = '系统对该客户打标签';

            foreach ($addTagName as $key => $value) {
                if ($key != count($addTagName) - 1) {
                    $content .= '【' . $value . '】、';
                } else {
                    $content .= '【' . $value . '】';
                }
            }

            $this->recordTrack($content, Event::TAG);
        }
    }

    /**
     * 记录轨迹.
     * @param $content
     * @param $event
     */
    private function recordTrack($content, $event)
    {
        $data = [
            'employee_id' => $this->params['employeeId'],
            'contact_id'  => $this->params['contactId'],
            'content'     => $content,
            'corp_id'     => user()['corpIds'][0],
            'event'       => $event,
        ];

        $res = $this->track->createContactEmployeeTrack($data);
        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '记录轨迹失败');
        }
    }

    /**
     * 根据标签id获取标签微信id和标签名.
     * @param $tagIds
     * @return array
     */
    private function getWorkContactTag($tagIds)
    {
        $wxTagIds = [];
        $tagName  = [];

        $tagInfo = $this->contactTag->getWorkContactTagsById($tagIds);
        if (! empty($tagInfo)) {
            $wxTagIds = array_column($tagInfo, 'wxContactTagId');
            $tagName  = array_column($tagInfo, 'name');
        }

        return [
            'wxTagIds' => $wxTagIds,
            'tagName'  => $tagName,
        ];
    }

    /**
     * 同步客户备注信息.
     * @param $data
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    private function synRemark($data)
    {
        $this->service = make(UpdateRemarkApply::class);

        //获取成员微信id
        $wxUserId = $this->getEmployeeId();
        if (! empty($wxUserId)) {
            //获取客户微信id
            $wxExternalUserId = $this->getContactId();
            if (! empty($wxExternalUserId)) {
                $params = [
                    'userid'          => $wxUserId,
                    'external_userid' => $wxExternalUserId,
                ];

                $data = array_merge($data, $params);

                $this->service->handle($data, user()['corpIds'][0]);
            }
        }
    }

    /**
     * 获取客户微信id.
     * @return string
     */
    private function getContactId()
    {
        $wxExternalUserId = '';
        //查询客户微信id
        $contact = $this->contact->getWorkContactById((int) $this->params['contactId'], ['wx_external_userid']);
        if (! empty($contact)) {
            $wxExternalUserId = $contact['wxExternalUserid'];
        }

        return $wxExternalUserId;
    }

    /**
     * 获取成员微信id.
     * @return string
     */
    private function getEmployeeId()
    {
        $wxUserId = '';
        $employee = $this->employee->getWorkEmployeeById((int) $this->params['employeeId'], ['wx_user_id']);
        if (! empty($employee)) {
            $wxUserId = $employee['wxUserId'];
        }

        return $wxUserId;
    }

    /**
     * 同步客户标签.
     * @param $data
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function synContactTag($data)
    {
        $this->contactTagService = make(UpdateContactTagApply::class);

        //获取成员微信id
        $wxUserId = $this->getEmployeeId();
        if (! empty($wxUserId)) {
            //获取客户微信id
            $wxExternalUserId = $this->getContactId();
            if (! empty($wxExternalUserId)) {
                $params = [
                    'userid'          => $wxUserId,
                    'external_userid' => $wxExternalUserId,
                ];

                $data = array_merge($data, $params);

                $this->contactTagService->handle($data, user()['corpIds'][0]);
            }
        }
    }
}
