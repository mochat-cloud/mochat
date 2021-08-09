<template>
  <div class="white-bg-module">
    <a-tabs default-active-key="user">
      <a-tab-pane key="user" tab="潜在客户">
        <div class="radios-btns">
          <div class="radios">
            筛选：
            <a-radio-group v-model="filter">
              <a-radio :value="1"> 全部</a-radio>
              <a-radio :value="2"> 待分配</a-radio>
              <a-radio :value="3"> 待添加</a-radio>
              <a-radio :value="4"> 待通过</a-radio>
              <a-radio :value="4"> 已添加</a-radio>
            </a-radio-group>
          </div>
          <div class="btns">
            <a-button type="primary" @click="visible = true" icon="plus"> 导入客户</a-button>
            <a-button type="primary" icon="profile" @click="importDataClick"> 导入记录</a-button>
            <a-button type="primary" icon="setting"> 设置</a-button>
          </div>
        </div>
        <div class="search-table">
          <div class="search">
            <h5>共5个客户</h5>
            <div class="an-form">
              客户来源：
              <a-select default-value="lucy" style="width: 200px">
                <a-select-option value="jack"> 全部</a-select-option>
                <a-select-option value="lucy"> 手动录入</a-select-option>
                <a-select-option value="disabled"> 有赞平台下单</a-select-option>
                <a-select-option value="Yiminghe"> 手机线索导入</a-select-option>
              </a-select>
              <a-input-search placeholder="请搜素电话 / 备注名" style="width: 200px" />
            </div>
          </div>
          <a-table bordered class="table" rowKey="id" :data-source="tableData">
            <a-table-column title="电话号码" data-index="phone" />
            <a-table-column title="客服备注名字" data-index="name" />
            <a-table-column title="导入时间" data-index="date" />
            <a-table-column title="客户标签" data-index="tag" />
            <a-table-column title="添加状态" data-index="status" />

            <a-table-column title="添加时间" data-index="date1" />
            <a-table-column title="分配员工" data-index="status1" />
            <a-table-column title="分配次数" data-index="status2" />
            <a-table-column title="操作">
              <template slot-scope="record">
                <span :id="record.id">
                  <a> 提醒</a>
                  <a-divider type="vertical" />
                  <a>删除</a>
                </span>
              </template>
            </a-table-column>
          </a-table>
        </div>
      </a-tab-pane>
      <a-tab-pane key="total" tab="数据统计" force-render>
        <a-row :gutter="0" type="flex" justify="space-between">
          <a-col :span="24">
            <div class="child_module">
              <div class="child_title">总览</div>
              <a-row class="total_module" type="flex" :gutter="20" justify="space-between">
                <a-col class="item item_hr" :span="4">
                  <span>0</span>
                  <p>导入总客户数</p>
                  <a>详情</a>
                </a-col>
                <a-col class="item" :span="4">
                  <span>0</span>
                  <p>待分配客户数</p>
                  <a>详情</a>
                </a-col>
                <a-col class="item item_hr" :span="4">
                  <span>0</span>
                  <p>待添加客户数</p>
                  <a>详情</a>
                </a-col>
                <a-col class="item" :span="4">
                  <span>0</span>
                  <p>待通过客户数</p>
                  <a>详情</a>
                </a-col>
                <a-col class="item item_hr" :span="4">
                  <span>0</span>
                  <p>已添加总客户数</p>
                  <a>详情</a>
                </a-col>
                <a-col class="item" :span="4">
                  <span>0%</span>
                  <p>添加完成率</p>
                </a-col>
              </a-row>
            </div>
          </a-col>
        </a-row>
        <div class="add-table">
          <div class="search">
            <div class="an-form">
              <a-select default-value="lucy">
                <a-select-option value="jack"> 全部</a-select-option>
                <a-select-option value="lucy"> 今日</a-select-option>
                <a-select-option value="disabled"> 近一周</a-select-option>
                <a-select-option value="Yiminghe"> 近一月</a-select-option>
              </a-select>
              自定义：
              <a-range-picker />
              选择成员：
              <a-select default-value="lucy">
                <a-select-option value="jack"> 全部</a-select-option>
                <a-select-option value="lucy"> 今日</a-select-option>
                <a-select-option value="disabled"> 近一周</a-select-option>
                <a-select-option value="Yiminghe"> 近一月</a-select-option>
              </a-select>
            </div>
            <a-button type="default">重置</a-button>
          </div>
          <a-table class="table" rowKey="id" :data-source="tableData">
            <a-table-column title="成员" data-index="phone" />
            <a-table-column title="分配客户数" data-index="name" />

            <a-table-column title="待添加客户数" data-index="date"></a-table-column>
            <a-table-column title="待通过客户数" data-index="tag" />
            <a-table-column title="已添加客户数" data-index="status" />

            <a-table-column title="回收客户数" data-index="date1" />
            <a-table-column title="添加完成率" data-index="status1" />
            <a-table-column title="操作">
              <template slot-scope="record">
                <span :id="record.id">
                  <a>详情</a>
                </span>
              </template>
            </a-table-column>
          </a-table>
        </div>
      </a-tab-pane>
    </a-tabs>
    <a-modal
      title="设置"
      :footer="null"
      width="600px"
      :visible="settingVisible"
      dialogClass="setting-module"
      @cancel="visible = false"
    >
      <a-tabs type="card" default-active-key="3">
        <a-tab-pane key="1" class="" tab="自动提醒">
          <div>未分配提醒
            <a-switch size="small" default-checked />
          </div>
          <div class="num-date">
            超过
            <a-input style="width: 50px" />
            天，<span class="b">客户未分配跟进成员</span>，次日
            <a-time-picker
              format="HH:mm"
              style="width: 120px"
            />
            提醒管理员分配
          </div>
          <div>
            选择接收提醒管理员:
            <a-select mode="tags" style="width: 300px" placeholder="选择管理员">
              <a-select-option v-for="i in 25" :key="(i + 9).toString(36) + i">
                {{ (i + 9).toString(36) + i }}
              </a-select-option>
            </a-select>
          </div>
          <div class="hr"></div>
          <div>未分配提醒
            <a-switch size="small" default-checked />
          </div>
          <div class="num-date">
            超过
            <a-input style="width: 50px" />
            天，<span class="b">成员未添加客户</span>，次日
            <a-time-picker
              format="HH:mm"
              style="width: 120px"
            />
            提醒成员分配
          </div>
          <div class="btns">
            <a-button>取消</a-button>
            <a-button type="primary"> 保存</a-button>
          </div>
        </a-tab-pane>
        <a-tab-pane key="2" tab="自动回收">
          <div>未分配提醒
            <a-switch size="small" default-checked />
          </div>
          <div class="num-date">
            超过
            <a-input style="width: 50px" />
            天，<span class="b">客户未通过好友</span>， 自动转移到待分配
          </div>
          <div class="btns">
            <a-button>取消</a-button>
            <a-button type="primary"> 保存</a-button>
          </div>
        </a-tab-pane>
        <a-tab-pane key="3" tab="短信提醒">
          <div class="img"><img src="@/assets/batch-add-user-sms.png" /></div>
          <div class="open-btn">
            <a-button type="primary"> 开启短信功能</a-button>
          </div>
          <div class="title-info">
            <h2>应用场景</h2>
            <p>开启短信提醒后，员工点击复制手机号，将自动给客户发送一条短信，提高通过率。</p>
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-modal>
    <a-modal
      title="上传表格"
      :footer="null"
      :visible="visible"
      dialogClass="uploadExcel"
      @cancel="visible = false"
      :maskClosable="false">
      <div class="text">
        请下载模板后输入手机号上传，可批量复制手机号至模板内，若输入内容有重复手机号或空行将会自动过滤
      </div>
      <div class="download-template">点击下载<a>Excel模板</a></div>
      <a-form-model
        class="upload-form"
        ref="ruleForm"
        :model="form"
        :rules="rules"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="选择表格">
          <a-upload
            name="file"
            :multiple="false"
            :showUploadList="false"
            accept=".xls,.xlsx"
            @change="uploadTable"
            :headers="headers"
            action="https://project.mochat.zonrn.cn/contactBatchAdd/importStore"
          >
            <a-button>
              <a-icon type="plus" />
              添加文件
            </a-button>
          </a-upload>
        </a-form-model-item>

        <a-form-model-item label="选择员工" prop="delivery">
          <a-button @click="$refs.selectMember.show()">
            选择员工
          </a-button>
          <div class="ml16">
            <a-tag v-for="v in tableModel.allotEmployee" :key="v.id">
              {{ v.name }}
            </a-tag>
          </div>
        </a-form-model-item>
        <!--        abc-->
        <a-form-model-item label="客户标签">
          <div @click="showModal" class="choiceAdmin">
            <span class="operationTips" v-if="clientTagArr.length==0">请选择标签</span>
            <a-tag v-for="(obj,idx) in clientTagArr" :key="idx">
              {{ obj.name }}
              <a-icon type="close" @click.stop="delTagsArr(idx)" />
            </a-tag>
          </div>
        </a-form-model-item>
        <a-form-model-item :wrapper-col="{ span: 14, offset: 4 }">
          <a-button type="primary" @click="importContactClick">保存</a-button>
          <a-button style="margin-left: 10px" @click="visible = false"> 取消</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-modal>
    <addlableIndex ref="childRef" @choiceTagsArr="acceptArray"></addlableIndex>
    <selectMember ref="selectMember" @change="e => tableModel.allotEmployee = e" />
  </div>
