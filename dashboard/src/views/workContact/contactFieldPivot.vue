<template>
  <div class="customer-detail">
    <a-card>
      <a-row>
        <a-col :span="3">
          <div class="img">
            <img :src="infoDetail.avatar" alt="">
          </div>
        </a-col>
        <a-col :span="16">
          <div class="data-box">
            <div class="name">
              <p>{{ infoDetail.name }}</p>
              <span>@微信</span>
              <a-icon v-if="infoDetail.gender === 1" type="man" />
              <a-icon v-else type="woman" />
            </div>
            <ul class="customer-data">
              <li>
                <p>备注名称：</p>
                <span v-if="infoDetail.remark !== ''">{{ infoDetail.remark }}</span>
                <span v-else>暂无</span>
                <a-icon v-permission="'/workContact/contactFieldPivot@update'" v-if="isContact" type="form" @click="() => { this.remarkModal = true }" />
              </li>
              <li class="tag">
                <p>标签：</p>
                <div v-if="infoDetail.tag && infoDetail.tag.length !== 0">
                  <span v-for="item in infoDetail.tag" :key="item.tagId" style="marginRight: 10px; color: #37AFD8">
                    {{ item.tagName }}
                  </span>
                </div>
                <span v-else>暂无</span>
                <a-icon v-permission="'/workContact/contactFieldPivot@update'" v-if="isContact" type="form" @click="() => { this.allTagModal = true; this.getAllTag() }" />
              </li>
              <li>
                <p>描述：</p>
                <span v-if="infoDetail.description !== ''">{{ infoDetail.description }}</span>
                <span v-else>暂无</span>
                <a-icon v-permission="'/workContact/contactFieldPivot@update'" v-if="isContact" type="form" @click="() => {this.descriptionModal = true}"/>
              </li>
              <li>
                <p>客户编号：</p>
                <span v-if="infoDetail.businessNo !== ''">{{ infoDetail.businessNo }}</span>
                <span v-else>暂无</span>
                <a-icon v-permission="'/workContact/contactFieldPivot@update'" v-if="isContact" type="form" @click="() => {this.businessNoModal = true}"/>
              </li>
            </ul>
          </div>
          <a-modal
            :visible="remarkModal"
            @cancel="() => {this.remarkModal = false}"
            title="修改备注">
            <a-input v-model="remark" :maxLength="10"/>
            <template slot="footer">
              <a-button @click="() => {this.remarkModal = false}">取消</a-button>
              <a-button type="primary" @click="editRemark">确定</a-button>
            </template>
          </a-modal>
          <a-modal
            :visible="descriptionModal"
            @cancel="() => {this.descriptionModal = false}"
            title="修改描述">
            <a-textarea
              v-model="description"
              :maxLength="150"
            />
            <template slot="footer">
              <a-button @click="() => {this.descriptionModal = false}">取消</a-button>
              <a-button type="primary" @click="editDescription">确定</a-button>
            </template>
          </a-modal>
          <a-modal
            :visible="businessNoModal"
            @cancel="() => {this.businessNoModal = false}"
            title="客户编号">
            <a-input v-model="businessNo" />
            <template slot="footer">
              <a-button @click="() => {this.businessNoModal = false}">取消</a-button>
              <a-button type="primary" @click="editBusinessNo">确定</a-button>
            </template>
          </a-modal>
          <a-modal
            :visible="allTagModal"
            title="为筛选的客户增加标签"
            @cancel="() => {this.allTagModal = false}">
            <a-form :label-col="{ span: 4 }" :wrapper-col="{ span: 17 }">
              <a-form-item label="选择分组">
                <a-select v-model="groupId" @change="selectChange">
                  <a-select-option value="">
                    所有分组
                  </a-select-option>
                  <a-select-option v-for="item in groupList" :key="item.groupId">
                    {{ item.groupName }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </a-form>
            <a-checkbox-group v-model="changeTagList" @change="onTagChange">
              <a-checkbox :disabled="tagCheckboxList.indexOf(item.id) !== -1" v-for="item in tagData" :key="item.id" :value="item.id">
                {{ item.name }}
              </a-checkbox>
            </a-checkbox-group>
            <template slot="footer">
              <a-button @click="() => { this.allTagModal = false }">取消</a-button>
              <a-button type="primary" @click="addTag">确定</a-button>
            </template>
          </a-modal>
        </a-col>
      </a-row>
      <div class="customer">
        <div class="two">
          <h3>所在群</h3>
          <p v-if="infoDetail.roomName && infoDetail.roomName.length !== 0">
            <span v-for="(item, val) in infoDetail.roomName" :key="val">{{ item }}</span>
          </p>
          <span v-else>暂无</span>
        </div>
        <div>
          <h3>归属企业成员</h3>
          <p>
            <span v-for="(item, index) in infoDetail.employeeName" :key="index">{{ item }}</span>
          </p>
        </div>
      </div>
      <a-tabs type="card" style="marginTop: 20px">
        <a-tab-pane v-permission="'/workContact/contactFieldPivot@track'" key="1" tab="互动轨迹">
          <template>
            <a-timeline v-for="item in trackList" :key="item.id">
              <a-timeline-item color="blue">
                {{ item.createdAt }}
                <p style="background: #eee; padding: 10px">
                  {{ item.content }}
                </p>
              </a-timeline-item>
            </a-timeline>
          </template>
        </a-tab-pane>
        <a-tab-pane v-permission="'/workContact/contactFieldPivot@detail'" key="2" tab="用户画像">
          <div v-permission="'/workContact/contactFieldPivot@edit'" class="btn-box">
            <div v-if="editPivotDis">
              <a-button style="marginRight: 10px;" @click="reset">取消</a-button>
              <a-button type="primary" @click="editPivot">提交</a-button>
            </div>
            <a-button v-else type="primary" @click="() => { this.editPivotDis = true }">编辑</a-button>
          </div>
          <div class="portrait">
            <div class="box-list" v-for="(item, index) in portraitList" :key="item.contactFieldId">
              <a-tag color="#108ee9">
                {{ item.name }}
              </a-tag>
              <a-select style="width:200px;" v-if="editPivotDis && item.typeText === '下拉'" v-model="item.value" @change="changePortrait">
                <a-select-option style="width: 100%" v-for="(val, inx) in item.options" :key="inx + 'e'" :value="val">
                  {{ val }}
                </a-select-option>
              </a-select>
              <a-radio-group v-if="editPivotDis && item.typeText === '单选'" v-model="item.value">
                <a-radio v-for="(valu, int) in item.options" :key="int + 'aw'" :value="valu">
                  {{ valu }}
                </a-radio>
              </a-radio-group>
              <a-input v-if="editPivotDis && item.typeText === '文本'" v-model="item.value"/>
              <a-input v-if="editPivotDis && item.typeText === '手机号'" v-model="item.value"/>
              <a-input v-if="editPivotDis && item.typeText === '日期'" v-model="item.value"/>
              <a-input v-if="editPivotDis && item.typeText === '邮箱'" v-model="item.value"/>
              <a-input v-if="editPivotDis && item.typeText === '数字'" v-model="item.value"/>
              <a-input v-if="editPivotDis && item.typeText === '生日'" v-model="item.value"/>
              <a-checkbox-group v-if="editPivotDis && item.typeText === '多选'" v-model="item.value" @change="changeItem(index)">
                <a-checkbox v-for="(val, ind) in item.options" :key="ind + val" :value="val">
                  {{ val }}
                </a-checkbox>
              </a-checkbox-group>
              <div v-if="editPivotDis && item.typeText === '图片'" @click="indexImg(index)">
                <upload
                  :imageUrl="item.pictureFlag"
                  @success="uploadSuccess"
                  :file-type="1"></upload>
              </div>
              <span v-if="!editPivotDis && item.typeText !== '图片' && item.typeText !== '多选'">{{ item.value }}</span>
              <span v-if="!editPivotDis && item.typeText === '多选' && item.value.length !== 0"> {{ item.value.join(',') }}</span>
              <span v-if="!editPivotDis && item.typeText === '多选' && item.value.length === 0">暂无</span>
              <span v-if="!editPivotDis && item.value === ''">暂无</span>

              <img v-if="!editPivotDis && item.typeText === '图片' && item.value !== ''" :src="item.pictureFlag" style="width:100px; height:100px"/>
            </div>
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-card>
  </div>
</template>

<script>
import { getWorkContactInfo, getUserPortrait, editWorkContactInfo, editUserPortrait, allTag, track } from '@/api/workContact'
import { getContactTagGroup } from '@/api/workContactTag'
import upload from '../mediumGroup/components/upload'
export default {
  components: {
    upload
  },
  data () {
    return {
      isContact: false,
      contactId: '',
      employeeId: '',
      infoDetail: {},
      remarkModal: false,
      tagModal: false,
      descriptionModal: false,
      businessNoModal: false,
      // 分组列表
      groupList: [],
      // 用户画像列表
      portraitList: [],
      // 备注
      remark: '',
      description: '',
      businessNo: '',
      // 用户画像编辑控制
      editPivotDis: false,
      allTagModal: false,
      groupId: '',
      tagData: [],
      tagCheckboxList: [],
      changeTagList: [],
      allTagList: [],
      // 图片上传展示
      imgUrl: '',
      checklist: [],
      imgIndex: null,
      trackList: []
    }
  },
  created () {
    this.contactId = this.$route.query.contactId
    this.employeeId = this.$route.query.employeeId
    if (this.$route.query.isContact == 1) {
      this.isContact = true
    } else {
      this.isContact = false
    }
    this.getInfoDetail()
    this.getPortrait()
    this.getTagGroup()
    this.getTrack()
  },
  methods: {
    getInfoDetail () {
      getWorkContactInfo({
        contactId: this.contactId,
        employeeId: this.employeeId
      }).then(res => {
        this.infoDetail = res.data
        this.remark = res.data.remark
        this.businessNo = res.data.businessNo
        this.description = res.data.description
        res.data.tag.map(item => {
          this.tagCheckboxList.push(item.tagId)
        })
      })
    },
    // 获取分组列表
    getTagGroup () {
      getContactTagGroup().then(res => {
        this.groupList = res.data
      })
    },
    // 获取用户画像
    getPortrait () {
      getUserPortrait({
        contactId: this.contactId
      }).then(res => {
        this.portraitList = res.data
        this.editPortraitList = JSON.parse(JSON.stringify(res.data))
        // res.data.map(item => {
        //   if (item.typeText === '图片') {
        //     item.pictureFlag = item.value
        //   }
        // })
      })
    },
    // 修改备注
    editRemark () {
      editWorkContactInfo({
        contactId: this.contactId,
        employeeId: this.employeeId,
        remark: this.remark
      }).then(res => {
        this.remarkModal = false
        this.getInfoDetail()
        this.getTrack()
      })
    },
    // 修改描述
    editDescription () {
      editWorkContactInfo({
        contactId: this.contactId,
        employeeId: this.employeeId,
        description: this.description
      }).then(res => {
        this.descriptionModal = false
        this.getInfoDetail()
        this.getTrack()
      })
    },
    // 修改客户编号
    editBusinessNo () {
      editWorkContactInfo({
        contactId: this.contactId,
        employeeId: this.employeeId,
        businessNo: this.businessNo
      }).then(res => {
        this.businessNoModal = false
        this.getInfoDetail()
        this.getTrack()
      })
    },
    // 编辑用户画像
    editPivot () {
      editUserPortrait({
        contactId: this.contactId,
        userPortrait: JSON.stringify(this.portraitList)
      }).then(res => {
        this.editPivotDis = false
        this.getPortrait()
        this.getTrack()
      })
    },
    // 所有标签
    getAllTag () {
      allTag({
        groupId: this.groupId
      }).then(res => {
        this.tagData = res.data
      })
    },
    // 复选框tag
    onTagChange (value) {
    },
    selectChange (value) {
      this.changeTagList.map(item => {
        this.allTagList.push(item)
      })
      this.groupId = value
      this.getAllTag()
    },
    addTag () {
      let tagList = []
      tagList = Array.from(new Set(this.tagCheckboxList.concat(this.allTagList, this.changeTagList)))
      if (tagList.length > 20) {
        return this.$message.error('只能添加20个标签')
      }
      editWorkContactInfo({
        contactId: this.contactId,
        employeeId: this.employeeId,
        tag: tagList
      }).then(res => {
        this.allTagModal = false
        this.getInfoDetail()
        this.groupId = ''
        this.allTagList = []
        this.changeTagList = []
        this.getTrack()
      })
    },
    // 用户画像下拉框
    changePortrait (value) {
    },
    // 上传图片
    uploadSuccess (data) {
      this.portraitList[this.imgIndex].value = data.path
      this.portraitList[this.imgIndex].pictureFlag = data.fullPath
    },
    indexImg (index) {
      this.imgIndex = index
    },
    // 用户画像 多选
    changeItem (index) {
      // this.portraitList[index].value = this.checklist.join()
    },
    // 取消
    reset () {
      this.editPivotDis = false
      this.portraitList = this.editPortraitList
      this.imgUrl = ''
    },
    // 互动轨迹
    getTrack () {
      track({
        contactId: this.contactId
      }).then(res => {
        this.trackList = res.data
      })
    }
  }
}
</script>

<style lang="less" scoped>
.customer-detail {
  .img {
    width: 150px;
    height: 150px;
    padding: 10px;
    img {
      width: 100%;
      height: auto;
    }
  }
  .data-box {
    width:100%;
    margin-left: 40px;
    .name {
      display: flex;
      p {
        margin: 0 5px 0 0;
        font-weight: 800;
        font-size: 20px;
      }
      span {
        margin-top: 8px;
        color: #1DCE7B;
        margin-right: 10px;
      }
    }
    .customer-data {
      padding: 0;
      li {
        height: 35px;
        line-height: 35px;
        display: flex;
        span {
          margin-right: 10px;
        }
        .anticon {
          margin-top: 10px;
        }
      }
      .tag {
        height: auto;
        display: flex;
        p {
          width: 65px;
          margin: 0;
        }
      }
    }
  }
  .customer {
    width: 100%;
    height: 120px;
    display: flex;
    background: #F2F2F2;
    border: 1px solid #E5E5E5;
    div {
      width: 50%;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 10px 0;
      h3 {
        font-weight: 800;
        margin-bottom: 15px;
      }
      p {
        text-align: center;
        overflow-y: scroll;
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
      }
    }
    .two {
      border-right: 1px solid #E5E5E5
    }
  }
  .btn-box {
    width: 13%;
    float: right;
    margin-bottom:20px;
  }
  .portrait {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    margin-top:20px;
    .box-list {
      width: 50%;
      height: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #DBDBDB;
      padding: 10px 30px;
      .ant-input {
        width: 200px;
      }
      .avatar-uploader {
        width: 20%;
      }
    }
  }
}
</style>
