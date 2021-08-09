<template>
  <div class="link-input">
    <div class="input">
      <a-input placeholder="请输入链接"></a-input>
      <a-button type="primary">选择互动雷达</a-button>
    </div>
    <div class="radio">
      <span class="title">高级设置：</span>
      <a-switch size="small" v-model="switchFlags.advanced"/>
    </div>
    <div class="link-form" v-if="switchFlags.advanced">
      <div class="item">
        <span>链接标题：</span>
        <a-input placeholder="请输入链接标题"></a-input>
      </div>
      <div class="item">
        <span>链接摘要：</span>
        <a-input placeholder="请输入链接摘要"></a-input>
      </div>
      <div class="item flex">
        <span>链接封面：</span>
        <m-upload/>
      </div>
    </div>
    <div class="radio">
      <span class="title">链接追踪设置：</span>
      <a-switch size="small" v-model="switchFlags.track" default-checked/>
    </div>
    <div class="track-form">
      <div class="item">
        <a-checkbox/>
        <span class="label">行为通知</span>
        <span class="desc">（当客户点击雷达链接时，发送雷达链接的员工将会收到消息提醒）</span>
      </div>
      <div class="item">
        <a-checkbox/>
        <span class="label">动态通知</span>
        <span class="desc">（当客户点击雷达链接时，会将客户的打开行为记录在客户动态里） </span>
      </div>
      <div class="item mb6">
        <a-checkbox/>
        <span class="label">客户标签</span>
        <span class="desc">（给点击雷达链接的客户打上选中的标签）</span>
      </div>
      <div class="client-tags mb16">
        <a-button size="small" class="mr10">添加标签</a-button>
        <a-tag closable>优秀</a-tag>
        <a-tag closable>良好</a-tag>
      </div>
      <div class="item">
        <a-checkbox/>
        <span class="label">设置客户评分<a class="ml6">什么是客户评分？</a></span>
      </div>
      <div class="score">
        <div class="item">
          <div class="flex">
            <span class="label">
              <a-icon type="delete" />
              规则1：
            </span>
            用户
            <a-select class="ml6 mr6" default-value="每打开一次" style="width: 140px">
              <a-select-option value="每打开一次">
                每打开一次
              </a-select-option>
              <a-select-option value="累计打开次数">
                累计打开次数
              </a-select-option>
            </a-select>
            时，可评
            <a-input class="ml6" style="width: 100px" addon-after="分"/>
          </div>
          <div class="ml60 mt10 flex">
            并打上标签：
            <a-switch size="small" class="mr10"/>
            <a-button size="small">添加标签</a-button>
          </div>
        </div>
        <div class="item">
          <div class="flex">
            <span class="label">
              <a-icon type="delete" />
              规则2：
            </span>
            用户
            <a-select class="ml6 mr6" default-value="每打开一次" style="width: 140px">
              <a-select-option value="每打开一次">
                每打开一次
              </a-select-option>
              <a-select-option value="累计打开次数">
                累计打开次数
              </a-select-option>
            </a-select>
            时，可评
            <a-input class="ml6" style="width: 100px" addon-after="分"/>
          </div>
          <div class="ml60 mt10 flex">
            并打上标签：
            <a-switch size="small" class="mr10"/>
            <a-button size="small">添加标签</a-button>
          </div>
        </div>
        <div class="footer">
          <a-icon type="plus-circle" class="mr6" />
          添加规则
        </div>
      </div>
      <div class="item">
        <a-checkbox/>
        <span class="label">保存为雷达链接</span>
      </div>
      <div class="item">
        <span>链接标题：</span>
        <a-input style="width: 60%;" placeholder="输入标题后即可保存该条链接为雷达链接"></a-input>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      switchFlags: {
        advanced: false,
        track: true
      }
    }
  }
}
</script>

<style lang="less" scoped>
.input {
  display: flex;
  align-items: center;
  margin-bottom: 16px;

  input {
    width: 293px;
    margin-right: 16px;
  }
}

.radio {
  margin-bottom: 8px;

  .title {
    font-weight: 600;
    color: rgba(0, 0, 0, .85);
  }
}

.link-form {
  margin: 16px 0;

  .item {
    margin-bottom: 16px;
  }
}

.track-form {
  .item {
    margin-bottom: 16px;

    .label {
      margin-left: 10px;
    }

    .desc {
      color: #909090;
    }
  }
}

.score {
  border-radius: 2px;
  border: 1px solid #eee;
  background: #fff;
  font-size: 14px;
  margin-bottom: 16px;
  margin-left: 26px;
  padding: 16px 16px 0;

  .item {
    margin-bottom: 20px;

    &:last-child {
      margin-bottom: 0;
    }
  }

  .label {
    font-weight: bold;
  }

  .footer {
    border-top: 1px dashed #e9e9e9;
    color: #1890ff;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: left;
    padding: 7px 15px;
    cursor: pointer;
  }
}
</style>