</template>
<script>
import addlableIndex from '@/components/addlabel/index'
import selectMember from '@/components/Select/member'
import MaUpload from '@/components/MaUpload'
// eslint-disable-next-line no-unused-vars
import { contactList, importData, importContact } from '@/api/contactBatchAdd'
import storage from 'store'

export default {
  components: {
    selectMember, MaUpload, addlableIndex
  },
  computed: {
    headers () {
      const token = storage.get('ACCESS_TOKEN')
      return {
        Accept: `application/json`,
        Authorization: token
      }
    }
  },
  data () {
    return {
      // 客户选择的标签
      clientTagArr: [],
      filter: 1,
      visible: false,
      settingVisible: false,
      labelCol: { span: 4 },
      wrapperCol: { span: 14 },
      tableData: [],
      form: {
        excel: '',
        region: ''
      },
      rules: {
        excel: [{ required: true, message: '请选择表格', trigger: 'change' }]
      },
      tableModel: {
        allotEmployee: [],
        file: {}
      }
    }
  },
  mounted () {
    contactList().then(res => {
      console.log(res)
    })
  },
  methods: {
    // 上传表格
    uploadTable (e) {
      console.log(e)
    },
    showModal () {
      this.$refs.childRef.show(this.clientTagArr)
    },
    delTagsArr (idx) {
      this.clientTagArr.splice(idx, 1)
    },
    // 接收标签组件传值
    acceptArray (e) {
      if (e) {
        this.clientTagArr = e
      }
      //   const pagArray = []
      //   e.forEach((item, index) => {
      //     const tagsArray = {
      //       tagid: '',
      //       tagname: ''
      //     }
      //     tagsArray.tagid = item.id
      //     tagsArray.tagname = item.name
      //     this.tag_rule[this.selectInputIndex].tags.push(tagsArray)
      //     pagArray[index] = item
      //   })
      //   this.tag_rule[this.selectInputIndex].clientTagArr = pagArray
      // } else {
      //   this.tag_rule[this.selectInputIndex].clientTagArr = []
      //   this.tag_rule[this.selectInputIndex].tags = []
      // }
    },
    importContactClick () {
      if (!this.tableModel.allotEmployee.length) {
        this.$message.error('员工未选择')
        return false
      }
      if (JSON.stringify(this.tableModel.file) === '{}') {
        this.$message.error('表格未上传')
        return false
      }
      const data = new FormData()
      data.append('file', this.tableModel.file)
      data.append('title', `加好友`)
      this.tableModel.allotEmployee.forEach((value, index) => {
        data.append(`allotEmployee[${index}]`, value.wxUserId)
      })
      console.log(data)
      // importContact(data).then(res => {
      //   if (res.code === 200) {
      //     this.$message.success('导入成功')
      //   }
      //   this.visible()
      // })
    },

    /**
     * 删除表格文件
     */
    tableFileDel () {
      console.log(123)
      this.tableModel.file = {}
      return false
    },

    /**
     * 上传表格
     */
    tableFileUpload (e) {
      this.tableModel.file = e

      return false
    },

    /**
     * 导入记录
     */
    importDataClick () {
      importData().then(res => {
        if (res.code === 200) {
          this.$message.success('导入成功')
        }
      })
    }
  }
}
</script>

