<template>
  <div class="material-library">
    <a-row>
      <a-col>
        <a-modal
          title="新增分组"
          :visible="visible"
          @cancel="() => {this.visible = false; this.groupName = '';}"
        >
          <a-input v-model="groupName" :maxLength="15" placeholder="请输入分组名（不得超过15个字符）"></a-input>
          <template slot="footer">
            <a-button key="back" @click="() => {this.visible = false; this.groupName = '';}">
              取消
            </a-button>
            <a-button :loading="btnLoading" key="submit" @click="addGroup" type="primary">
              确定
            </a-button>
          </template>
        </a-modal>
        <a-modal
          title="修改分组"
          :visible="editGroupModal"
          @cancel="() => {this.editGroupModal = false; this.editGroupId = ''; this.editGroupName = ''}"
        >
          <a-form-model :label-col="{ span: 5 }" :wrapper-col="{ span: 14 }">
            <a-form-model-item label="选择分组">
              <a-select v-model="editGroupId">
                <a-select-option v-for="item in groupList" :key="item.id" :disabled="item.id == 0">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
            <a-form-model-item label="修改分组名称">
              <a-input v-model="editGroupName" :maxLength="15" placeholder="请输入分组名（不得超过15个字符）"></a-input>
            </a-form-model-item>
          </a-form-model>
          <template slot="footer">
            <a-button key="back" @click="() => {this.editGroupModal = false; this.editGroupId = ''; this.editGroupName = ''}">
              取消
            </a-button>
            <a-button key="submit" @click="editGroupDefine" type="primary">
              确定
            </a-button>
          </template>
        </a-modal>
        <a-card class="tag-box">
          <a-form-model :label-col="{ span: 2 }" :wrapper-col="{ span: 5 }">
            <a-form-model-item label="选择分组">
              <a-select v-model="changeGroupId" @change="clickGroup">
                <a-select-option v-for="item in groupList" :key="item.id">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-form-model>
          <div class="group-btn">
            <a-button v-permission="'/mediumGroup/index@editGroup'" type="primary" @click="() => {this.editGroupModal = true}" style="marginRight: 10px">修改分组</a-button>
            <a-button v-permission="'/mediumGroup/index@add'" type="primary" @click="() => {this.visible = true}">新增分组</a-button>
          </div>
          <a-tabs @change="changeTab">
            <a-tab-pane :key="0" tab="所有">
              <p>共有{{ pagination.total }}个素材</p>
              <a-row>
                <a-col :span="6">
                  <a-input-search
                    placeholder="输入要搜索的内容"
                    v-model="searchValue"
                    enter-button="搜索"
                    @search="onSearch"
                  />
                </a-col>
                <a-col :span="1">
                  <a-button @click="() => { this.searchValue = '' }">清空</a-button>
                </a-col>
              </a-row>
              <a-table
                style="marginTop:20px;"
                :columns="allColumns"
                :data-source="allTable"
                :rowKey="record => record.id"
                :pagination="pagination"
                @change="handleTableChange">
                <div slot="titles" slot-scope="text, record">
                  <span v-if="record.type === '文本' || record.type === '图文' || record.type === '小程序'">{{ record.content.title }}</span>
                  <span v-if="record.type === '图片'">{{ record.content.imageName }}</span>
                  <span v-if="record.type === '文件'">{{ record.content.fileName }}</span>
                  <span v-if="record.type === '音频'">{{ record.content.voiceName }}</span>
                  <span v-if="record.type === '视频'">{{ record.content.videoName }}</span>
                </div>
                <div slot="contents" slot-scope="text, record">
                  <span v-if="record.type === '文本'">
                    {{ record.content.content }}
                  </span>
                  <img style="height: 80px; width: 80px;" v-if="record.type === '图片'" :src="record.content.imageFullPath"/>
                  <img style="height: 80px; width: 80px;" v-if="record.type === '图文'" :src="record.content.imageFullPath"/>
                  <img style="height: 80px; width: 80px;" v-if="record.type === '小程序'" :src="record.content.imageFullPath"/>
                </div>
                <div slot="action" slot-scope="text, record">
                  <template>
                    <a-button type="link" v-if="record.type == '文本' || record.type == '图文' || record.type == '小程序'" @click="edit(record.id, record.type)">编辑</a-button>
                    <a-button type="link" @click="moveTagModal(record.id)">移动</a-button>
                    <a-popconfirm
                      title="确认删除该素材？"
                      ok-text="确认"
                      cancel-text="取消"
                      @confirm="delMaterial(record.id)"
                    >
                      <a-button type="link">删除</a-button>
                    </a-popconfirm>
                  </template>
                </div>
              </a-table>
            </a-tab-pane>
            <a-tab-pane key="1" tab="文本">
              <p>共有{{ pagination.total }}个素材</p>
              <a-row>
                <a-col :span="6">
                  <a-input-search
                    placeholder="输入要搜索的标题或内容"
                    v-model="searchValue"
                    enter-button="搜索"
                    @search="onSearch"
                  />
                </a-col>
                <a-col :span="1">
                  <a-button @click="() => { this.searchValue = '' }">清空</a-button>
                </a-col>
                <a-col :span="2" :offset="12">
                  <a-button type="primary" @click="() => {this.textModal = true}">+添加文本</a-button>
                </a-col>
              </a-row>
              <a-table
                style="marginTop:20px;"
                :columns="allColumns"
                :data-source="textTablel"
                :rowKey="record => record.id"
                :row-selection="{ onChange: onSelectAllChange }"
                :pagination="pagination"
                @change="handleTableChange">
                <div slot="titles" slot-scope="text, record">
                  {{ record.content.title }}
                </div>
                <div slot="contents" slot-scope="text, record">
                  {{ record.content.content }}
                </div>
                <div slot="action" slot-scope="text, record">
                  <template>
                    <a-button type="link" @click="editDetail(record.id, 1)">编辑</a-button>
                    <a-button type="link" @click="moveTagModal(record.id)">移动</a-button>
                    <a-popconfirm
                      title="确认删除该素材？"
                      ok-text="确认"
                      cancel-text="取消"
                      @confirm="delMaterial(record.id)"
                    >
                      <a-button type="link">删除</a-button>
                    </a-popconfirm>
                  </template>
                </div>
              </a-table>
            </a-tab-pane>
            <a-tab-pane key="2" tab="图片">
              <p>共有{{ pagination.total }}个素材</p>
              <div class="top-btn">
                <div class="search">
                  <a-input-search
                    placeholder="输入要搜索的内容"
                    v-model="searchValue"
                    enter-button="搜索"
                    @search="onSearch"
                  />
                  <a-button @click="() => { this.searchValue = '' }">清空</a-button>
                </div>
                <div class="btn">
                  <a-button type="primary" style="marginRight: 10px" @click="modalTypeDefine(2)"><a-icon type="upload" />上传图片</a-button>
                </div>
              </div>
              <div class="picture">
                <a-card style="width: 250px" v-for="item in pictureTableList" :key="item.id">
                  <div class="time">
                    <p>{{ item.createdAt }}</p>
                    <a-checkbox></a-checkbox>
                  </div>
                  <div class="img-box">
                    <span>{{ item.content.imageName }}</span>
                    <img
                      :src="item.content.imageFullPath"
                    />
                  </div>
                  <div class="text-box">
                    <span>上传者： {{ item.userName }}</span>
                    <span>来源：{{ item.mediumGroupName }}</span>
                  </div>
                  <template slot="actions" class="ant-card-actions">
                    <a-button type="link" @click="moveTagModal(item.id)">移动</a-button>
                    <a-popconfirm
                      title="确认删除该素材？"
                      ok-text="确认"
                      cancel-text="取消"
                      @confirm="delMaterial(item.id)"
                    >
                      <a-button type="link">删除</a-button>
                    </a-popconfirm>
                  </template>
                </a-card>
              </div>
            </a-tab-pane>
            <a-tab-pane key="3" tab="图文">
              <p>共有{{ pagination.total }}个素材</p>
              <div class="top-btn">
                <div class="search">
                  <a-input-search
                    placeholder="输入要搜索的内容"
                    v-model="searchValue"
                    enter-button="搜索"
                    @search="onSearch"
                  />
                  <a-button @click="() => { this.searchValue = '' }">清空</a-button>
                </div>
                <div class="btn">
                  <a-button type="primary" style="marginRight: 10px" @click="() => { this.imageTextModal = true; this.modalType = 3 }"><a-icon type="plus" />添加图文</a-button>
                </div>
              </div>
              <div class="picture">
                <a-card style="width: 250px" v-for="item in pictrueTextList" :key="item.id">
                  <div class="time">
                    <p>{{ item.createdAt }}</p>
                    <a-checkbox></a-checkbox>
                  </div>
                  <div class="img-box">
                    <span>{{ item.content.title }}</span>
                    <img
                      :src="item.content.imageFullPath"
                    />
                  </div>
                  <div class="text-box">
                    <span>上传者： {{ item.userName }}</span>
                    <span>来源：{{ item.mediumGroupName }}</span>
                  </div>

                  <template slot="actions" class="ant-card-actions">
                    <a-button v-permission="'/mediumGroup/index@edit'" type="link" @click="editDetail(item.id, 3)">编辑</a-button>
                    <a-button v-permission="'/mediumGroup/index@move'" type="link" @click="moveTagModal(item.id)">移动</a-button>
                    <a-popconfirm
                      title="确认删除该素材？"
                      ok-text="确认"
                      cancel-text="取消"
                      @confirm="delMaterial(item.id)"
                    >
                      <a-button v-permission="'/mediumGroup/index@delete'" type="link">删除</a-button>
                    </a-popconfirm>
                  </template>

                </a-card>
              </div>
            </a-tab-pane>
            <a-tab-pane key="4" tab="音频">
              <p>共有{{ pagination.total }}个素材</p>
              <div class="top-btn">
                <div class="search">
                  <a-input-search
                    placeholder="输入要搜索的内容"
                    v-model="searchValue"
                    enter-button="搜索"
                    @search="onSearch"
                  />
                  <a-button @click="() => { this.searchValue = '' }">清空</a-button>
                </div>
                <div class="btn">
                  <a-button type="primary" style="marginRight: 10px" @click="modalTypeDefine(4)"><a-icon type="upload" />上传音频</a-button>
                </div>
              </div>
              <div class="picture">
                <a-card style="width: 250px" v-for="item in voiceTableList" :key="item.id">
                  <div class="time">
                    <p>{{ item.createdAt }}</p>
                    <a-checkbox></a-checkbox>
                  </div>
                  <a-icon type="customer-service" theme="twoTone" style="fontSize: 40px;" />
                  <div class="text-box">
                    <span>上传者： {{ item.userName }}</span>
                    <span>来源：{{ item.mediumGroupName }}</span>
                  </div>
                  <template slot="actions" class="ant-card-actions">
                    <a-button type="link" @click="moveTagModal(item.id)">移动</a-button>
                    <a-popconfirm
                      title="确认删除该素材？"
                      ok-text="确认"
                      cancel-text="取消"
                      @confirm="delMaterial(item.id)"
                    >
                      <a-button type="link">删除</a-button>
                    </a-popconfirm>
                  </template>
                </a-card>
              </div>
            </a-tab-pane>
            <a-tab-pane key="5" tab="视频">
              <p>共有{{ pagination.total }}个素材</p>
              <div class="top-btn">
                <div class="search">
                  <a-input-search
                    placeholder="输入要搜索的内容"
                    v-model="searchValue"
                    enter-button="搜索"
                    @search="onSearch"
                  />
                  <a-button @click="() => { this.searchValue = '' }">清空</a-button>
                </div>
                <div class="btn">
                  <a-button type="primary" style="marginRight: 10px" @click="modalTypeDefine(5)"><a-icon type="upload" />上传视频</a-button>
                </div>
              </div>
              <div class="picture">
                <a-card style="width: 250px" v-for="item in videoList" :key="item.id">
                  <div class="time">
                    <p>{{ item.createdAt }}</p>
                    <a-checkbox></a-checkbox>
                  </div>
                  <div class="video-box">
                    <video :src="item.content.videoFullPath" style="width: 100%" controls></video>
                  </div>
                  <div class="text-box">
                    <span>上传者： {{ item.userName }}</span>
                    <span>来源：{{ item.mediumGroupName }}</span>
                  </div>
                  <template slot="actions" class="ant-card-actions">
                    <a-button type="link" @click="moveTagModal(item.id)">移动</a-button>
                    <a-popconfirm
                      title="确认删除该素材？"
                      ok-text="确认"
                      cancel-text="取消"
                      @confirm="delMaterial(item.id)"
                    >
                      <a-button type="link">删除</a-button>
                    </a-popconfirm>
                  </template>
                </a-card>
              </div>
            </a-tab-pane>
            <a-tab-pane key="6" tab="小程序">
              <p>共有{{ pagination.total }}个素材</p>
              <div class="top-btn">
                <div class="search">
                  <a-input-search
                    placeholder="输入要搜索的内容"
                    v-model="searchValue"
                    enter-button="搜索"
                    @search="onSearch"
                  />
                  <a-button @click="() => { this.searchValue = '' }">清空</a-button>
                </div>
                <div class="btn">
                  <a-button type="primary" style="marginRight: 10px" @click="() => { this.appletsModal = true; this.modalType = 6 }"><a-icon type="plus" />添加小程序</a-button>
                </div>
              </div>
              <div class="picture">
                <a-card style="width: 250px" v-for="item in appletsTableList" :key="item.id">
                  <div class="time">
                    <p>{{ item.createdAt }}</p>
                    <a-checkbox></a-checkbox>
                  </div>
                  <div class="img-box">
                    <span>{{ item.content.title }}</span>
                    <img :src="item.content.imageFullPath">
                  </div>
                  <div class="text-box">
                    <span>上传者： {{ item.userName }}</span>
                    <span>来源：{{ item.mediumGroupName }}</span>
                  </div>
                  <template slot="actions" class="ant-card-actions">
                    <a-button type="link" @click="editDetail(item.id, 6)">编辑</a-button>
                    <a-button type="link" @click="moveTagModal(item.id)">移动</a-button>
                    <a-popconfirm
                      title="确认删除该素材？"
                      ok-text="确认"
                      cancel-text="取消"
                      @confirm="delMaterial(item.id)"
                    >
                      <a-button type="link">删除</a-button>
                    </a-popconfirm>
                  </template>
                </a-card>
              </div>
            </a-tab-pane>
            <a-tab-pane key="7" tab="文件">
              <p>共有{{ pagination.total }}个素材</p>
              <div class="top-btn">
                <div class="search">
                  <a-input-search
                    placeholder="输入要搜索的内容"
                    v-model="searchValue"
                    enter-button="搜索"
                    @search="onSearch"
                  />
                  <a-button @click="() => { this.searchValue = '' }">清空</a-button>
                </div>
                <div class="btn">
                  <a-button type="primary" style="marginRight: 10px" @click="modalTypeDefine(7)"><a-icon type="upload" />上传文件</a-button>
                </div>
              </div>
              <div class="picture">
                <a-card style="width: 250px" v-for="item in fileTableList" :key="item.id">
                  <div class="time">
                    <p>{{ item.createdAt }}</p>
                    <a-checkbox></a-checkbox>
                  </div>
                  <div class="file-box">
                    <a-icon type="file" theme="twoTone" style="fontSize: 46px;"/>
                    <span>{{ item.content.fileName }}</span>
                  </div>
                  <div class="text-box">
                    <span>上传者： {{ item.userName }}</span>
                    <span>来源：{{ item.mediumGroupName }}</span>
                  </div>
                  <template slot="actions" class="ant-card-actions">
                    <a-button type="link" @click="moveTagModal(item.id)">移动</a-button>
                    <a-popconfirm
                      title="确认删除该素材？"
                      ok-text="确认"
                      cancel-text="取消"
                      @confirm="delMaterial(item.id)"
                    >
                      <a-button type="link">删除</a-button>
                    </a-popconfirm>
                  </template>
                </a-card>
              </div>
            </a-tab-pane>
          </a-tabs>
          <a-modal
            :visible="textModal"
            title="新建文本素材"
            @cancel="() => {this.textModal = false; this.addTextData.content.title = ''; this.addTextData.content.content = ''; this.materialGroupId = 0 }">
            <a-form-model ref="textForm" :model="addTextData.content" :rules="textRules" :label-col="{ span: 5 }" :wrapper-col="{ span: 15 }">
              <a-form-model-item label="选择分组：">
                <a-select v-model="materialGroupId">
                  <a-select-option v-for="item in groupList" :key="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-form-model-item label="文本标题：" prop="title">
                <a-input v-model="addTextData.content.title" />
              </a-form-model-item>
              <a-form-model-item label="文本内容：" prop="content">
                <a-textarea v-model="addTextData.content.content"></a-textarea>
              </a-form-model-item>
            </a-form-model>
            <template slot="footer">
              <a-button @click="() => {this.textModal = false; this.addTextData.content.title = ''; this.addTextData.content.content = ''; this.materialGroupId = 0 }">取消</a-button>
              <a-button type="primary" @click="addText">确定</a-button>
            </template>
          </a-modal>
          <a-modal
            :visible="editTextModal"
            title="编辑文本"
            @cancel="() => {this.editTextModal = false}">
            <a-form-model :label-col="{ span: 5 }" :wrapper-col="{ span: 15 }">
              <a-form-model-item label="选择分组：">
                <a-select v-model="materialDetail.mediumGroupId">
                  <a-select-option v-for="item in groupList" :key="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-form-model-item label="文本标题：">
                <a-input v-model="materialDetail.content.title" />
              </a-form-model-item>
              <a-form-model-item label="文本内容：">
                <a-textarea v-model="materialDetail.content.content"></a-textarea>
              </a-form-model-item>
            </a-form-model>
            <template slot="footer">
              <a-button @click="() => {this.editTextModal = false;}">取消</a-button>
              <a-button type="primary" @click="addText">确定</a-button>
            </template>
          </a-modal>
          <a-modal
            :visible="imageTextModal"
            title="新建图文"
            @cancel="resetTextImg('取消')">
            <a-form-model ref="imageForm" :model="imageTextData" :rules="imageForm" :label-col="{ span: 5 }" :wrapper-col="{ span: 17 }">
              <a-form-model-item label="选择分组：">
                <a-select v-model="materialGroupId">
                  <a-select-option v-for="item in groupList" :key="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
              <a-form-model-item label="图片封面：">
                <upload
                  v-if="imgUrl === ''"
                  :imageUrl="imgUrl"
                  @success="uploadSuccess"
                  :file-type="1"></upload>
                <div v-else>
                  <img :src="imgUrl" style="width: 80px; height:80px;"/>
                </div>
                <span>图片大小不超过2M，支持JPG、JPEG及PNG格式</span>
              </a-form-model-item>
              <a-form-model-item label="填写标题：" prop="title">
                <a-input v-model="imageTextData.title" />
              </a-form-model-item>
              <a-form-model-item label="添加描述：">
                <a-textarea v-model="imageTextData.description"></a-textarea>
              </a-form-model-item>
              <a-form-model-item label="点击跳转：" prop="imageLink">
                <a-input v-model="imageTextData.imageLink" placeholder="请输入跳转链接，且必须以http://或https://开头"/>
                <a-button @click="resetTextImg('重置')">重置</a-button>
              </a-form-model-item>
            </a-form-model>
            <template slot="footer">
              <a-button @click="resetTextImg('取消')">取消</a-button>
              <a-button type="primary" @click="addImageTextDefine">确定</a-button>
            </template>
          </a-modal>
          <a-modal
            :visible="editImageTextModal"
            title="编辑图文"
            @cancel="() => {this.editImageTextModal = false; this.imgUrl = ''; this.upLoadRes = {}}">
            <a-form :label-col="{ span: 5 }" :wrapper-col="{ span: 17 }">
              <a-form-item label="选择分组：">
                <a-select v-model="materialDetail.mediumGroupId">
                  <a-select-option v-for="item in groupList" :key="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="图片封面：">
                <div>
                  <img
                    style="height:80px; width: 80px;"
                    :src="imgUrl !== '' ? imgUrl : materialDetail.content.imageFullPath"/>
                </div>
                <upload
                  :imageUrl="imgUrl"
                  @success="uploadSuccess"
                  :btnType="false"
                  :file-type="1"></upload>
                <span>图片大小不超过2M，支持JPG、JPEG及PNG格式</span>
              </a-form-item>
              <a-form-item label="填写标题：">
                <a-input v-model="materialDetail.content.title" />
              </a-form-item>
              <a-form-item label="添加描述：">
                <a-textarea v-model="materialDetail.content.description"></a-textarea>
              </a-form-item>
              <a-form-item label="点击跳转：">
                <a-input v-model="materialDetail.content.imageLink"/>
              </a-form-item>
            </a-form>
            <template slot="footer">
              <a-button @click="() => {this.editImageTextModal = false; this.imgUrl = ''; this.upLoadRes = {}}">取消</a-button>
              <a-button type="primary" @click="addImageTextDefine">确定</a-button>
            </template>
          </a-modal>
          <a-modal
            :visible="pictureModal"
            :title="allTitle"
            @cancel="() => {this.pictureModal = false; this.materialGroupId = 0; this.uploadDefine = false}">
            <a-form v-if="modalType === 2" :label-col="{ span: 5 }" :wrapper-col="{ span: 15 }">
              <a-form-item label="选择分组：">
                <a-select v-model="materialGroupId">
                  <a-select-option v-for="item in groupList" :key="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="上传图片">
                <upload
                  :imageUrl="imgUrl"
                  @success="uploadSuccess"
                  :file-type="1"></upload>
                <p>(图片大小不超过2M，图片名不能重复，支持JPG，JPEG及PNG格式)</p>
              </a-form-item>
            </a-form>
            <a-form v-if="modalType === 4" :label-col="{ span: 5 }" :wrapper-col="{ span: 15 }">
              <a-form-item label="选择分组：">
                <a-select v-model="materialGroupId">
                  <a-select-option v-for="item in groupList" :key="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="上传音频：">
                <vpload
                  @successDefine="uploadSuccessV"
                  :file-type="2"></vpload>
                <div>(音频上传大小不超过2MB，播放长度不超过60s,支持AMR,MP3格式。)</div>
                <div v-if="uploadDefine" style="color: red">上传成功</div>
              </a-form-item>
            </a-form>
            <a-form v-if="modalType === 5" :label-col="{ span: 5 }" :wrapper-col="{ span: 15 }">
              <a-form-item label="选择分组：">
                <a-select v-model="materialGroupId">
                  <a-select-option v-for="item in groupList" :key="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="上传视频：">
                <vpload
                  @successDefine="uploadSuccessV"
                  :file-type="3"></vpload>
                <div>(视频上传大小不超过10M，支持MP4格式)</div>
                <div v-if="uploadDefine" style="color: red">上传成功</div>
              </a-form-item>
            </a-form>
            <a-form v-if="modalType === 7" :label-col="{ span: 5 }" :wrapper-col="{ span: 15 }">
              <a-form-item label="选择分组：">
                <a-select v-model="materialGroupId">
                  <a-select-option v-for="item in groupList" :key="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="上传文件：">
                <vpload
                  @successDefine="uploadSuccessV"
                  :file-type="4"></vpload>
                <div v-if="uploadDefine" style="color: red">上传成功</div>
                <div>(上传文件大小不超过20MB，支持DOC、DOCX、XLS、XLSX、CSV、PPT、PPTX、TXT、PDF及Xmind格式。)</div>
              </a-form-item>
            </a-form>
            <template slot="footer">
              <a-button @click="() => { this.pictureModal = false; this.imgUrl = ''; this.upLoadRes = {}; this.materialGroupId = 0; this.uploadDefine = false }">取消</a-button>
              <a-button type="primary" @click="defineModelType">确定</a-button>
            </template>
          </a-modal>
          <div class="jbox" ref="jbox">
            <a-modal
              width="850px"
              :visible="appletsModal"
              title="新增小程序"
              :getContainer="() => $refs.jbox"
              @cancel="resetApplets">
              <div class="applets-box">
                <div class="left">
                  <div class="msg-box">
                    在企业微信里发送小程序（必须在微信公众平台通过审核和发布的），请先将其关联到企业微信，再到本系统添加该小程序，否则发送失败，客户接收不到。如果没有微信小程序，请前往微信小程序进行注册。<a href="https://mp.weixin.qq.com/cgi-bin/wx" target="_blank"><a-button type="link">立即前往</a-button></a>
                  </div>
                  <a-form-model ref="appletsForm" :model="appletsData" :rules="appletsForm" :label-col="{ span: 5 }" :wrapper-col="{ span: 15 }">
                    <a-form-model-item label="选择分组">
                      <a-select v-model="materialGroupId">
                        <a-select-option v-for="item in groupList" :key="item.id">
                          {{ item.name }}
                        </a-select-option>
                      </a-select>
                    </a-form-model-item>
                    <a-form-model-item label="小程序appID：" prop="appid">
                      <a-input v-model="appletsData.appid" />
                    </a-form-model-item>
                    <a-form-model-item label="小程序路径：" prop="page">
                      <a-input v-model="appletsData.page" />
                    </a-form-model-item>
                    <a-form-model-item label="卡片标题：" prop="title">
                      <a-input v-model="appletsData.title" />
                    </a-form-model-item>
                    <a-form-model-item label="卡片图片：">
                      <p>上传图片不超过1M，尺寸必须为1080*864像素，支持jpg/jpeg/png格式</p>
                      <upload
                        :imageUrl="imgUrl"
                        @success="uploadSuccess"
                        :file-type="1"></upload>
                    </a-form-model-item>
                  </a-form-model>
                </div>
                <div class="right">
                  <img src="@/assets/applets.png"/>
                  <span>示例</span>
                </div>
              </div>
              <template slot="footer">
                <a-button @click="resetApplets">取消</a-button>
                <a-button type="primary" @click="appletsDefine">确定</a-button>
              </template>
            </a-modal>
            <a-modal
              width="850px"
              :visible="editAppletsModal"
              title="编辑小程序"
              :getContainer="() => $refs.jbox"
              @cancel="resetApplets">
              <div class="applets-box">
                <div class="left">
                  <div class="msg-box">
                    在企业微信里发送小程序（必须在微信公众平台通过审核和发布的），请先将其关联到企业微信，再到本系统添加该小程序，否则发送失败，客户接收不到。如果没有微信小程序，请前往微信小程序进行注册。<a href="https://mp.weixin.qq.com/cgi-bin/wx" target="_blank"><a-button type="link">立即前往</a-button></a>
                  </div>
                  <a-form :label-col="{ span: 5 }" :wrapper-col="{ span: 15 }">
                    <a-form-item label="选择分组">
                      <a-select v-model="materialDetail.mediumGroupId">
                        <a-select-option v-for="item in groupList" :key="item.id">
                          {{ item.name }}
                        </a-select-option>
                      </a-select>
                    </a-form-item>
                    <a-form-item label="小程序appID：">
                      <a-input disabled v-model="materialDetail.content.appid" />
                    </a-form-item>
                    <a-form-item label="小程序路径：">
                      <a-input v-model="materialDetail.content.page" />
                    </a-form-item>
                    <a-form-item label="卡片标题：">
                      <a-input v-model="materialDetail.content.title" />
                    </a-form-item>
                    <a-form-item label="卡片图片：">
                      <p>上传图片不超过1M，尺寸必须为1080*864像素，支持jpg/jpeg/png格式</p>
                      <div>
                        <img
                          style="height:80px; width: 80px;"
                          :src="imgUrl !== '' ? imgUrl : materialDetail.content.imageFullPath"/>
                      </div>
                      <upload
                        :imageUrl="imgUrl"
                        @success="uploadSuccess"
                        :btnType="false"
                        :file-type="1"></upload>
                    </a-form-item>
                  </a-form>
                </div>
                <div class="right">
                  <img src="@/assets/applets.png"/>
                  <span>示例</span>
                </div>
              </div>
              <template slot="footer">
                <a-button @click="resetApplets">取消</a-button>
                <a-button type="primary" @click="appletsDefine">确定</a-button>
              </template>
            </a-modal>
          </div>
          <div class="bbox" ref="bbox">
            <a-modal
              :getContainer="() => $refs.bbox"
              class="modal"
              :visible="moveModal"
              @cancel="() => {this.moveModal = false}"
              title="选择分组">
              <a-select v-model="currentId" style="width: 200px;marginBottom: 20px;" @change="changeGroupMove">
                <a-select-option v-for="item in groupList" :key="item.id">
                  {{ item.name }}
                </a-select-option>
              </a-select>
              <div class="tag-box">
                <span :class="classActive === item.id ? 'active' : 'box'" v-for="item in groupList" :key="item.id" @click="moveTag(item.id)">{{ item.name }}</span>
              </div>
              <template slot="footer">
                <a-button @click="() => {this.moveModal = false}">取消</a-button>
                <a-button type="primary" @click="moveDefine">确定</a-button>
              </template>
            </a-modal>
          </div>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script>
