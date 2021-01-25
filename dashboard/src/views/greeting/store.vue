<template>
  <div class="wrapper">
    <div class="left">
      <a-card title="客户账号">
        <div class="dialogue-box">
          <div class="portrait">
            <a-icon type="user" class="user" />
          </div>
          <div class="content">
            {{ addWelcomeData.words }}
          </div>
        </div>
        <div class="dialogue-box" v-if="mediumDetail.type == 2">
          <div class="portrait">
            <a-icon type="user" class="user" />
          </div>
          <div class="img-box">
            <img :src="mediumDetail.imageFullPath" alt="">
          </div>
        </div>
        <div class="dialogue-box" v-if="mediumDetail.type == 6">
          <div class="portrait">
            <a-icon type="user" class="user" />
          </div>
          <div class="applets-box">
            <h4>{{ mediumDetail.title }}</h4>
            <img :src="mediumDetail.imageFullPath" alt="">
          </div>
        </div>
        <div class="dialogue-box" v-if="mediumDetail.type == 3">
          <div class="portrait">
            <a-icon type="user" class="user" />
          </div>
          <div class="text-box">
            <h4>{{ mediumDetail.title }}</h4>
            <div>
              <span v-if="mediumDetail.description">{{ mediumDetail.description }}</span>
              <img :src="mediumDetail.imageFullPath" alt="">
            </div>
          </div>
        </div>
      </a-card>
    </div>
    <div class="right">
      <a-card>
        <a-form-model :label-col="{ span: 3 }" :wrapper-col="{ span: 14 }">
          <a-form-model-item label="企业微信成员：">
            <a-row>
              <a-col :lg="8">
                <a-radio-group v-model="addWelcomeData.rangeType">
                  <a-radio :disabled="isHadGeneral" :value="1" >
                    通用
                  </a-radio>
                  <a-radio :default-checked="isHadGeneral":value="2">
                    指定企业成员
                  </a-radio>
                </a-radio-group>
              </a-col>
              <a-col :lg="5">
                <a-button @click="() =>{this.choosePeopleShow = true}">选择成员</a-button>
              </a-col>
              <a-col :lg="7">
                <div v-if="employeeNum != 0">
                  <span>已选择{{ employeeNum }}名成员</span>
                  <a-button type="link" @click="() => {this.employeeIdList = ''; this.employees = [];this.employeeNum = 0}">重置</a-button>
                </div>
              </a-col>
            </a-row>
          </a-form-model-item>
          <a-form-model-item label="文本内容：">
            <a-textarea id="textarea" v-model="addWelcomeData.words" :rows="4" :maxLength="1000"/>
            <a-button @click="insert">+插入客户名称</a-button>
            <a-alert message="提示：火狐浏览器可能出现无法正确插入客户名称，请使用谷歌，360浏览器" type="info" />
          </a-form-model-item>
          <a-form-model-item label="添加内容：" v-if="mediumTitle === ''">
            <a-popover>
              <template slot="content">
                <a-button icon="file-image" @click="() => {this.visible = true; this.mediumType = 2; this.getMaterialLibrary()}">
                  图片
                </a-button>
                <a-button icon="link" @click="() => {this.imageTextModal = true; this.mediumType = 3; this.isImageText = true}">
                  图文
                </a-button>
                <a-button icon="paper-clip" @click="() => {this.appletsModal = true; this.mediumType = 6; this.isApplets = true}">
                  小程序
                </a-button>
              </template>
              <a-button>
                +添加图片/图文/小程序
              </a-button>
            </a-popover>
          </a-form-model-item>
          <a-form-model-item v-else class="medium-btn">
            <a-icon type="link" /> <span @click="showMedium">{{ mediumTitle }}</span> <a-icon type="close" @click="closeMedium"/>
          </a-form-model-item>
          <template>
            <a-button v-permission="'/greeting/store@add'" style="marginLeft: 50px" type="primary" :loading="btnLoading" @click="addWelcomeMesage">创建欢迎语</a-button>
          </template>
        </a-form-model>
      </a-card>
    </div>
    <a-modal
      title="选择企业成员"
      :maskClosable="false"
      :width="700"
      :visible="choosePeopleShow"
      @cancel="() => {this.choosePeopleShow = false; this.employeeIdList = ''; this.employees = []}"
    >
      <department
        v-if="choosePeopleShow"
        :isSelected="selectList"
        :isChecked="employees"
        :memberKey="employees"
        @change="peopleChange"
        @search="searchEmp"></department>
      <template slot="footer">
        <a-button @click="() => { this.choosePeopleShow = false; this.employeeIdList = ''; this.employees = [] }">取消</a-button>
        <a-button type="primary" @click="() => { this.choosePeopleShow = false }">确定</a-button>
      </template>
    </a-modal>
    <div class="pbox" ref="pbox">
      <a-modal
        :getContainer="() => $refs.pbox"
        width="900px"
        title="选择素材"
        :visible="visible"
        @cancel="() => { this.visible = false}">
        <a-row>
          <a-col :lg="8">
            <a-select v-model="mediumGroupId" @change="changeGroup" style="width: 100%">
              <a-select-option v-for="item in mediumGroupList" :key="item.id">
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :lg="8" :offset="1">
            <a-input-search
              v-model="searchValue"
              placeholder="输入要搜索的内容"
              enter-button="搜索"
              @search="getMaterialLibrary"
            />
          </a-col>
          <a-col :lg="3">
            <a-button @click="() => {this.searchValue = ''}">清空</a-button>
          </a-col>
          <a-col :lg="3" v-if="isImport == 1 || mediumType == 2">
            <a-button icon="download" @click="() => {this.localUploadModal = true}">本地上传</a-button>
          </a-col>
        </a-row>
        <div class="picture-box" v-if="mediumType === 2">
          <div class="nothing" v-if="materialData.length === 0">
            暂无数据
          </div>
          <div :class="mediumId === item.id ? 'active' : null" v-else v-for="item in materialData" :key="item.id" @click="definedMedium(item.id, item.content)">
            <img :src="item.content.imageFullPath" alt="">
            <span>{{ item.content.imageName }}</span>
          </div>
        </div>
        <div class="picture-box" v-else>
          <div class="nothing" v-if="materialData.length === 0">
            暂无数据
          </div>
          <div :class="mediumId === item.id ? 'active' : null" v-else v-for="item in materialData" :key="item.id" @click="definedMedium(item.id, item.content)">
            <img :src="item.content.imageFullPath" alt="">
            <span>{{ item.content.title }}</span>
          </div>
        </div>
        <template slot="footer">
          <a-button @click="resetImage">取消</a-button>
          <a-button type="primary" @click="() => {this.visible = false}">确定</a-button>
        </template>
      </a-modal>
    </div>
    <div class="tbox" ref="tbox">
      <a-modal
        width="800px"
        title="图文"
        :getContainer="() => $refs.tbox"
        :visible="imageTextModal"
        @cancel="() => { this.imageTextModal = false}">
        <a-form-model ref="imageForm" :model="imageTextData" :rules="imageForm" :label-col="{ span: 4 }" :wrapper-col="{ span: 18}">
          <a-form-model-item label="添加方式">
            <a-radio-group v-model="isImport" @change="importChange">
              <a-radio :value="0">新建</a-radio>
              <a-radio :value="1">导入</a-radio>
            </a-radio-group>
          </a-form-model-item>
          <div v-if="isImport === 0">
            <a-form-model-item label="图片封面">
              <div class="up-box" @click="mediumModal('image')" v-if="imgUrl == ''">+</div>
              <div class="img-box" v-else>
                <div class="imgs">
                  <img :src="imgUrl" alt="">
                </div>
                <a-button type="link" @click="mediumModal('isImage')">重新上传</a-button>
              </div>
            </a-form-model-item>
            <a-form-model-item label="填写标题" prop="title">
              <a-input :maxLength="32" v-model="imageTextData.title"></a-input>
            </a-form-model-item>
            <a-form-model-item label="添加描述">
              <a-textarea :rows="4" v-model="imageTextData.description" placeholder="填写图文描述" :maxLength="128"></a-textarea>
            </a-form-model-item>
            <a-form-model-item label="素材同步">
              <a-radio-group v-model="isSync">
                <a-radio :value="2">不同步</a-radio>
                <a-radio :value="1">同步至【内容引擎】</a-radio>
              </a-radio-group>
              <a-select class="select-box" v-model="mediumGroupId" v-if="isSync == 1">
                <a-select-option v-for="item in mediumGroupList" :key="item.id">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
            <a-form-model-item label="点击跳转" prop="imageLink">
              <a-input v-model="imageTextData.imageLink" placeholder="请输入跳转链接，且必须以http://或http://开头"></a-input>
            </a-form-model-item>
          </div>
          <div v-else>
            <a-form-model :label-col="{ span: 4 }" :wrapper-col="{ span: 18}">
              <a-form-model-item label="图片">
                <a-button type="primary" @click="importMedium('图文')" v-if="mediumTitle == ''">选择图文</a-button>
                <div class="import-box" v-else>
                  <a-icon type="check-circle" theme="twoTone" two-tone-color="#52c41a" /><span>已选择1条素材</span><a-button type="link" @click="() => {this.imageTextModal = false; this.importModal = true}">[修改]</a-button>
                </div>
              </a-form-model-item>
            </a-form-model>
          </div>
        </a-form-model>
        <template slot="footer">
          <a-button @click="reset('图片')">取消</a-button>
          <a-button type="primary" @click="addImageTextDefine">确定</a-button>
        </template>
      </a-modal>
    </div>
    <a-modal
      width="800px"
      title="新建图片素材"
      :getContainer="() => $refs.tbox"
      :visible="localUploadModal"
      @cancel="() => { this.localUploadModal = false}">
      <a-form-model :label-col="{ span: 4 }" :wrapper-col="{ span: 18}">
        <a-form-model-item label="上传图片">
          <upload
            :imageUrl="imgUrl"
            @success="uploadSuccess"
            :file-type="1"></upload>
          <p>(图片大小不超过2M，支持JPG、JPEG及PNG格式)</p>
        </a-form-model-item>
        <a-form-model-item label="素材同步">
          <a-radio-group v-model="isSync">
            <a-radio :value="2">不同步</a-radio>
            <a-radio :value="1">同步至【内容引擎】</a-radio>
          </a-radio-group>
          <a-select class="select-box" v-model="mediumGroupId" v-if="isSync == 1">
            <a-select-option v-for="item in mediumGroupList" :key="item.id">
              {{ item.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
      </a-form-model>
      <template slot="footer">
        <a-button @click="() => { this.localUploadModal = false; this.visible = true; this.isSync = 1; this.imgUrl = '' }">取消</a-button>
        <a-button type="primary" @click="uploadImage">确定</a-button>
      </template>
    </a-modal>
    <a-modal
      width="800px"
      title="小程序"
      :getContainer="() => $refs.tbox"
      :visible="appletsModal"
      @cancel="() => { this.appletsModal = false}">
      <a-form-model ref="appletsForm" :model="appletsData" :rules="appletsForm" :label-col="{ span: 4 }" :wrapper-col="{ span: 18}">
        <a-form-model-item label="添加方式">
          <a-radio-group v-model="isImport" @change="importChange">
            <a-radio :value="0">新建</a-radio>
            <a-radio :value="1">导入</a-radio>
          </a-radio-group>
        </a-form-model-item>
        <div v-if="isImport === 0">
          <a-form-model-item label="图片封面">
            <div class="up-box" @click="mediumModal('applets')" v-if="imgUrl == ''">+</div>
            <div class="img-box" v-else>
              <div class="imgs">
                <img :src="imgUrl" alt="">
              </div>
              <a-button type="link" @click="mediumModal('isApplets')">重新上传</a-button>
            </div>
          </a-form-model-item>
          <a-form-model-item label="填写标题" prop="title">
            <a-input v-model="appletsData.title" placeholder="请填写小程序标题(4-12个字符)" :maxLength="12" />
          </a-form-model-item>
          <a-form-model-item label="AppID" prop="appid">
            <a-input v-model="appletsData.appid" placeholder="请填写小程序AppID,必须是关联到企业的小程序应用"/>
          </a-form-model-item>
          <a-form-model-item label="page路径" prop="page">
            <a-input v-model="appletsData.page" placeholder="请填写小程序路径，例如：pages/index"/>
          </a-form-model-item>
          <a-form-model-item label="素材同步">
            <a-radio-group v-model="isSync">
              <a-radio :value="2">不同步</a-radio>
              <a-radio :value="1">同步至【内容引擎】</a-radio>
            </a-radio-group>
            <a-select class="select-box" v-model="mediumGroupId" v-if="isSync == 1">
              <a-select-option v-for="item in mediumGroupList" :key="item.id">
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
        </div>
        <div v-else>
          <a-form-model :label-col="{ span: 4 }" :wrapper-col="{ span: 18}">
            <a-form-model-item label="小程序">
              <a-button type="primary" @click="importMedium('小程序')" v-if="mediumTitle == ''">选择小程序</a-button>
              <div class="import-box" v-else>
                <a-icon type="check-circle" theme="twoTone" two-tone-color="#52c41a" /><span>已选择1条小程序</span><a-button type="link" @click="() => {this.appletsModal = false; this.importModal = true}">[修改]</a-button>
              </div>
            </a-form-model-item>
          </a-form-model>
        </div>
      </a-form-model>
      <template slot="footer">
        <a-button @click="reset('小程序')">取消</a-button>
        <a-button type="primary" @click="appletsDefind">确定</a-button>
      </template>
    </a-modal>
    <a-modal
      width="800px"
      title="选择素材"
      :getContainer="() => $refs.pbox"
      :visible="importModal"
      @cancel="() => { this.importModal = false}">
      <a-row>
        <a-col :lg="8">
          <a-select v-model="mediumGroupId" @change="changeGroup" style="width: 100%">
            <a-select-option v-for="item in mediumGroupList" :key="item.id">
              {{ item.name }}
            </a-select-option>
          </a-select>
        </a-col>
        <a-col :lg="8" :offset="1">
          <a-input-search
            v-model="searchValue"
            placeholder="输入要搜索的内容"
            enter-button="搜索"
            @search="getMaterialLibrary"
          />
        </a-col>
        <a-col :lg="3">
          <a-button @click="() => {this.searchValue = ''}">清空</a-button>
        </a-col>
      </a-row>
      <div class="picture-box">
        <div class="nothing" v-if="materialData.length === 0">
          暂无数据
        </div>
        <div :class="mediumId === item.id ? 'active' : null" v-else v-for="item in materialData" :key="item.id" @click="mediumImport(item.id, item.content)">
          <img :src="item.content.imageFullPath" alt="">
          <span>{{ item.content.title }}</span>
        </div>
      </div>
      <template slot="footer">
        <a-button @click="resetImport">取消</a-button>
        <a-button type="primary" @click="importDefined">确定</a-button>
      </template>
    </a-modal>
  </div>
</template>

<script>
import storage from 'store'
import upload from './components/upload'
import vpload from './components/vpload'
import department from './components/department'
import { greetingStore, greetingDetail, upDateGreeting } from '@/api/greeting'
import { materialLibraryList, mediumGroup, addMaterialLibrary } from '@/api/mediumGroup'
export default {
  components: {
    upload,
    department,
    vpload
  },
  data () {
    return {
      visible: false,
      imageTextModal: false,
      appletsModal: false,
      localUploadModal: false,
      btnLoading: false,
      importModal: false,
      greetingId: '',
      greetingData: {},
      searchValue: '',
      imgUrl: '',
      // 是否导入
      isImport: 0,
      // 是否同步
      isSync: 1,
      // 创建欢迎语
      addWelcomeData: {
        words: ''
      },
      // 素材库分组列表
      mediumGroupList: [],
      // 素材库分组id
      mediumGroupId: '',
      // 素材库类型
      mediumType: '',
      // 素材列表
      materialData: [],
      // 素材详情
      mediumDetail: {},
      // 选中素材或添加素材Id
      mediumId: 0,
      // 素材名称
      mediumTitle: '',
      // 选择图文
      isImageText: false,
      // 选择小程序
      isApplets: false,
      imageTextData: {},
      appletsData: {},
      upLoadRes: {},
      // 成员显示
      choosePeopleShow: false,
      // 成员列表
      employeeIdList: '',
      // 已选成员
      employees: [],
      // 成员人数
      employeeNum: 0,
      // 欢迎语类型
      welcomeType: [],
      // 是否已有通用
      hadGeneral: '',
      isHadGeneral: false,
      imageForm: {
        title: [
          { required: true, message: '请填写标题', trigger: 'blur' }
        ],
        imageLink: [{ required: true, message: '请输入跳转', trigger: 'blur' }]
      },
      appletsForm: {
        appid: [
          { required: true, message: '请输入小程序appID', trigger: 'blur' }
        ],
        page: [{ required: true, message: '请输入小程序路径', trigger: 'blur' }],
        title: [{ required: true, message: '请输入卡片标题', trigger: 'blur' }]
      },
      searchKeyWords: '',
      selectList: []
    }
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
  created () {
    this.greetingId = this.$route.query.greetingId
    this.hadGeneral = this.$route.query.hadGeneral
    this.selectList = this.$route.query.hadEmployees.split(',')
    if (this.hadGeneral == 1) {
      this.isHadGeneral = true
    }
    if (this.greetingId != undefined) {
      this.getGreetingDetail()
    }
  },
  methods: {
    // 获取欢迎语详情
    getGreetingDetail () {
      greetingDetail({
        greetingId: this.greetingId
      }).then(res => {
        const rangeType = res.data.rangeType
        const words = res.data.words
        this.addWelcomeData = {
          ...this.addWelcomeData,
          rangeType,
          words
        }
        this.employees = res.data.employees

        this.mediumDetail = res.data.mediumContent
        this.mediumTitle = res.data.mediumContent.title
        this.mediumId = res.data.mediumId
        this.imgUrl = res.data.mediumContent.imageFullPath
        if (res.data.mediumContent.imageLink != undefined) {
          this.mediumDetail.type = 3
          this.imageTextData = res.data.mediumContent
          this.mediumType = 3
        } else if (res.data.mediumContent.appid != undefined) {
          this.mediumDetail.type = 6
          this.mediumType = 6
          this.appletsData = res.data.mediumContent
        } else if (res.data.mediumContent.imageLink == undefined && res.data.mediumContent.appid == undefined && res.data.mediumContent.imageFullPath == undefined) {
          this.mediumDetail = {}
          this.imgUrl = ''
          this.mediumTitle = ''
        } else if (res.data.mediumContent.imageLink == undefined && res.data.mediumContent.appid == undefined) {
          this.mediumDetail.type = 2
          this.mediumType = 2
          this.mediumTitle = res.data.mediumContent.imageName
        }
      })
    },
    // 上传
    uploadSuccess (data) {
      this.imgUrl = data.fullPath
      const imagePath = data.path
      this.upLoadRes.imageFullPath = data.fullPath
      this.upLoadRes.imagePath = imagePath
      this.upLoadRes.imageName = data.name
    },
    // 插入客户名称
    insert () {
      const textarea = document.getElementById('textarea')
      const start = textarea.selectionStart// input 第0个字符到选中的字符
      console.log(start)
      this.addWelcomeData.words = this.addWelcomeData.words.slice(0, start) + '##客户名称##' + this.addWelcomeData.words.slice(start)
    },
    // 获取素材库
    getMaterialLibrary () {
      materialLibraryList({
        mediumGroupId: this.mediumGroupId,
        searchValue: this.searchValue,
        type: this.mediumType
      }).then(res => {
        this.materialData = res.data.list
      })
      this.getMediumGroup()
    },
    // 获取素材库分组列表
    getMediumGroup () {
      mediumGroup().then(res => {
        this.mediumGroupList = res.data
        this.mediumGroupId = 0
      }).catch(res => {
        this.mediumGroupId = ''
      })
    },
    // 素材库分组改变
    changeGroup (value) {
      this.mediumGroupId = value
      this.getMaterialLibrary()
    },
    // 选择图片
    mediumModal (type) {
      this.visible = true
      if (this.mediumType == 3 || type == 'isImage') {
        this.imageTextModal = false
        this.isImageText = true
        this.mediumType = 2
      } else if (this.mediumType == 6 || type == 'isApplets') {
        this.appletsModal = false
        this.isApplets = true
        this.mediumType = 2
      }
      this.getMaterialLibrary()
    },
    // 点击素材
    definedMedium (id, content) {
      this.visible = false
      if (this.isImageText) {
        this.upLoadRes = content
        this.imgUrl = content.imageFullPath
        this.imageTextModal = true
        this.isImageText = false
        this.mediumType = 3
      } else if (this.isApplets) {
        this.upLoadRes = content
        this.imgUrl = content.imageFullPath
        this.appletsModal = true
        this.isApplets = false
        this.mediumType = 6
      } else {
        this.mediumId = id
        this.upLoadRes = content
        this.mediumTitle = content.imageName
        this.mediumDetail = content
        this.mediumDetail.type = 2
        this.mediumType = 2
      }
    },
    // 添加方式切换
    importChange () {
      this.imageTextData = {}
      this.appletsData = {}
      this.imgUrl = ''
      this.upLoadRes = {}
      this.mediumDetail = {}
      this.mediumTitle = ''
    },
    // 导入
    importMedium (type) {
      this.importModal = true
      if (type == '图文') {
        this.mediumType = 3
        this.imageTextModal = false
      } else {
        this.mediumType = 6
        this.appletsModal = false
      }
      this.getMaterialLibrary()
    },
    // 导入选择
    mediumImport (id, content) {
      this.mediumId = id
      this.mediumTitle = content.title
      this.mediumDetail = content
      this.mediumDetail.imageFullPath = content.imageFullPath
    },
    // 导入确定
    importDefined () {
      if (this.mediumId == '') {
        this.$message.error('请选择素材')
        return
      }
      this.importModal = false
      if (this.mediumType == 3) {
        this.imageTextModal = false
        this.mediumDetail.type = 3
      } else {
        this.appletsModal = false
        this.mediumDetail.type = 6
      }
    },
    // 取消导入
    resetImport () {
      this.importModal = false
      this.mediumDetail = {}
      this.mediumTitle = ''
      this.mediumId = ''
      if (this.mediumType == 3) {
        this.imageTextModal = true
      } else {
        this.appletsModal = true
      }
    },
    // 上传图片
    uploadImage () {
      if (this.imgUrl == '') {
        this.$message.error('请上传')
        return
      }
      addMaterialLibrary({
        type: 2,
        isSync: this.isSync,
        content: this.upLoadRes,
        mediumGroupId: this.mediumGroupId
      }).then(res => {
        this.localUploadModal = false
        this.visible = false
        if (this.isImageText) {
          this.isImageText = false
          this.imageTextModal = true
          this.mediumType = 3
        } else if (this.isApplets) {
          this.isApplets = false
          this.appletsModal = true
          this.mediumType = 6
        } else {
          this.mediumDetail.type = 2
          this.mediumTitle = this.upLoadRes.imageName
          this.mediumDetail = this.upLoadRes
          this.mediumDetail.imageFullPath = this.upLoadRes.imageFullPath
          this.mediumId = res.data.id
        }
      })
    },
    // 添加图文
    addImageTextDefine () {
      if (this.isImport == 1) {
        if (this.mediumId == '') {
          this.$message.error('请选择素材')
          return
        }
        this.appletsModal = false
        this.isImport = 0
        this.mediumDetail.type = 3
      } else {
        const reg = /(http|https):\/\/([\w.]+\/?)\S*/
        if (!(reg.test(this.imageTextData.imageLink))) {
          this.$message.error('点击跳转以http://或http://开头')
          return
        }
        this.$refs.imageForm.validate(valid => {
          if (valid) {
            const content = {
              title: this.imageTextData.title,
              description: this.imageTextData.description,
              imagePath: this.upLoadRes.imagePath,
              imageLink: this.imageTextData.imageLink,
              imageName: this.upLoadRes.imageName
            }
            if (this.upLoadRes.imagePath == undefined) {
              this.$message.error('请上传图片')
              return
            }
            addMaterialLibrary({
              type: this.mediumType,
              mediumGroupId: this.mediumGroupId,
              content: content,
              isSync: this.isSync
            }).then(res => {
              this.mediumType = 3
              this.mediumId = res.data.id
              this.mediumDetail = content
              this.mediumDetail.imageFullPath = this.upLoadRes.imageFullPath
              this.mediumDetail.type = 3
              this.mediumTitle = content.title
              this.imageTextModal = false
            })
          } else {
            console.log('error submit!!')
            return false
          }
        })
      }
    },
    // 添加小程序
    appletsDefind () {
      if (this.isImport == 1) {
        if (this.mediumId == '') {
          this.$message.error('请选择素材')
          return
        }
        this.appletsModal = false
        this.isImport = 0
        this.mediumDetail.type = 6
      } else {
        this.$refs.appletsForm.validate(valid => {
          if (valid) {
            const content = {
              title: this.appletsData.title,
              page: this.appletsData.page,
              imagePath: this.upLoadRes.imagePath,
              appid: this.appletsData.appid,
              imageName: this.upLoadRes.imageName
            }
            if (this.upLoadRes.imagePath == undefined) {
              this.$message.error('请上传图片')
              return
            }
            addMaterialLibrary({
              type: this.mediumType,
              mediumGroupId: this.mediumGroupId,
              content: content,
              isSync: this.isSync
            }).then(res => {
              this.mediumType = 6
              this.mediumId = res.data.id
              this.mediumTitle = content.title
              this.mediumDetail = content
              this.mediumDetail.imageFullPath = this.upLoadRes.imageFullPath
              this.mediumDetail.type = 6
              this.appletsModal = false
            })
          } else {
            console.log('error submit!!')
            return false
          }
        })
      }
    },
    // 取消图片
    resetImage () {
      this.visible = false
      this.mediumGroupId = 0
      this.mediumId = ''
      if (this.isImageText) {
        this.imageTextModal = true
        this.mediumType = 3
      } else if (this.isApplets) {
        this.appletsModal = true
        this.mediumType = 6
      }
    },
    // 取消
    reset (type) {
      this.imgUrl = ''
      this.isImport = 0
      this.isSync = 1
      if (type === '图片') {
        this.imageTextModal = false
        this.imageTextData = {}
        this.upLoadRes = {}
        this.isImageText = false
      } else {
        this.appletsData = {}
        this.appletsModal = false
        this.upLoadRes = {}
        this.isApplets = false
      }
    },
    // 查看素材
    showMedium () {
      if (this.mediumType == 2) {
        this.visible = true
        this.getMaterialLibrary()
      } else if (this.mediumType == 3) {
        this.imageTextModal = true
      } else {
        this.appletsModal = true
      }
    },
    // 关闭素材
    closeMedium () {
      this.mediumDetail = {}
      this.mediumTitle = ''
      this.mediumId = ''
      this.imageTextData = {}
      this.appletsData = {}
      this.upLoadRes = {}
      this.imgUrl = ''
      this.isImport = 0
    },
    // 成员选择
    peopleChange (data) {
      const arr = []
      data.map(item => {
        arr.push(item.employeeId)
      })
      this.employeeNum = arr.length
      this.employeeIdList = arr.join(',')
    },
    searchEmp (data) {
      this.employeeNum = data.length
      if (data.length === 0) {
        this.employeeIdList = '空'
      } else {
        this.employeeIdList = data[0].id
      }
    },
    // 创建欢迎语
    addWelcomeMesage () {
      let type = ''
      if (this.addWelcomeData.words !== '' && this.mediumType !== '') {
        type = 1 + ',' + this.mediumType
      } else if (this.mediumType !== '') {
        type = this.mediumType + ''
      } else {
        type = '1'
      }
      if (this.addWelcomeData.rangeType == undefined) {
        this.$message.error('请选择成员类型')
        return
      }
      if (this.addWelcomeData.words == '' && this.mediumId == 0) {
        this.$message.error('欢迎语内容不能为空！')
        return
      }
      if (this.addWelcomeData.rangeType == 2 && this.employeeIdList == '') {
        this.$message.error('请选择成员')
        return
      }
      if (this.greetingId != undefined) {
        const params = {
          rangeType: this.addWelcomeData.rangeType,
          employees: this.employeeIdList,
          type: type,
          words: this.addWelcomeData.words,
          mediumId: this.mediumId,
          greetingId: Number(this.greetingId)
        }
        this.btnLoading = true
        upDateGreeting(params).then(res => {
          this.btnLoading = false
          this.$router.push('/greeting/index')
        }).catch(res => {
          this.btnLoading = false
        })
      } else {
        const params = {
          rangeType: this.addWelcomeData.rangeType,
          employees: this.employeeIdList,
          type: type,
          words: this.addWelcomeData.words,
          mediumId: this.mediumId
        }
        this.btnLoading = true
        greetingStore(params).then(res => {
          this.btnLoading = false
          this.$router.push('/greeting/index')
        }).catch(res => {
          this.btnLoading = false
        })
      }
    }
  }
}
</script>

<style lang="less" scoped>
  .wrapper {
    display: flex;
    .left {
      width: 28%;
      height: 800px;
      margin-right: 6px;
      .dialogue-box {
        width: 100%;
        padding: 10px;
        display: flex;
        .portrait {
          .user {
            color: #1890ff;
            font-size: 40px;
          }
        }
        .content {
          margin-left: 20px;
          width: 75%;
          background: #f3f6fb;
          padding: 10px;
          border-radius: 4px 5px 5px 0px;
          word-wrap: break-word;
        }
        .img-box {
          margin-left: 20px;
          width: 200px;
          img {
            width: 100%;
            height: auto;
          }
        }
        .text-box {
          margin-left: 20px;
          width: 300px;
          height: 130px;
          display: flex;
          flex-direction: column;
          padding: 10px 15px;
          border:1px solid #eee;
          border-radius: 4px;
          background: #fff;
          h4 {
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 18px;
            margin: 0;
          }
          div {
            margin-top:5px;
            width: 100%;
            height: 80px;
            display: flex;
            justify-content: space-between;
            img {
              width: 80px;
              height: 80px;
            }
          }
        }
        .applets-box {
          margin-left: 20px;
          width: 200px;
          border: 1px solid #eee;
          border-radius: 4px;
          background: #fff;
          padding: 10px;
          h4 {
            width: 100%;
            height: 40px;
            line-height: 40px;
          }
          img {
            width: 100%;
            height: auto;
          }
        }
      }
    }
    .right {
      width: 70%;
      .medium-btn {
        display: flex;
        align-items: center;
        margin-left: 50px;
        span {
          margin: 0 10px;
        }
      }
    }
  }
  .pbox {
    .picture-box {
      width: 100%;
      padding: 10px;
      display: flex;
      flex-wrap: wrap;
      div {
        width: 150px;
        height: 150px;
        border-radius: 4px;
        margin: 5px;
        position: relative;
        img {
          width: 100%;
          height: auto;
          max-height: 100%;
          border-radius: 4px;
        }
        span {
          position: absolute;
          bottom: 0;
          left: 0;
          width: 100%;
          height: 35px;
          background: #000;
          opacity: 0.4;
          color: #fff;
          line-height: 35px;
          padding: 0 5px;
          border-radius:0 0 4px 4px;
        }
      }
      .active {
        width: 150px;
        height: 150px;
        border-radius: 4px;
        margin: 5px;
        position: relative;
        border: 2px solid blue;
        img {
          width: 100%;
          height: auto;
          max-height: 100%;
          border-radius: 4px;
        }
        span {
          position: absolute;
          bottom: 0;
          left: 0;
          width: 100%;
          height: 35px;
          background: #000;
          opacity: 0.4;
          color: #fff;
          line-height: 35px;
          padding: 0 5px;
          border-radius:0 0 4px 4px;
        }
      }
      .nothing {
        margin: 20px auto;
      }
    }
  }
  .tbox {
    .up-box {
      width: 90px;
      height: 90px;
      border: 1px dashed #ccc;
      text-align: center;
      line-height: 80px;
      font-size: 50px;
      font-weight: 800px;
    }
    .img-box {
      display: flex;
      align-items: flex-end;
      .imgs {
        width: 90px;
        height: 90px;
        border: 1px dashed #ccc;
        line-height: 90px;
        img {
          width: 100%;
          max-height: 100%
        }
      }
    }
    .select-box {
      width: 200px;
    }
    .import-box {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      .anticon {
        font-size: 35px;
        margin-right: 5px;
      }
    }
  }
  .applets-box {
      .left {
        .msg-box {
          width: 85%;
          border: 1px solid #FFE1B6;
          background: #FFF1DE;
          padding: 10px;
          margin-bottom: 20px;
        }
      }
    }
</style>
