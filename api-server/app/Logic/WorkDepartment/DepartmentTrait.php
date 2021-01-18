<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkDepartment;

trait DepartmentTrait
{
    /**
     * 获取关联关系.
     * @param $department
     * @param $parentDepartment
     * @param string $path
     * @param int $level
     * @return array
     */
    protected function getDepartmentRelation($department, $parentDepartment, &$path = '', &$level = 0)
    {
        $path = '#' . $department['id'] . '#-' . $path;
        if (! empty($department['wxParentid'])) {
            $level = $level + 1;
        }
        if (! empty($department['wxParentid'])) {
            $this->getDepartmentRelation($parentDepartment[$department['wxParentid']], $parentDepartment, $path, $level);
        }
        return [
            'path'  => substr($path, 0, -1),
            'level' => $level,
        ];
    }
}
