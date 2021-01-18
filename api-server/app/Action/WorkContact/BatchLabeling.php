<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContact;

use App\Contract\WorkContactTagPivotServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 批量打标签.
 *
 * Class BatchLabeling
 * @Controller
 */
class BatchLabeling extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkContactTagPivotServiceInterface
     */
    private $contactTagPivotService;

    /**
     * @RequestMapping(path="/workContact/batchLabeling", methods="POST")
     */
    public function handle()
    {
        //接收参数
        $params['contactId'] = $this->request->input('contactId');
        $params['tagId']     = $this->request->input('tagId');

        //校验参数
        $this->validated($params);

        //客户id
        $contactIds = explode(',', $params['contactId']);
        //标签id
        $tagIds = explode(',', $params['tagId']);
        //查询客户已有标签id
        $columns = [
            'contact_id',
            'contact_tag_id',
        ];

        $contactInfo = $this->contactTagPivotService->getWorkContactTagPivotsByContactIdsTagIds($contactIds, $tagIds, $columns);

        //例如：客户A选择选了标签，“优质”、“跟进中”，客户B选择了标签“意向强烈”、“跟进中”，
        //那么两个客户在批量打标签的时候“跟进中”这个标签置灰不可以选择，
        //但是“优质”和“意向强烈”是可以进行选择，选择后对应的标签添加到原来并未设置的客户下

        $data = [];
        foreach ($contactIds as $val) {
            foreach ($tagIds as $v) {
                $data[] = [
                    'contact_id'     => $val,
                    'employee_id'    => user()['workEmployeeId'],
                    'contact_tag_id' => $v,
                ];
            }
        }

        foreach ($data as $key => $raw) {
            foreach ($contactInfo as $item) {
                //如果已经添加过该标签
                if ($raw['contact_id'] == $item['contactId'] && $raw['contact_tag_id'] == $item['contactTagId']) {
                    unset($data[$key]);
                }
            }
        }

        //批量添加标签
        $res = $this->contactTagPivotService->createWorkContactTagPivots($data);
        if ($res != true) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '批量打标签失败');
        }
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'contactId' => 'required',
            'tagId'     => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'contactId.required' => '客户id必传',
            'tagId.required'     => '标签id必传',
        ];
    }
}