<style lang="less" scoped>
.choiceAdmin{
  width: 280px;
  min-height: 32px;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #dcdfe6;
  cursor: pointer;
  padding-left: 5px;
  margin-right: 8px;
  line-height: 30px;
}
.operationTips{
  color: #b2b2b2;
}
.white-bg-module {
  background-color: #fff;

  .radios-btns {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 00px 20px;

    .btns {
      .ant-btn {
        margin-left: 15px;
      }
    }
  }

  .search-table {
    background-color: #fff;
    box-shadow: 0px 0px 10px #ddd;
    margin: 20px;
    padding: 15px;

    .search {
      display: flex;
      justify-content: space-between;
      align-items: center;

      h5 {
        font-weight: bold;
        margin: 0px;
      }

      .ant-input-search {
        margin-left: 15px;
      }
    }

    .table {
      margin-top: 20px;
    }
  }

  .child_module {
    padding: 0px 25px;

    .child_title {
      font-size: 14px;
      margin-left: -10px;
      margin-bottom: 15px;
    }

    .total_module {
      background-color: #fbfdff;
      border: 1px solid #daedff;
      position: relative;
      padding: 10px 0px;

      .item {
        padding: 15px 0px;
        text-align: center;

        span {
          font-size: 20px;
          color: black;

          span {
            font-size: 22px;
            font-weight: bold;
            padding-left: 3px;
          }
        }

        p {
          color: #666;
        }
      }

      .item_hr::after {
        position: absolute;
        top: 30%;
        right: 0;
        height: 40%;
        width: 1px;
        content: '';
        background: #e9e9e9;
      }
    }
  }

  .add-table {
    padding: 0px 15px;
    margin-top: 15px;

    .title {
      padding: 15px 0px 20px;
      display: flex;
      font-weight: bold;
      align-items: center;
      justify-content: space-between;
    }

    .search {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;

      .ant-select {
        width: 120px;
        margin-right: 15px;
      }

      .ant-calendar-picker {
        margin-right: 15px;
      }
    }
  }
}

