<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Action\Sidebar\ProcessStatus;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\WorkContact\Constants\Event;
use MoChat\App\WorkContact\Contract\ContactEmployeeTrackContract;
use MoChat\App\WorkContact\Contract\ContactProcessContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户详情 - 编辑跟进状态.
 *
 * Class Update
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Inject
     * @var ContactProcessContract
     */
    private $contactProcess;

    /**
     * 互动轨迹表.
     * @Inject
     * @var ContactEmployeeTrackContract
     */
    private $track;

    /**
     * 联系人表.
     * @Inject
     * @var WorkContactContract
     */
    private $workContact;

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/contactProcessStatus/update", methods="PUT")
     * @return array
     */
    public function handle()
    {
        //接收参数
        $params['contactId'] = (int) $this->request->input('contactId');
        $params['statusId'] = (int) $this->request->input('statusId');

        //校验参数
        $this->validated($params);
        //记录轨迹
        $process = $this->contactProcess->getContactProcessById($params['statusId']);
        $content = '编辑用户跟进状态：' . $process['name'];
        ## 数据操作
        Db::beginTransaction();
        try {
            $this->recordTrack($params['contactId'], $content);
            ## 编辑用户跟进状态
            $this->workContact->updateWorkContactById($params['contactId'], ['follow_up_status' => $params['statusId']]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '编辑用户跟进状态失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }

        return [];
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'contactId' => 'required',
            'statusId' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'contactId.required' => '客户id必传',
            'statusId.required' => '跟进状态id必传',
        ];
    }

    /**
     * 记录轨迹.
     * @param $contactId
     * @param $content
     */
    private function recordTrack($contactId, $content)
    {
        $data = [
            'employee_id' => user()['workEmployeeId'],
            'contact_id' => $contactId,
            'content' => $content,
            'corp_id' => user()['corpId'],
            'event' => Event::PROCESS_STATUS,
        ];

        $res = $this->track->createContactEmployeeTrack($data);
        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '记录轨迹失败');
        }
    }
}
