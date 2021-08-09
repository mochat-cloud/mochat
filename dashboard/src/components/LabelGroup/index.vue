<template>
  <div>
    <a-modal title="选择标签" :footer="null" :visible="selectVisible" dialogClass="select-tags" @cancel="handleCancel">
      <div class="tag" v-for="(item, index1) in tagArray" :key="index1">
        <div class="name">{{ item.name }}：</div>
        <div class="tags">
          <template v-for="(tag, index) in item.tags">
            <a-tooltip v-if="tag.length > 20" :key="tag" :title="tag">
              <a-tag :key="tag" :closable="index !== 0" @close="() => handleClose(item.tags, index)">
                {{ `${tag.slice(0, 20)}...` }}
              </a-tag>
            </a-tooltip>
            <a-tag
              :color="tag.active == 1 ? '#108ee9' : ''"
              v-else
              :key="tag.id"
              :closable="index !== 0"
              @click="handleClick(tag)"
              @close="() => handleClose(item.tags, index)"
            >
              {{ tag.name }}
            </a-tag>
          </template>
          <a-input
            v-if="item.inputVisible"
            type="text"
            size="small"
            :id="'input' + index1"
            :style="{ width: '78px' }"
            v-model="item.inputValue"
            @blur="handleInputConfirm"
            @keyup.enter="handleInputConfirm"
          />
          <a-tag v-else style="background: #fff; borderstyle: dashed" @click="showInput(item, index1)">
            <a-icon type="plus" /> 添加
          </a-tag>
        </div>
      </div>
      <div class="add-btns">
        <div class="add" @click="addVisible = true"><a-icon type="plus" />新建标签组</div>
        <div class="btns">
          <a-button type="default">取消</a-button>
          <a-button type="primary">保存</a-button>
        </div>
      </div>
    </a-modal>
    <a-modal title="新建标签组" :footer="null" :visible="addVisible" dialogClass="add-tags" @cancel="handleAddCancel">
      <a-form-model :model="form" :label-col="labelCol" :wrapper-col="wrapperCol">
        <a-form-model-item label="标签组名称">
          <a-input v-model="form.name" placeholder="请输入分组名字" />
        </a-form-model-item>
        <a-form-model-item label="可见范围">
          <a-radio-group v-model="form.type">
            <a-radio value="1"> 全部员工 </a-radio>
            <a-radio value="2"> 部门可用 </a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item label="选择部门" v-if="form.type == 2">
          <a-button @click="choosePeopleShow = true" type="default" icon="plus"> 添加部门 </a-button>
        </a-form-model-item>
        <a-form-model-item label="标签名称">
          <div class="tag-item" v-for="item in 4" :key="item">
            <a-input v-model="form.name" placeholder="请输入标签名字" />
            <a-icon type="minus-circle" />
          </div>
          <div class="add-icon"><a-icon type="plus-circle" />添加标签</div>
        </a-form-model-item>

        <a-form-model-item :wrapper-col="{ span: 14, offset: 4 }">
          <a-button type="primary" @click="onSubmit"> 确定 </a-button>
          <a-button style="margin-left: 10px" @click="handleAddCancel">取消</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-modal>

    <a-modal title="选择企业成员" :maskClosable="false" :width="700" :visible="choosePeopleShow" @cancel="cancel">
      <department
        v-if="choosePeopleShow"
        :isSelected="selectList"
        :isChecked="employees"
        :memberKey="employees"
      ></department>
      <template slot="footer">
        <a-button @click="cancel">取消</a-button>
        <a-button type="primary" @click="choosePeopleShow = false">确定</a-button>
      </template>
    </a-modal>
  </div>
</template>
<script>
import department from '@/components/department'
export default {
  components: {
    department
  },
  data () {
    return {
      selectVisible: false,
      tagArray: [
        {
          name: '测试标签',
          inputVisible: false,
          inputValue: '',
          tags: [{
            id: 1234,
            name: '维护',
            active: 1
          },
          {
            id: 32421,
            name: '吃饭'
          }, {
            id: 132432,
            name: '哈哈',
            active: 1
          }]
        },
        {
          name: '公司标签',
          inputVisible: false,
          inputValue: '',
          tags: [{
            id: 1213,
            name: '白领',
            active: 0
          },
          {
            id: 2341,
            name: '公司',
            active: 1
          }, {
            id: 1234,
            name: '效率',
            active: 0
          }]
        }
      ],
      addVisible: false,
      labelCol: { span: 4 },
      wrapperCol: { span: 18 },
      form: {
        name: '',
        type: '1'
      },
      choosePeopleShow: false,
      // 成员列表
      employeeIdList: '',
      // 已选成员
      employees: [],
      // 成员人数
      employeeNum: 0,
      selectList: []
    }
  },
  methods: {
    onSubmit () {

    },
    handleClick (e) {
      if (e.active == 1) {
        e.active = 0
      } else {
        e.active = 1
      }
    },
    /**
     * 删除标签
     */
    handleClose (tags, index) {
      tags.splice(index, 1)
    },
    /**
     * 显示输入框
     */
    showInput (item, index) {
      item.inputVisible = true
      this.item = item
      this.$nextTick(function () {
        document.getElementById('input' + index).focus()
      })
    },
    /**
     * 新增标签
     */
    handleInputConfirm () {
      const inputValue = this.item.inputValue
      const tags = this.item.tags
      if (inputValue && tags.indexOf(inputValue) === -1) {
        this.item.tags.push({
          id: new Date().getTime(),
          name: inputValue
        })
      }
      this.item.inputVisible = false
      this.item.inputValue = ''
    },
    /**
  * 选择群主取消
  */
    cancel () {
      this.choosePeopleShow = false
      this.employeeIdList = ''
      this.employees = []
    },
    /**
     * 显示筛选弹窗
     */
    show () {
      this.selectVisible = true
    },
    /**
     * 关闭筛选弹窗
     */
    handleCancel () {
      this.selectVisible = false
    },
    /**
     * 取消新建标签分组
     */
    handleAddCancel () {
      this.addVisible = false
    }
  }
}
</script>
<style scoped lang="less">
.select-tags {
  border: 1px solid red;
  .tag {
    margin-bottom: 20px;
    .name {
      padding-bottom: 10px;
    }
  }
  .ant-tag {
    margin-bottom: 15px;
  }
  .add-btns {
    display: flex;
    justify-content: space-between;
    align-items: center;
    .add {
      color: #1890ff;
      cursor: pointer;
    }
    .active {
      background-color: #1890ff !important;
      color: #fff !important;
    }
    .ant-btn {
      margin-left: 10px;
    }
  }
}

.add-tags {
  .tag-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    .anticon-minus-circle {
      margin-left: 15px;
      font-size: 16px;
    }
  }
  .add-icon {
    color: #1890ff;
    margin-top: -10px;
    cursor: pointer;
    .anticon-plus-circle {
      margin-right: 5px;
    }
  }
}
</style>