.uploadExcel {
  .text {
    background: #f0f8ff;
    border-radius: 2px;
    padding: 8px 17px 7px;
    font-size: 12px;
    color: rgba(0, 0, 0, 0.44);
    line-height: 17px;
  }

  .download-template {
    margin-top: 15px;

    a {
      padding-left: 4px;
      color: #1890ff;
    }
  }

  .upload-form {
    margin-top: 15px;

    .tag_client {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-right: 15px;
      border: 1px solid #d9d9d9;
      background-color: #fff;
      cursor: pointer;
      flex-wrap: nowrap;
      height: 40px;

      .icon {
        font-size: 14px;
        color: #999;
        flex-shrink: 0;
      }

      .tag {
        width: 100%;
        flex-shrink: 1;
        padding: 0px 15px;
        overflow: hidden;
        display: flex;
        cursor: pointer;
        justify-content: flex-start;
      }
    }
  }
}

.setting-module {
  .num-date {
    display: flex;
    align-items: center;
    margin: 15px 0px;

    .ant-input,
    .ant-time-picker {
      margin: 0px 5px;
    }

    .b {
      font-weight: bold;
    }
  }

  .hr {
    margin: 20px 0px;
    border-bottom: 1px dashed #e8e8e8;
  }

  .btns {
    display: flex;
    justify-content: flex-end;

    .ant-btn {
      margin-left: 10px;
    }
  }

  .img {
    text-align: center;

    img {
      width: 200px;
    }
  }

  .open-btn {
    text-align: center;
    padding: 20px 0px;
  }

  .title-info {
    height: 81px;
    background: #f9f9f9;
    border-radius: 2px;
    padding: 14px 16px;
    font-size: 13px;
    font-family: PingFangSC-Regular, PingFang SC;
    font-weight: 400;
    color: #000;
    margin: 10px auto 0px;
    line-height: 18px;

    h2 {
      font-size: 16px;
      font-weight: bold;
    }
  }
}
</style>