import storage from 'store'
import { materialLibraryList, mediumGroup, addMediumGroup, editMediumGroup, delMediumGroup, delMaterialLibrary, addMaterialLibrary, moveGroup, getMaterialLibrary, editMaterialLibrary } from '@/api/mediumGroup'
import upload from './components/upload'
import vpload from './components/vpload'
export default {
  components: {
    upload,
    vpload
  },
  data () {
    return {
      btnLoading: false,
      searchValue: '',
      visible: false,
      textVisible: false,
      editGroupModal: false,
      textModal: false,
      pictureModal: false,
      imageTextModal: false,
      moveModal: false,
      upLoadPictureModal: false,
      appletsModal: false,
      changeGroupId: '',
      allColumns: [
        {
          title: '标题',
          dataIndex: 'titles',
          align: 'center',
          scopedSlots: { customRender: 'titles' }
        },
        {
          title: '内容',
          dataIndex: 'contents',
          align: 'center',
          scopedSlots: { customRender: 'contents' }
        },
        {
          title: '上传者',
          dataIndex: 'userName',
          align: 'center'
        },
        {
          title: '素材来源',
          dataIndex: 'mediumGroupName',
          align: 'center'
        },
        {
          title: '类型',
          dataIndex: 'type',
          align: 'center'
        },
        {
          title: '添加时间',
          dataIndex: 'createdAt',
          align: 'center'
        },
        {
          title: '操作',
          dataIndex: 'action',
          align: 'center',
          scopedSlots: { customRender: 'action' }
        }
      ],
      allTable: [],
      textTablel: [],
      pictureTableList: [],
      voiceTableList: [],
      pictrueTextList: [],
      videoList: [],
      fileTableList: [],
      appletsTableList: [],
      // 分组列表
      groupList: [],
      // 新增分组名称
      groupName: '',
      // 编辑分组Id
      editGroupId: '',
      editGroupName: '',
      tabsNumber: 0,
      allTypeList: [
        {
          label: '文本',
          value: 1
        },
        {
          label: '图片',
          value: 2
        },
        {
          label: '图文',
          value: 3
        },
        {
          label: '音频',
          value: 4
        },
        {
          label: '视频',
          value: 5
        },
        {
          label: '小程序',
          value: 6
        },
        {
          label: '文件',
          value: 7
        }
      ],
      modalType: 0,
      modelTitle: '',
      // 新增文本数据
      addTextData: {
        content: {
          title: '',
          content: ''
        }
      },
      fileObj: {},
      classActive: 0,
      currentId: null,
      moveId: null,
      moveData: {},
      upLoadRes: {},
      materialGroupId: 0,
      picTextModalData: [],
      imgSearchStr: '',
      imgActive: 0,
      imgPath: '',
      // 添加图文数据
      imageTextData: {},
      appletsData: {},
      // 编辑
      materialId: '',
      materialDetail: {
        content: {
          title: '',
          content: ''
        }
      },
      editTypeNum: '',
      editTextModal: false,
      editImageTextModal: false,
      editModal: false,
      editAppletsModal: false,
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      allTitle: '',
      imgUrl: '',
      textRules: {
        title: [
          { required: true, message: '请输入文本标题', trigger: 'blur' }
        ],
        content: [{ required: true, message: '请输入文本内容', trigger: 'blur' }]
      },
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
      uploadDefine: false
    }
  },
  created () {
    this.getGroupList()
    this.getTableData()
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
  methods: {
    // 获取表格数据
    getTableData () {
      const params = {
        mediumGroupId: this.changeGroupId,
        type: Number(this.tabsNumber),
        searchStr: this.searchValue,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      materialLibraryList(params).then(res => {
        this.pagination.total = res.data.page.total
        if (this.tabsNumber === 0) {
          this.allTable = res.data.list
        } else if (this.tabsNumber === '1') {
          this.textTablel = res.data.list
        } else if (this.tabsNumber === '2') {
          this.pictureTableList = res.data.list
        } else if (this.tabsNumber === '4') {
          this.voiceTableList = res.data.list
        } else if (this.tabsNumber === '3') {
          this.pictrueTextList = res.data.list
        } else if (this.tabsNumber === '5') {
          this.videoList = res.data.list
        } else if (this.tabsNumber === '7') {
          this.fileTableList = res.data.list
        } else if (this.tabsNumber === '6') {
          this.appletsTableList = res.data.list
        }
      })
    },
    // 获取分组列表
    getGroupList () {
      mediumGroup().then(res => {
        this.groupList = res.data
        this.changeGroupId = 0
      })
    },
    // 获取素材详情
    getMaterialDetail () {
      getMaterialLibrary({
        id: this.materialId
      }).then(res => {
        this.materialDetail = res.data
      })
    },
    // 新增分组
    addGroup () {
      if (this.groupName.trim() == '') {
        this.$message.error('请输入分组名称')
        return
      }
      this.btnLoading = true
      addMediumGroup({
        name: this.groupName
      }).then(res => {
        this.btnLoading = false
        this.visible = false
        this.groupName = ''
        this.getGroupList()
      }).catch(res => {
        this.btnLoading = false
      })
    },
    // 修改分组
    editGroup (id, name) {
      this.editGroupModal = true
      this.editGroupId = id
      this.editGroupName = name
    },
    editGroupDefine () {
      if (this.editGroupName.trim() == '') {
        this.$message.error('请输入修改分组名称')
        return
      }
      editMediumGroup({
        id: this.editGroupId,
        name: this.editGroupName
      }).then(res => {
        this.editGroupModal = false
        this.editGroupId = ''
        this.editGroupName = ''
        this.getGroupList()
      })
    },
    // 删除分组
    delGroup (id) {
      delMediumGroup({
        id: id
      }).then(res => {
        this.getGroupList()
      })
    },
    // 点击分组切换
    clickGroup (value) {
      this.changeGroupId = value
      this.getTableData()
      this.pagination.current = 1
      this.searchValue = ''
    },
    // tab 切换
    changeTab (value) {
      this.tabsNumber = value
      this.pagination.current = 1
      this.searchValue = ''
      this.getTableData()
    },
    // 选中所有分组
    onSelectAllChange () {
    },
    // 筛选
    onSearch (value) {
      this.searchValue = value
      this.getTableData()
    },
    // 所有编辑
    edit (id, type) {
      this.materialId = id
      this.upLoadRes = {}
      this.getMaterialDetail()
      if (type === '文本') {
        this.editTypeNum = 1
        this.editTextModal = true
      } else if (type === '图文') {
        this.editTypeNum = 3
        this.editImageTextModal = true
        this.modalType = 3
      } else if (type === '小程序') {
        this.editTypeNum = 6
        this.modalType = 6
        this.editAppletsModal = true
      }
    },
    // 编辑
    editDetail (id, type) {
      this.editTypeNum = type
      this.materialId = id
      this.getMaterialDetail()
      this.upLoadRes = {}
      if (type === 1) {
        this.editTextModal = true
      } else if (type === 3) {
        this.editImageTextModal = true
        this.modalType = 3
      } else if (type === 6) {
        this.modalType = 6
        this.editAppletsModal = true
      } else {
        this.editModal = true
        this.modalType = type
      }
    },
    // 删除素材
    delMaterial (id) {
      delMaterialLibrary({
        id: id
      }).then(res => {
        this.getTableData()
      })
    },
    // 弹框确认
    modalTypeDefine (num) {
      this.modalType = num
      this.pictureModal = true
      if (num === 2) {
        this.allTitle = '新建图片素材'
      } else if (num === 4) {
        this.allTitle = '新建音频素材'
      } else if (num === 5) {
        this.allTitle = '新建视频素材'
      } else if (num === 7) {
        this.allTitle = '新建文件素材'
      }
    },
    // 新增文本
    addText () {
      if (this.editTypeNum === 1) {
        const params = {
          id: this.materialDetail.id,
          type: this.editTypeNum,
          content: this.materialDetail.content,
          mediumGroupId: this.materialDetail.mediumGroupId
        }
        editMaterialLibrary(params).then(res => {
          this.upLoadRes = {}
          this.editTextModal = false
          this.getTableData()
        })
      } else {
        this.$refs.textForm.validate(valid => {
          if (valid) {
            const params = {
              type: this.tabsNumber,
              content: this.addTextData.content,
              mediumGroupId: this.materialGroupId
            }
            addMaterialLibrary(params).then(res => {
              this.upLoadRes = {}
              this.textModal = false
              this.getTableData()
              this.addTextData = {
                content: {
                  title: '',
                  content: ''
                }
              }
              this.materialGroupId = 0
            })
          } else {
            console.log('error submit!!')
            return false
          }
        })
      }
    },
    // 单独获取图片素材
    pictureModalDis () {
      this.upLoadPictureModal = true
      this.getPictureData()
    },
    // 分组筛选
    changeGroup (value) {
      this.materialGroupId = value
      this.getPictureData()
    },
    // input搜索
    changeInput (value) {
      this.imgSearchStr = value
      this.getPictureData()
    },
    // 选择图片素材
    imgPathDefine (item) {
      this.imgUrl = item.content.imageFullPath
      this.imgActive = item.id
      this.imgPath = item.content.imagePath
    },
    getPictureData () {
      materialLibraryList({
        type: 2,
        mediumGroupId: this.materialGroupId,
        searchStr: this.imgSearchStr
      }).then(res => {
        this.picTextModalData = res.data.list
      })
    },
    uploadSuccessV (data) {
      if (this.modalType === 4) {
        const voicePath = data.path
        this.upLoadRes.voicePath = voicePath
        this.upLoadRes.voiceName = data.name
        this.uploadDefine = true
      } else if (this.modalType === 5) {
        const videoPath = data.path
        this.upLoadRes.videoPath = videoPath
        this.upLoadRes.videoName = data.name
        this.uploadDefine = true
      } else if (this.modalType === 7) {
        const filePath = data.path
        this.upLoadRes.filePath = filePath
        this.upLoadRes.fileName = data.name
        this.uploadDefine = true
      }
    },
    // 上传
    uploadSuccess (data) {
      this.imgUrl = data.fullPath
      if (this.modalType === 2 || this.modalType === 6 || this.modalType === 3) {
        const imagePath = data.path
        this.upLoadRes.imagePath = imagePath
        this.upLoadRes.imageName = data.name
      }
    },
    // 添加图文素材
    addImageTextDefine () {
      if (this.editTypeNum === 3) {
        if (this.imgPath === '') {
          this.imgPath = this.upLoadRes.imagePath
        }
        const content = {
          title: this.materialDetail.content.title,
          description: this.materialDetail.content.description,
          imagePath: this.imgPath || this.materialDetail.content.imagePath,
          imageLink: this.materialDetail.content.imageLink
        }
        editMaterialLibrary({
          id: this.materialDetail.id,
          type: this.editTypeNum,
          content: content,
          mediumGroupId: this.materialDetail.mediumGroupId
        }).then(res => {
          this.upLoadRes = {}
          this.editImageTextModal = false
          this.getTableData()
          this.editTypeNum = ''
          this.imgUrl = ''
        })
      } else {
        this.$refs.imageForm.validate(valid => {
          if (valid) {
            if (this.imgPath === '') {
              this.imgPath = this.upLoadRes.imagePath
            }
            const contents = {
              title: this.imageTextData.title,
              description: this.imageTextData.description,
              imagePath: this.imgPath,
              imageLink: this.imageTextData.imageLink
            }
            if (contents.imagePath != undefined) {
              addMaterialLibrary({
                type: Number(this.tabsNumber),
                content: contents,
                mediumGroupId: this.materialGroupId
              }).then(res => {
                this.imgPath = ''
                this.upLoadRes = {}
                this.imageTextModal = false
                this.imageTextData = {}
                this.imgUrl = ''
                this.materialGroupId = 0
                this.getTableData()
              })
            } else {
              this.$message.error('请上传')
            }
          } else {
            console.log('error submit!!')
            return false
          }
        })
      }
    },
    // 取消小程序
    resetApplets () {
      this.appletsData = {}
      this.materialDetail.mediumGroupId = 0
      this.materialGroupId = 0
      if (!this.editAppletsModal) {
        this.$refs.appletsForm.resetFields()
      }
      this.appletsModal = false
      this.editAppletsModal = false
      this.imgUrl = ''
      this.upLoadRes = {}
    },
    // 添加小程序
    appletsDefine () {
      if (this.editTypeNum === 6) {
        const content = {
          appid: this.materialDetail.content.appid,
          page: this.materialDetail.content.page,
          imagePath: Object.keys(this.upLoadRes).length === 2 ? this.upLoadRes.imagePath : this.materialDetail.content.imagePath,
          imageName: Object.keys(this.upLoadRes).length === 2 ? this.upLoadRes.imageName : this.materialDetail.content.imageName,
          title: this.materialDetail.content.title
        }
        editMaterialLibrary({
          id: this.materialDetail.id,
          type: this.editTypeNum,
          content: content,
          mediumGroupId: this.materialDetail.mediumGroupId
        }).then(res => {
          this.editAppletsModal = false
          this.getTableData()
          this.editTypeNum = ''
          this.imgUrl = ''
        })
      } else {
        this.$refs.appletsForm.validate(valid => {
          if (valid) {
            const contents = {
              appid: this.appletsData.appid,
              page: this.appletsData.page,
              imagePath: this.upLoadRes.imagePath,
              imageName: this.upLoadRes.imageName,
              title: this.appletsData.title
            }
            console.log(this.upLoadRes.imagePath)
            if (contents.imagePath != undefined) {
              addMaterialLibrary({
                type: Number(this.tabsNumber),
                content: contents,
                mediumGroupId: this.materialGroupId
              }).then(res => {
                this.materialGroupId = 0
                this.upLoadRes = {}
                this.appletsModal = false
                this.appletsData = {}
                this.getTableData()
                this.imgUrl = ''
              })
            } else {
              this.$message.error('请上传')
            }
          } else {
            console.log('error submit!!')
            return false
          }
        })
      }
    },
    // 确认添加
    defineModelType () {
      if (Object.keys(this.upLoadRes).length === 2) {
        addMaterialLibrary({
          type: Number(this.tabsNumber),
          content: this.upLoadRes,
          mediumGroupId: this.materialGroupId
        }).then(res => {
          this.upLoadRes = {}
          this.pictureModal = false
          this.getTableData()
          this.imgUrl = ''
          this.uploadDefine = false
          this.materialGroupId = 0
        })
      } else {
        this.$message.error('请上传')
      }
    },
    // 移动分组
    moveTagModal (id) {
      this.moveId = id
      this.moveModal = true
      this.currentId = this.changeGroupId
      this.classActive = this.currentId
    },
    moveTag (id) {
      this.classActive = id
      this.currentId = id
    },
    changeGroupMove (value) {
      this.currentId = value
      this.classActive = value
    },
    moveDefine () {
      moveGroup({
        id: this.moveId,
        mediumGroupId: this.currentId
      }).then(res => {
        this.moveModal = false
        this.getTableData()
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    // 取消新建图文
    resetTextImg (type) {
      this.materialGroupId = 0
      this.imageTextData = {}
      this.upLoadRes = {}
      this.imgUrl = ''
      this.$refs.imageForm.resetFields()
      if (type === '重置') {
        this.imageTextModal = true
      } else {
        this.imageTextModal = false
      }
    },
    linkJump () {
      window.open = ('https://mp.weixin.qq.com/cgi-bin/wx')
    }
  }
}
</script>

<style lang="less" scoped>
.material-library {
    height: 100%;
    .left {
      background: #fff;
      padding: 15px 15px;
      .btn {
        margin-top: 10px;
        width: 100%;
      }
      .group-list {
        .tags {
          display: inline-block;
          width: 100%;
          height: 40px;
          line-height: 40px;
          margin-top: 5px
        }
        .activeTag {
          display: inline-block;
          width: 100%;
          height: 40px;
          line-height: 40px;
          padding-left:5px;
          border-radius: 3px;
          background: #1890FF;
          margin-top:5px;
          color: #fff;
        }
      }
    }
    .lists{
      margin-bottom: 20px;
    }
    .tag-box {
      .picture {
        width: 100%;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        padding: 10px;
        margin-top: 20px;
        .ant-card {
          margin-right: 20px;
          margin-bottom: 15px;
          .time {
            width: 100%;
            height: 30px;
            display: flex;
            margin-bottom: 10px;
            p {
              font-size: 16px;
              margin: 0;
              flex: 9
            }
            .ant-checkbox-wrapper {
              flex: 1;
            }
          }
          .video-box {
            width: 100%;
            height: 120px;
            margin-bottom:10px;
            text-align: center;
            position: relative;
            video{
              width: auto;
              height: auto;
              max-height: 100%;
              max-width: 100%;
            }
          }
          .file-box {
            width: 100%;
            height: 80px;
            margin-bottom:10px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            .anticon {
              margin-bottom: 10px;
            }
            span {
              width: 100%;
              height: 30px;
              line-height: 30px;
              text-align: center;
              color: #fff;
              background: #000;
              opacity: 0.6;
              display: inline-block;
              position: absolute;
              bottom: 0;
              left: 0;
            }
          }
         .img-box {
           width: 100%;
           height: 120px;
           margin-bottom:10px;
           text-align: center;
           position: relative;
           span {
              width: 100%;
              height: 30px;
              line-height: 30px;
              color: #fff;
              background: #000;
              opacity: 0.6;
              display: inline-block;
              position: absolute;
              bottom: 0;
              left: 0;
            }
            img{
              width: auto;
              height: auto;
              max-height: 100%;
              max-width: 100%;
            }
         }
          .text-box {
            width: 100%;
            display: flex;
            flex-direction: column;
          }
        }
      }
      .top-btn {
        width: 100%;
        display: flex;
        justify-content: space-between;
        .search {
          width: 300px;
          display: flex;
        }
        .btn {
          padding: 0 15px;
        }
      }
    }
    .group-btn {
      display: flex;
      justify-content: flex-end;
    }
     .bbox {
      .tag-box {
        .box {
          display: inline-block;
          height: 60px;
          padding: 0 25px;
          border: 1px solid #ccc;
          text-align: center;
          line-height: 60px;
          margin: 0 20px 20px 0;
          border-radius: 8px;
        }
        .active {
          display: inline-block;
          height: 60px;
          padding: 0 25px;
          border: 1px solid rgb(62, 142, 233);
          color: rgb(62, 142, 233);
          text-align: center;
          line-height: 60px;
          margin: 0 20px 20px 0;
          border-radius: 8px;
        }
      }
    }
    .sbox {
      position: fixed;
      z-index: 9999;
      .img-box {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        padding: 20px;
        div {
          width: 22%;
          height: auto;
          margin-right: 20px;
          border-radius: 8%;
          img {
            width: 100%;
            height: auto;
          }
        }
        .img-active {
          width: 22%;
          margin-right: 20px;
          img {
            width: 100%;
            height: auto;
            border: 2px solid rgb(62, 142, 233);
            border-radius: 8%;
          }
        }
      }
    }
    .applets-box {
      display: flex;
      .left {
        width: 65%;
        .msg-box {
          width: 85%;
          border: 1px solid #FFE1B6;
          background: #FFF1DE;
          padding: 10px;
          margin-bottom: 20px;
        }
      }
      .right {
        width: 30%;
        margin-top: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        img {
          margin-bottom: 10px;
        }
      }
    }
  }
</style>
