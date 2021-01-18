<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ContactFieldPivot;

use App\Constants\ContactField\Options;
use App\Constants\ContactField\Status;
use App\Contract\ContactFieldPivotServiceInterface;
use App\Contract\ContactFieldServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户详情 - 用户画像列表.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactFieldServiceInterface
     */
    private $contactField;

    /**
     * @Inject
     * @var ContactFieldPivotServiceInterface
     */
    private $contactFieldPivot;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/contactFieldPivot/index", methods="GET")
     */
    public function handle()
    {
        //接收参数
        $params['contactId'] = $this->request->input('contactId');
        //校验参数
        $this->validated($params);
        //查询所有开启的高级属性
        $fieldInfo = $this->contactField->getContactFieldsByStatusOrderByOrder((int) Status::EXHIBITION, ['id', 'label', 'type', 'options']);

        if (empty($fieldInfo)) {
            return [];
        }

        $fieldIds = array_column($fieldInfo, 'id');

        //查询客户用户画像
        $info = $this->contactFieldPivot->getContactFieldPivotsByOtherId($params['contactId'], $fieldIds, ['id', 'contact_field_id', 'value']);
        if (! empty($info)) {
            $info = array_column($info, null, 'contactFieldId');
        }

        foreach ($fieldInfo as &$raw) {
            $raw['contactFieldId'] = $raw['id'];
            $raw['name']           = $raw['label'];
            $raw['value']          = '';
            //多选
            if ($raw['type'] == Options::CHECKBOX) {
                //默认值
                $raw['value'] = [];
            }
            //图片
            if ($raw['type'] == Options::PICTURE) {
                //默认值
                $raw['pictureFlag'] = ''; //前端存值需要
            }

            $raw['typeText']            = Options::getMessage($raw['type']);
            $raw['contactFieldPivotId'] = '';

            //客户 用户画像
            if (isset($info[$raw['id']])) {
                $raw['contactFieldPivotId'] = $info[$raw['id']]['id'];
                $raw['value']               = $info[$raw['id']]['value'];
                //图片
                if ($raw['type'] == Options::PICTURE && ! empty($raw['value'])) {
                    $raw['pictureFlag'] = file_full_url($raw['value']); //前端存值需要
                }
                //多选
                if ($raw['type'] == Options::CHECKBOX) {
                    if (! empty($raw['value'])) {
                        $raw['value'] = explode(',', $raw['value']);
                    } else {
                        $raw['value'] = [];
                    }
                }
            }

            unset($raw['label'], $raw['id']);
        }
        unset($raw);

        return $fieldInfo;
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'contactId' => 'required|integer|min:1|bail',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'contactId.required' => '客户id必传',
        ];
    }
}
