'use strict';

module.exports = {

  types: [
    {
      value: 'WIP',
      name : '💪  WIP:      未完待续'
    },
    {
      value: 'feat',
      name : '✨  feat:     新的功能'
    },
    {
      value: 'fix',
      name : '🐞  fix:      Bug 修复'
    },
    {
      value: 'refactor',
      name : '🛠  refactor: 功能重构'
    },
    {
      value: 'docs',
      name : '📚  docs:     文档相关'
    },
    {
      value: 'test',
      name : '🏁  test:     测试相关'
    },
    {
      value: 'chore',
      name : '🗯  chore:    琐碎事项'
    },
    {
      value: 'style',
      name : '💅  style:    优化代码结构或格式'
    },
    {
      value: 'revert',
      name : '⏪  revert:   回退 commit'
    }
  ],

  scopes: ['系统页面', '客户', '客户群', '素材库', '运营', '设置', '框架'],

  allowCustomScopes: true,

  allowBreakingChanges: ["feat", "fix"],

  skipQuestions: ['footer']
};
