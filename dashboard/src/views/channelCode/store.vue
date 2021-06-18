<template>
  <div class="wrapper">
    <a-card title="基础设置">
      <a-form-model ref="baseInfoRules" :model="baseInfo" :rules="baseInfoRules" :label-col="{ span: 2 }" :wrapper-col="{ span: 10 }">
        <a-form-model-item label="选择分组" prop="groupId">
          <a-select v-model="baseInfo.groupId">
            <a-select-option v-for="item in channelCodeGroup" :key="item.groupId">{{ item.name }}</a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="活码名称" prop="name">
          <a-input v-model="baseInfo.name" placeholder="搜索群名称" :maxLeng="30" :disabled="isEdit"></a-input>
          <span style="color:red">(一旦创建，不可修改)</span>
        </a-form-model-item>
        <a-form-model-item label="自动添加好友" prop="autoAddFriend">
          <a-radio-group v-model="baseInfo.autoAddFriend">
            <a-radio :value="2">需验证</a-radio>
            <a-radio :value="1">直接通过</a-radio>
          </a-radio-group>
          <span style="color:red">（开启时，客户添加时无需企业成员确认，自动成为好友）</span>
        </a-form-model-item>
        <a-form-model-item label="客户标签">
          <div class="label">
            <ul :class="btnText == '展开' ? 'tags-wrapper' : 'tags-active'">
              <li class="tag-item" v-for="(item, index) in tagList" :key="index + '标签'">
                <a-button class="group-name" @click="chooseTagGroup(item.groupId)">{{ item.groupName }}</a-button>
                <div class="tag-content">
                  <a-tag class="tag" :color="inner.isSelected == 1 ? '#1890ff' : ''" v-for="(inner, i) in item.list" @click="chooseTag(item, inner.tagId)" :key="i + '新建标签'">{{ inner.tagName }}</a-tag>
                  <a-button class="add-new" @click="addNewTag(item.groupId)">+新建标签</a-button>
                </div>
              </li>
            </ul>
            <a-button @click="() => {this.tagGroupModal = true}">+ 新建标签组</a-button>
            <a-button v-if="btnTextDis" class="more-btn" type="link" @click="clickMore">{{ btnText }}</a-button>
          </div>
        </a-form-model-item>
      </a-form-model>
      <a-modal
        title="新建标签"
        :visible="tagModal"
        @cancel="() => { this.tagModal = false}">
        <a-form-model :label-col="{ span: 4 }" :wrapper-col="{ span: 16 }">
          <a-form-model-item label="标签名称">
            <p>每个标签名称最多15个字。同时新建多个标签时，请用“空格”隔开</p>
            <a-input v-model="newTagsName" placeholder="请输入标签（不得超过15个字符）"/>
          </a-form-model-item>
        </a-form-model>
        <template slot="footer">
          <a-button>取消</a-button>
          <a-button :loading="btnLoading" @click="addGroupTags">确定</a-button>
        </template>
      </a-modal>
      <a-modal
        title="新建标签组"
        :visible="tagGroupModal"
        @cancel="() => { this.tagGroupModal = false; this.newGroupName = ''}">
        <a-input v-model="newGroupName" placeholder="请输入分组名（不得超过15个字符）"/>
        <template slot="footer">
          <a-button @click="() => { this.tagGroupModal = false; this.newGroupName = ''}">取消</a-button>
          <a-button :loading="btnLoading" @click="addGroup">确定</a-button>
        </template>
      </a-modal>
    </a-card>
    <a-card title="引流成员设置" style="marginTop: 20px">
      <a-alert type="error" :show-icon="false" message="扫码添加成员规则：在周期和特殊时期共存情况下，扫码优先添加特殊时期的成员" banner />
      <a-form-model ref="one" :label-col="{ span: 2 }" :wrapper-col="{ span: 12 }">
        <a-form-model-item label="类型">
          <a-radio-group :disabled="isEdit" v-model="drainageEmployee.type" @change="typeChange">
            <a-radio :value="1">单人</a-radio>
            <a-radio :value="2">多人</a-radio>
          </a-radio-group>
          <span style="color:red">(一旦创建,不可修改)</span>
        </a-form-model-item>
        <a-form-model-item label="企业成员">
          <a-button v-if="drainageEmployee.employees.length !== 7" @click="() => {this.memberModal = true}">+ 添加成员</a-button>
        </a-form-model-item>
        <a-form-model-item>
          <div class="table">
            <div v-for="item in weekList" :key="item.value">
              <div class="title"><span>{{ item.label }}</span></div>
              <div class="content" v-for="(vals, ind) in drainageEmployee.employees" :key="ind + '表格'" v-if="vals.week == item.value">
                <div v-for="(val, index) in vals.timeSlot" :key="index + '单人表格'" v-if="drainageEmployee.type == 1">
                  <a-icon type="edit" @click="editEmployess(vals, ind, index)"/>
                  <p>
                    <span>{{ val.startTime }}</span>~<span>{{ val.endTime }}</span>
                  </p>
                  <a-button disabled v-if="typeof val.selectMembers == 'string'">{{ val.selectMembers }}</a-button>
                  <a-button disabled v-else>{{ val.selectMembers.join(' ') }}</a-button>
                </div>
                <div v-for="(vas, index) in vals.timeSlot" :key="index + '多人表格'" v-if="drainageEmployee.type == 2">
                  <a-icon type="edit" @click="editEmployess(vals, ind, index)"/>
                  <p>
                    <span>{{ vas.startTime }}</span>~<span>{{ vas.endTime }}</span>
                  </p>
                  <a-tooltip>
                    <template slot="title" v-if="vas.selectMembers && vas.selectMembers.length !== 0">
                      {{ vas.selectMembers.join(',') }}
                    </template>
                    <p class="name" v-if="vas.selectMembers && vas.selectMembers.length !== 0">
                      <span>{{ vas.selectMembers.join(',') }}</span>
                    </p>
                  </a-tooltip>
                  <div class="department" v-if="vas.selectDepartment && vas.selectDepartment.length !== 0">
                    <span v-for="(vl, num) in vas.selectDepartment" :key="num + '部门'">{{ vl }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </a-form-model-item>
        <a-form-model-item label="特殊时期">
          <a-radio-group v-model="drainageEmployee.specialPeriod.status">
            <a-radio :value="1">开启</a-radio>
            <a-radio :value="2">关闭</a-radio>
          </a-radio-group>
          <span style="color:red; marginLeft:10px">(如遇到国家节假日或是企业自身重要日子，在特殊时期内，需要更换活码成员)</span>
          <div v-if="drainageEmployee.specialPeriod.status == 1">
            <div v-if="drainageEmployee.type == 1" class="special-box" v-for="(itam, i) in specialTimeList" :key="i + '时期'">
              <a-form-model style="width: 800px;" :label-col="{ span: 3 }" :wrapper-col="{ span: 20}">
                <a-form-model-item :label="'特殊时期' + (i + 1)">
                  <div @click="clickPicker(i)">
                    <a-range-picker v-model="itam.dataString" @change="changeData"/>
                  </div>
                  <div v-for="(val, index) in itam.timeSlot" :key="index + '特殊时期列表'">
                    <a-row style="marginLeft: 100px;" v-if="val.startTime == '00:00'">
                      <a-col :span="5">
                        <a-time-picker format="HH:mm" :minute-step="60" :default-value="moment('00:00', 'HH:mm')" disabled/>
                      </a-col>
                      <a-col :span="5">
                        <a-time-picker format="HH:mm" :minute-step="60" :default-value="moment('00:00', 'HH:mm')" disabled/>
                      </a-col>
                      <a-col :span="7">
                        <a-select label-in-value v-model="val.employeeSelect">
                          <a-select-option v-for="item in memberList" :key="item.employeeId">{{ item.name }}</a-select-option>
                        </a-select>
                      </a-col>
                    </a-row>
                    <div v-else>
                      <a-row style="marginLeft: 100px;">
                        <a-col :span="5">
                          <a-time-picker
                            format="HH:mm"
                            :minute-step="60"
                            valueFormat="HH:mm"
                            v-model="val.startTime"
                            @change="(val,dateStrings)=>changeTime(val,dateStrings,'startTime')"/>
                        </a-col>
                        <a-col :span="5">
                          <a-time-picker
                            format="HH:mm"
                            :minute-step="60"
                            valueFormat="HH:mm"
                            v-model="val.endTime"
                            :disabledHours="getDisabledHours"
                            :disabledSeconds="getDisabledSeconds"
                            @change="(val,dateStrings)=>changeTime(val,dateStrings,'endTime')"/>
                        </a-col>
                        <a-col :span="7">
                          <a-select label-in-value v-model="val.employeeSelect">
                            <a-select-option v-for="item in memberList" :key="item.employeeId">{{ item.name }}</a-select-option>
                          </a-select>
                        </a-col>
                      </a-row>
                      <a-button style="marginLeft: 90px; color: red" type="link" @click="deleteSpecialTime(i, index)">删除</a-button>
                    </div>
                  </div>
                  <a-button style="marginLeft: 90px;" type="link" @click="addSpecialTime(i)">添加</a-button>
                  <a-icon type="delete" style="color:red" v-if="specialTimeList.length > 1" @click="deleteSpecial(i)"/>
                </a-form-model-item>
              </a-form-model>
            </div>
            <div class="special-box" v-if="drainageEmployee.type == 2" v-for="(itam, i) in specialTimeList" :key="i + '时期dd'">
              <a-form-model style="width: 800px;" :label-col="{ span: 3 }" :wrapper-col="{ span: 20}">
                <a-form-model-item :label="'特殊时期' + (i + 1)">
                  <div @click="clickPicker(i)">
                    <a-range-picker v-model="itam.dataString" @change="changeData"/>
                  </div>
                  <div v-for="(val, index) in itam.timeSlot" :key="index + '特殊时期ll'">
                    <div v-if="val.startTime == '00:00'">
                      <a-row style="marginLeft: 100px;">
                        <a-col :span="5">
                          <a-time-picker :minute-step="60" format="HH:mm" valueFormat="HH:mm" :default-value="moment('00:00', 'HH:mm')" disabled/>
                        </a-col>
                        <a-col :span="5">
                          <a-time-picker :minute-step="60" format="HH:mm" valueFormat="HH:mm" :default-value="moment('00:00', 'HH:mm')" disabled/>
                        </a-col>
                        <a-col :span="7">
                          <span style="marginRight: 5px" v-if="val.selectMembers && val.selectMembers.length !== 0">{{ val.selectMembers.join(' ') }}</span>
                          <a-button @click="specialMember(i,index)">选择企业成员</a-button>
                        </a-col>
                      </a-row>
                      <a-row>
                        <template>
                          <a-tree-select
                            v-model="val.departmentId"
                            style="width: 400px; marginLeft: 100px;"
                            :label-in-value="false"
                            :tree-data="departmentList"
                            :replaceFields="replaceFields"
                            :treeCheckStrictly="true"
                            tree-checkable
                            search-placeholder="Please select"
                          />
                        </template>
                      </a-row>
                    </div>
                    <div v-else>
                      <a-row style="marginLeft: 100px;">
                        <a-col :span="5">
                          <a-time-picker
                            :minute-step="60"
                            format="HH:mm"
                            valueFormat="HH:mm"
                            v-model="val.startTime"
                            @change="(val,dateStrings)=>changeTime(val,dateStrings,'startTime')"/>
                        </a-col>
                        <a-col :span="5">
                          <a-time-picker
                            :minute-step="60"
                            format="HH:mm"
                            valueFormat="HH:mm"
                            v-model="val.endTime"
                            :disabledHours="getDisabledHours"
                            :disabledSeconds="getDisabledSeconds"
                            @change="(val,dateStrings)=>changeTime(val,dateStrings,'endTime')"/>
                        </a-col>
                        <a-col :span="7">
                          <span v-if="val.selectMembers && val.selectMembers.length !== 0">{{ val.selectMembers.join(' ') }}</span>
                          <a-button @click="specialMember(i,index)">选择企业成员</a-button>
                        </a-col>
                      </a-row>
                      <a-row>
                        <template>
                          <a-tree-select
                            v-model="val.departmentId"
                            style="width: 400px; marginLeft: 100px;"
                            :label-in-value="false"
                            :tree-data="departmentList"
                            :replaceFields="replaceFields"
                            :treeCheckStrictly="true"
                            tree-checkable
                            search-placeholder="Please select"
                          />
                        </template>
                      </a-row>
                      <a-button style="marginLeft: 90px; color: red" type="link" @click="deleteSpecialTime(i, index)">删除</a-button>
                    </div>
                  </div>
                  <a-button style="marginLeft: 90px;" type="link" @click="addSpecialTime(i)">添加</a-button>
                </a-form-model-item>
              </a-form-model>
            </div>
            <a-button type="primary" size="small" @click="addPeriod">+添加时期</a-button>
          </div>
        </a-form-model-item>
        <a-form-model-item label="员工添加上限：">
          <a-radio-group v-model="drainageEmployee.addMax.status" @change="onAddMaxChange">
            <a-radio :value="1">开启</a-radio>
            <a-radio :value="2">关闭</a-radio>
          </a-radio-group>
          <span style="color:red; marginLeft:10px">(因受官方限制，无法对动态部门的员工设置添加好友上限，只可针对指定活码成员进行设置)</span>
          <a-table
            v-if="drainageEmployee.addMax.status == 1"
            :columns="columns"
            :data-source="allMemderList"
            :rowKey="record => record.employeeId"
            :pagination="false">
            <div slot="max" slot-scope="text, record">
              <input v-model="record.max" @blur="inputNum(record.max)"/>
            </div>
          </a-table>
        </a-form-model-item>
        <a-form-model-item label="备用员工" v-if="drainageEmployee.addMax.status == 1">
          <a-select v-model="drainageEmployee.addMax.spareEmployeeIds" v-if="drainageEmployee.type == 1" @change="onChange">
            <a-select-option v-for="item in memberList" :key="item.employeeId">{{ item.name }}</a-select-option>
          </a-select>
          <a-button v-else @click="recordIndex(0, '备用人员')">选择企业成员</a-button>
        </a-form-model-item>
      </a-form-model>
      <a-modal
        width="800px"
        title="添加企业成员"
        :visible="memberModal"
        @cancel="resetDrainage">
        <a-alert type="warning" :show-icon="false" message="1、系统默认生成一条每日24小时（00:00~00:00）数据，当时间点不在所新增的时段内，客户扫码后，添加的是“24小时”的企业成员。" banner />
        <a-alert type="warning" :show-icon="false" message="2、因受官方限制，无法对动态部门的员工设置添加好友上限，只可针对指定活码成员进行设置。" banner />
        <a-form-model style="marginTop: 20px" :label-col="{ span: 3 }" :wrapper-col="{ span: 20}">
          <a-form-model-item label="固定时段" v-if="drainageEmployee.type == 1">
            <div v-for="(item, index) in singleTimeList" :key="index + '单人时间'">
              <a-row v-if="item.startTime == '00:00'">
                <a-col :span="5">
                  <a-time-picker format="HH:mm" :minute-step="60" :default-value="moment('00:00', 'HH:mm')" disabled/>
                </a-col>
                <a-col :span="5">
                  <a-time-picker format="HH:mm" :minute-step="60" :default-value="moment('00:00', 'HH:mm')" disabled />
                </a-col>
                <a-col :span="7">
                  <a-select label-in-value v-model="item.employeeSelect">
                    <a-select-option v-for="val in memberList" :key="val.employeeId">{{ val.name }}</a-select-option>
                  </a-select>
                </a-col>
              </a-row>
              <a-row v-else>
                <a-col :span="5">
                  <a-time-picker
                    :minute-step="60"
                    format="HH:mm"
                    valueFormat="HH:mm"
                    v-model="item.startTime"
                    @change="(val,dateStrings)=>changeTime(val,dateStrings,'startTime')"/>
                </a-col>
                <a-col :span="5">
                  <a-time-picker
                    :minute-step="60"
                    format="HH:mm"
                    valueFormat="HH:mm"
                    v-model="item.endTime"
                    :disabledHours="getDisabledHours"
                    :disabledSeconds="getDisabledSeconds"
                    @change="(val,dateStrings)=>changeTime(val,dateStrings,'endTime')"/>
                </a-col>
                <a-col :span="7">
                  <a-select label-in-value v-model="item.employeeSelect">
                    <a-select-option v-for="val in memberList" :key="val.employeeId">{{ val.name }}</a-select-option>
                  </a-select>
                </a-col>
                <a-button type="link" style="color: red" @click="deleteSingle(index, '单人')">删除</a-button>
              </a-row>
            </div>
            <a-button type="link" @click="addSingleTime">添加</a-button>
          </a-form-model-item>
          <a-form-model-item label="固定时段" v-else>
            <div v-for="(val, index) in manyTimeList" :key="index + '多人添加23'">
              <div v-if="val.startTime == '00:00'">
                <a-row>
                  <a-col :span="5">
                    <a-time-picker :minute-step="60" format="HH:mm" :default-value="moment('00:00', 'HH:mm')" disabled/>
                  </a-col>
                  <a-col :span="5">
                    <a-time-picker :minute-step="60" format="HH:mm" :default-value="moment('00:00', 'HH:mm')" disabled/>
                  </a-col>
                  <a-col :span="7">
                    <span v-if="val.selectMembers && val.selectMembers.length !== 0">{{ val.selectMembers.join(',') }}</span>
                    <a-button @click="recordIndex(index, '人员')">选择企业成员</a-button>
                  </a-col>
                </a-row>
                <template>
                  <a-tree-select
                    v-model="val.departmentSelect"
                    style="width: 100%"
                    :tree-data="departmentList"
                    :replaceFields="replaceFields"
                    :treeCheckStrictly="true"
                    tree-checkable
                    search-placeholder="Please select"
                  />
                </template>
              </div>
              <div v-else>
                <a-row>
                  <a-col :span="5">
                    <a-time-picker
                      :minute-step="60"
                      format="HH:mm"
                      valueFormat="HH:mm"
                      v-model="val.startTime"
                      @change="(val,dateStrings)=>changeTime(val,dateStrings,'startTime')"/>
                  </a-col>
                  <a-col :span="5">
                    <a-time-picker
                      :minute-step="60"
                      format="HH:mm"
                      valueFormat="HH:mm"
                      v-model="val.endTime"
                      :disabledHours="getDisabledHours"
                      :disabledSeconds="getDisabledSeconds"
                      @change="(val,dateStrings)=>changeTime(val,dateStrings,'endTime')"/>
                  </a-col>
                  <a-col :span="5" v-if="val.selectMembers && val.selectMembers.length !== 0">
                    <span>{{ val.selectMembers.join(' ') }}</span>
                  </a-col>
                  <a-col :span="7">
                    <a-button @click="recordIndex(index, '人员')">选择企业成员</a-button>
                  </a-col>
                </a-row>
                <template>
                  <a-tree-select
                    v-model="val.departmentSelect"
                    style="width: 100%"
                    :tree-data="departmentList"
                    :replaceFields="replaceFields"
                    :treeCheckStrictly="true"
                    tree-checkable
                    search-placeholder="Please select"
                  />
                </template>
                <a-button type="link" style="color: red" @click="deleteSingle(index, '多人')">删除</a-button>
              </div>
            </div>
            <a-button type="link" @click="addManyTime">添加</a-button>
          </a-form-model-item>
          <a-form-model-item label="适用周期">
            <a-tag class="tag" :color="item.isChecked == 1 ? '#1890ff' : ''" v-for="(item, i) in weekTagList" @click="chooseWeekTag(item.id)" :key="i + 'esd'">{{ item.name }}</a-tag>
          </a-form-model-item>
        </a-form-model>
        <template slot="footer">
          <a-button @click="resetDrainage">取消</a-button>
          <a-button type="primary" :loading="btnLoading" @click="drainageDefine">确定</a-button>
        </template>
      </a-modal>
      <a-modal
        title="选择企业成员"
        :maskClosable="false"
        :width="700"
        :visible="choosePeopleShow"
        @cancel="choosePeopleShow = false"
      >
        <Department v-if="choosePeopleShow" :isChecked="employees" :memberKey="employees" @change="peopleChange" @search="searchEmp"></Department>
        <template slot="footer">
          <a-button @click="() => { this.choosePeopleShow = false; this.employeeIdList = ''; this.employees = [] }">取消</a-button>
          <a-button type="primary" @click="() => { this.choosePeopleShow = false }">确定</a-button>
        </template>
      </a-modal>
    </a-card>
    <a-card style="marginTop: 20px" title="欢迎语设置">
      <a-alert type="error" :show-icon="false" message="欢迎语推送规则：在通用、周期及特殊时期欢迎语共存情况下，推送优先顺序为特殊时期的欢迎语 > 按周期的欢迎语 > 通用欢迎语。若企业微信官方后台已配置了欢迎语，则在第三方系统配置的欢迎语均失效，客户收到的依然是官方推送的。" banner />
      <a-form-model ref="two" :label-col="{ span: 2 }" :wrapper-col="{ span: 12 }">
        <a-form-model-item label="扫码推送：">
          <a-radio-group v-model="welcomeMessage.scanCodePush">
            <a-radio :value="1">开启</a-radio>
            <a-radio :value="2">关闭</a-radio>
          </a-radio-group>
          <span style="color:red; marginLeft:10px">(关闭后，客户扫该渠道活码，依然可对该客户自动打标签，但仅收到系统的【欢迎语】消息)</span>
        </a-form-model-item>
      </a-form-model>
      <a-tabs
        v-if="welcomeMessage.scanCodePush == 1"
        tab-position="left"
        @change="tabChange">
        <a-tab-pane key="1" tab="通用欢迎语">
          <div class="box">
            <div class="left">
              <p>客服账号</p>
              <div class="dialogue-box">
                <div class="portrait">
                  <a-icon type="user" class="user" />
                </div>
                <div class="content">
                  {{ currencyMessage.welcomeContent }}
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
            </div>
            <div class="right">
              <a-textarea id="currency" placeholder="请输入消息内容" :rows="4" v-model="currencyMessage.welcomeContent"/>
              <a-button style="marginTop: 20px; marginBottom: 20px" type="primary" @click="insert('通用', 0, 0)">客户名称</a-button>
              <a-alert message="提示：火狐浏览器可能出现无法正确插入客户名称，请使用谷歌、360浏览器" type="info"/>
              <a-popover style="marginTop: 20px" v-if="mediumDetail.mediumTitle == undefined">
                <template slot="content">
                  <a-button icon="file-image" @click="imgBoxModal('图片')">
                    图片
                  </a-button>
                  <a-button icon="link" @click="imgBoxModal('图文')">
                    图文
                  </a-button>
                  <a-button icon="paper-clip" @click="imgBoxModal('小程序')">
                    小程序
                  </a-button>
                </template>
                <a-button>
                  +添加图片/图文/小程序
                </a-button>
              </a-popover>
              <div class="medium-btn" v-else>
                <a-icon type="link" /> <span @click="showMedium">{{ mediumDetail.mediumTitle }}</span> <a-icon type="close" @click="closeMedium"/>
              </div>
            </div>
          </div>
        </a-tab-pane>
        <a-tab-pane key="2" tab="周期欢迎语">
          <a-form-model :label-col="{ span: 2 }" :wrapper-col="{ span: 12 }">
            <a-form-model-item label="状态">
              <a-radio-group v-model="cycleMessage.status">
                <a-radio :value="1">开启</a-radio>
                <a-radio :value="2">关闭</a-radio>
              </a-radio-group>
            </a-form-model-item>
          </a-form-model>
          <a-tabs type="editable-card" @edit="onEdit" v-if="cycleMessage.status == 1">
            <a-tab-pane v-for="(item, index) in cycleMessage.detail" :key="'周期欢迎语' + (index + 1)" :tab="'欢迎语' + (index + 1) " :closable="index !== 0">
              <a-form-model ref="thr" :label-col="{ span: 2 }" :wrapper-col="{ span: 12 }">
                <a-form-model-item label="选择周期" v-if="cycleMessage.status == 1">
                  <a-tag class="tag" :color="v.isChecked == 1 ? '#1890ff' : ''" v-for="(v, i) in cycleWeekTagList[index]" @click="cycleWeek(v.id, index)" :key="i">{{ v.name }}</a-tag>
                </a-form-model-item>
              </a-form-model>
              <div style="marginTop: 20px">
                <div v-for="(val, ind) in item.timeSlot" :key="ind + '周期时间'" class="message-box">
                  <a-form-model :label-col="{ span: 2 }" :wrapper-col="{ span: 12 }">
                    <a-form-model-item :label="'时间段' + (ind + 1)">
                      <a-time-picker :minute-step="60" format="HH:mm" valueFormat="HH:mm" v-model="val.startTime" />
                      <a-time-picker :minute-step="60" format="HH:mm" valueFormat="HH:mm" v-model="val.endTime"/>
                      <a-icon v-if="item.timeSlot.length > 1" style="color: red; marginLeft: 20px" type="delete" @click="cycleDelete(index, ind)" />
                    </a-form-model-item>
                  </a-form-model>
                  <div class="box">
                    <div class="left">
                      <p>客服账号</p>
                      <div class="dialogue-box">
                        <div class="portrait">
                          <a-icon type="user" class="user" />
                        </div>
                        <div class="content">
                          {{ val.welcomeContent }}
                        </div>
                      </div>
                      <div v-if="cycleMediumList[index].medium[ind].type != undefined">
                        <div class="dialogue-box" v-if="cycleMediumList[index].medium[ind].type == 2">
                          <div class="portrait">
                            <a-icon type="user" class="user" />
                          </div>
                          <div class="img-box">
                            <img :src="cycleMediumList[index].medium[ind].imageFullPath" alt="">
                          </div>
                        </div>
                        <div class="dialogue-box" v-if="cycleMediumList[index].medium[ind].type == 6">
                          <div class="portrait">
                            <a-icon type="user" class="user" />
                          </div>
                          <div class="applets-box">
                            <h4>{{ cycleMediumList[index].medium[ind].title }}</h4>
                            <img :src="cycleMediumList[index].medium[ind].imageFullPath" alt="">
                          </div>
                        </div>
                        <div class="dialogue-box" v-if="cycleMediumList[index].medium[ind].type == 3">
                          <div class="portrait">
                            <a-icon type="user" class="user" />
                          </div>
                          <div class="text-box">
                            <h4>{{ cycleMediumList[index].medium[ind].title }}</h4>
                            <div>
                              <span v-if="cycleMediumList[index].medium[ind].description">{{ cycleMediumList[index].medium[ind].description }}</span>
                              <img :src="cycleMediumList[index].medium[ind].imageFullPath" alt="">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="right">
                      <a-textarea id="cycle" placeholder="请输入消息内容" :rows="4" v-model="val.welcomeContent"/>
                      <a-button style="marginTop: 20px; marginBottom: 20px" type="primary" @click="insert('周期', ind, index)">客户名称</a-button>
                      <a-alert message="提示：火狐浏览器可能出现无法正确插入客户名称，请使用谷歌、360浏览器" type="info"/>
                      <a-popover style="marginTop: 20px" v-if="cycleMediumList[index].medium[ind].mediumTitle == '' || cycleMediumList[index].medium[ind].mediumTitle == undefined">
                        <template slot="content">
                          <a-button icon="file-image" @click="cycleImgBoxModal('图片', index, ind)">
                            图片
                          </a-button>
                          <a-button icon="link" @click="cycleImgBoxModal('图文', index, ind)">
                            图文
                          </a-button>
                          <a-button icon="paper-clip" @click="cycleImgBoxModal('小程序', index, ind)">
                            小程序
                          </a-button>
                        </template>
                        <a-button>
                          +添加图片/图文/小程序
                        </a-button>
                      </a-popover>
                      <div class="medium-btn" v-else>
                        <a-icon type="link" /> <span>{{ cycleMediumList[index].medium[ind].mediumTitle }}</span> <a-icon type="close" @click="cycleCloseMedium(index, ind)"/>
                      </div>
                    </div>
                  </div>
                </div>
                <a-button style="marginTop:20px;" @click="addCycleTime(index)">添加时间段</a-button>
              </div>
            </a-tab-pane>
          </a-tabs>
        </a-tab-pane>
        <a-tab-pane key="3" tab="特殊时期欢迎语">
          <a-form-model :label-col="{ span: 2 }" :wrapper-col="{ span: 12 }">
            <a-form-model-item label="状态">
              <a-radio-group v-model="specialMessage.status">
                <a-radio :value="1">开启</a-radio>
                <a-radio :value="2">关闭</a-radio>
              </a-radio-group>
            </a-form-model-item>
          </a-form-model>
          <a-tabs type="editable-card" @edit="onEdits" v-if="specialMessage.status == 1">
            <a-tab-pane v-for="(item, index) in specialMessage.detail" :key="'特殊时期欢迎语' + (index + 1)" :tab="'欢迎语' + (index + 1) " :closable="index !== 0">
              <div>
                <a-form-model :label-col="{ span: 2 }" :wrapper-col="{ span: 12 }">
                  <a-form-model-item label="日期1">
                    <div @click="defineDateIndex(index)">
                      <a-range-picker @change="specialDate" v-model="item.dataString"/>
                    </div>
                  </a-form-model-item>
                </a-form-model>
                <div v-for="(val, ind) in item.timeSlot" :key="ind + '特殊时期时间'" class="message-box">
                  <a-form-model :label-col="{ span: 2 }" :wrapper-col="{ span: 12 }">
                    <a-form-model-item :label="'时间段' + (ind + 1)">
                      <a-time-picker :minute-step="60" format="HH:mm" valueFormat="HH:mm" v-model="val.startTime" />
                      <a-time-picker :minute-step="60" format="HH:mm" valueFormat="HH:mm" v-model="val.endTime"/>
                      <a-icon v-if="item.timeSlot.length > 1" style="color: red; marginLeft: 20px" type="delete" @click="specialDelete(index, ind)" />
                    </a-form-model-item>
                  </a-form-model>
                  <div class="box">
                    <div class="left">
                      <p>客服账号</p>
                      <div class="dialogue-box">
                        <div class="portrait">
                          <a-icon type="user" class="user" />
                        </div>
                        <div class="content">
                          {{ val.welcomeContent }}
                        </div>
                      </div>
                      <div class="dialogue-box" v-if="specialMediumList[index].medium[ind].type == 2">
                        <div class="portrait">
                          <a-icon type="user" class="user" />
                        </div>
                        <div class="img-box">
                          <img :src="specialMediumList[index].medium[ind].imageFullPath" alt="">
                        </div>
                      </div>
                      <div class="dialogue-box" v-if="specialMediumList[index].medium[ind].type == 6">
                        <div class="portrait">
                          <a-icon type="user" class="user" />
                        </div>
                        <div class="applets-box">
                          <h4>{{ specialMediumList[index].medium[ind].title }}</h4>
                          <img :src="specialMediumList[index].medium[ind].imageFullPath" alt="">
                        </div>
                      </div>
                      <div class="dialogue-box" v-if="specialMediumList[index].medium[ind].type == 3">
                        <div class="portrait">
                          <a-icon type="user" class="user" />
                        </div>
                        <div class="text-box">
                          <h4>{{ specialMediumList[index].medium[ind].title }}</h4>
                          <div>
                            <span v-if="specialMediumList[index].medium[ind].description">{{ specialMediumList[index].medium[ind].description }}</span>
                            <img :src="specialMediumList[index].medium[ind].imageFullPath" alt="">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="right">
                      <a-textarea id="special" placeholder="请输入消息内容" :rows="4" v-model="val.welcomeContent"/>
                      <a-button style="marginTop: 20px; marginBottom: 20px" type="primary" @click="insert('特殊', ind, index)">客户名称</a-button>
                      <a-alert message="提示：火狐浏览器可能出现无法正确插入客户名称，请使用谷歌、360浏览器" type="info"/>
                      <a-popover style="marginTop: 20px" v-if="specialMediumList[index].medium[ind].mediumTitle == '' || specialMediumList[index].medium[ind].mediumTitle == undefined">
                        <template slot="content">
                          <a-button icon="file-image" @click="specialImgBoxModal('图片', index, ind)">
                            图片
                          </a-button>
                          <a-button icon="link" @click="specialImgBoxModal('图文', index, ind)">
                            图文
                          </a-button>
                          <a-button icon="paper-clip" @click="specialImgBoxModal('小程序', index, ind)">
                            小程序
                          </a-button>
                        </template>
                        <a-button>
                          +添加图片/图文/小程序
                        </a-button>
                      </a-popover>
                      <div class="medium-btn" v-else>
                        <a-icon type="link" /> <span>{{ specialMediumList[index].medium[ind].mediumTitle }}</span> <a-icon type="close" @click="specialCloseMedium(index, ind)"/>
                      </div>
                    </div>
                  </div>
                </div>
                <a-button v-if="specialMessage.status == 1" @click="addSpecialMessageTime(index)">添加时间段</a-button>
              </div>
            </a-tab-pane>
          </a-tabs>
        </a-tab-pane>
      </a-tabs>
    </a-card>
    <div class="define-btn">
      <router-link :to="{path: '/channelCode/index'}">
        <a-button>取消</a-button>
      </router-link>
      <a-button v-permission="'/channelCode/store@confirm'" size="large" type="primary" :loading="btnLoading" @click="defineChannelCode">确定</a-button>
    </div>
    <div class="pbox" ref="pbox">
      <a-modal
        :getContainer="() => $refs.pbox"
        width="900px"
        title="选择素材"
        :visible="imgModal"
        @cancel="() => { this.imgModal = false}">
        <a-row :gutter="16">
          <a-col :lg="8">
            <a-select v-model="mediumGroupId" @change="changeGroup" style="width: 100%">
              <a-select-option v-for="item in mediumGroupList" :key="item.id">
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :lg="8">
            <a-input-search
              placeholder="输入要搜索的内容"
              enter-button="搜索"
              v-model="searchStr"
              @search="getMaterialLibrary"
            />
          </a-col>
          <a-col :lg="3">
            <a-button @click="() => {this.searchStr = ''; this.getMaterialLibrary()}">清空</a-button>
          </a-col>
          <a-col :lg="3">
            <a-button icon="download" @click="uploadModal">本地上传</a-button>
          </a-col>
        </a-row>
        <div class="picture-box" v-if="mediumType === 2">
          <div class="nothing" v-if="materialData && materialData.length == 0">
            暂无数据
          </div>
          <div :class="mediumId === item.id ? 'active' : null" v-else v-for="item in materialData" :key="item.id" @click="definedMedium(item.id, item.content)">
            <img :src="item.content.imageFullPath" alt="">
            <span>{{ item.content.imageName }}</span>
          </div>
        </div>
        <template slot="footer">
          <a-button @click="resetImage">取消</a-button>
          <a-button type="primary">确定</a-button>
        </template>
      </a-modal>
    </div>
    <div class="tbox" ref="tbox">
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
          <a-button @click="() => { this.localUploadModal = false; this.imgModal = true; this.isSync = 1; this.imgUrl = '' }">取消</a-button>
          <a-button type="primary" @click="uploadImage">确定</a-button>
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
            v-model="searchStr"
            placeholder="输入要搜索的内容"
            enter-button="搜索"
            @search="getMaterialLibrary"
          />
        </a-col>
        <a-col :lg="3">
          <a-button @click="() => {this.searchStr = ''; this.getMaterialLibrary()}">清空</a-button>
        </a-col>
      </a-row>
      <div class="picture-box">
        <div class="nothing" v-if="materialData && materialData.length == 0">
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
  </div>
</template>

<script>
import moment from 'moment'
import upload from './components/upload'
import Department from '@/components/department'
import { getContactTagGroup, addContactTagGroup, addContactTag } from '@/api/workContactTag'
import { allTag } from '@/api/workContact'
import { channelCodeGroup, department, channelCodeAdd, channelCodeDetail, channelCodeUpdate } from '@/api/channelCode'
import { materialLibraryList, mediumGroup, addMaterialLibrary } from '@/api/mediumGroup'
export default {
  components: {
    Department,
    upload
  },
  data () {
    return {
      btnText: '展开',
      isEditEmp: false,
      codeDetail: {},
      channelCodeId: '',
      imgModal: false,
      localUploadModal: false,
      btnLoading: false,
      imageTextModal: false,
      appletsModal: false,
      importModal: false,
      replaceFields: {
        title: 'name',
        key: 'id',
        children: 'son',
        value: 'id'
      },
      columns: [
        {
          align: 'center',
          title: '名称',
          dataIndex: 'employeeName'
        },
        {
          align: 'center',
          title: '上限',
          dataIndex: 'max',
          scopedSlots: { customRender: 'max' }
        }
      ],
      // 欢迎语
      welcomeMessage: {
        scanCodePush: 1,
        messageDetail: []
      },
      // 通用欢迎语
      currencyMessage: {
        type: 1,
        welcomeContent: '',
        mediumId: ''
      },
      // 周期欢迎语
      cycleMessage: {
        type: 2,
        status: 2,
        detail: [
          {
            key: '周期欢迎语',
            chooseCycle: [],
            timeSlot: [
              {
                welcomeContent: '',
                mediumId: '',
                startTime: '',
                endTime: ''
              }
            ]
          }
        ]
      },
      // 特殊时期欢迎语
      specialMessage: {
        type: 3,
        status: 2,
        detail: [
          {
            key: '特殊时期欢迎语',
            startDate: '',
            endDate: '',
            dataString: [],
            timeSlot: [
              {
                welcomeContent: '',
                mediumId: '',
                startTime: '',
                endTime: ''
              }
            ]
          }
        ]
      },
      weekList: [
        {
          label: '周一',
          value: 1
        },
        {
          label: '周二',
          value: 2
        },
        {
          label: '周三',
          value: 3
        },
        {
          label: '周四',
          value: 4
        },
        {
          label: '周五',
          value: 5
        },
        {
          label: '周六',
          value: 6
        },
        {
          label: '周日',
          value: 0
        }
      ],

      // 周期欢迎语下标
      cycleTabIndex: 1,
      // 特殊时期欢迎语下标
      specialTabIndex: 1,
      // 基础设置
      baseInfo: {},
      // 标签弹窗
      tagModal: false,
      // 标签组弹窗
      tagGroupModal: false,
      // 添加成员弹窗
      memberModal: false,
      // 引流成员设置
      drainageEmployee: {
        type: 1,
        employees: [],
        specialPeriod: {
          status: 2
        },
        addMax: {
          status: 2
        }
      },
      oneEmployeeValue: {
        key: '',
        label: ''
      },
      checked1: 1,
      // 周期tag
      weekTagList: [
        {
          name: '周一',
          id: 1,
          isChecked: 2
        },
        {
          name: '周二',
          id: 2,
          isChecked: 2
        },
        {
          name: '周三',
          id: 3,
          isChecked: 2
        },
        {
          name: '周四',
          id: 4,
          isChecked: 2
        },
        {
          name: '周五',
          id: 5,
          isChecked: 2
        },
        {
          name: '周六',
          id: 6,
          isChecked: 2
        },
        {
          name: '周日',
          id: 0,
          isChecked: 2
        }
      ],
      // 周期欢迎语Tag
      cycleWeekTagList: [[
        {
          name: '周一',
          id: 1,
          isChecked: 2
        },
        {
          name: '周二',
          id: 2,
          isChecked: 2
        },
        {
          name: '周三',
          id: 3,
          isChecked: 2
        },
        {
          name: '周四',
          id: 4,
          isChecked: 2
        },
        {
          name: '周五',
          id: 5,
          isChecked: 2
        },
        {
          name: '周六',
          id: 6,
          isChecked: 2
        },
        {
          name: '周日',
          id: 0,
          isChecked: 2
        }
      ]],
      // 选中周期
      checkedWeek: [],
      // 周期欢迎语选择周期
      cycleCheckedWeek: [[]],
      // 标签分组
      tagGroupList: [],
      // 标签列表
      tagList: [],
      edit: false,
      // 选中的标签
      checkedTags: [],
      // 新建分组名称
      newGroupName: '',
      // 新建标签名称
      newTagsName: '',
      newTagsGroupId: '',
      // 渠道码分组
      channelCodeGroup: [],
      baseInfoRules: {
        groupId: [
          { required: true, message: '请选择分组', trigger: 'change' }
        ],
        name: [{ required: true, message: '请输入活码名称', trigger: 'blur' }],
        autoAddFriend: [{ required: true, message: '请选择是否自动添加好友', trigger: 'change' }]
      },
      // 成员下拉
      memberList: [],
      // 部门下拉
      departmentList: [],
      // 成员
      employees: [],
      employeeIdList: '',
      choosePeopleShow: false,
      // 选中部门
      selectDepartment: [],
      // 单人 添加singleTimeList时间
      singleTimeList: [
        {
          startTime: '00:00',
          endTime: '00:00',
          employeeId: '',
          selectMembers: [],
          departmentId: [],
          employeeSelect: {
            label: '',
            key: ''
          }
        }
      ],
      manyTimeList: [
        {
          startTime: '00:00',
          endTime: '00:00',
          employeeId: '',
          selectMembers: [],
          departmentId: [],
          departmentSelect: [],
          employeeSelect: {
            label: '',
            key: ''
          }
        }
      ],
      manyTimeIndex: null,
      dataList: [],
      specialList: [],
      // 特殊时期添加
      specialTimeList: [
        {
          dataString: [],
          startDate: '',
          endDate: '',
          timeSlot: [
            {
              startTime: '00:00',
              endTime: '00:00',
              employeeId: '',
              selectMembers: [],
              departmentId: [],
              employeeSelect: {
                label: '',
                key: ''
              }
            }
          ]
        }
      ],
      addSpecialList: [],
      specialIndex: null,
      specialPickerIndex: null,
      specialTimeSlotIndex: null,
      allMemderList: [],
      // 素材类型
      mediumType: '',
      // 素材分组
      mediumGroupId: '',
      // 素材列表
      materialData: [],
      // 素材分组列表
      mediumGroupList: [],
      // 素材搜索
      searchStr: '',
      // 素材展示详情
      mediumDetail: {},
      // 选中图片素材id
      mediumId: '',
      // 展示素材名称
      mediumTitle: '',
      // 下载时存储数据
      upLoadRes: {},
      // 图片展示
      imgUrl: '',
      // 是否同步
      isSync: 1,
      // 是否导入
      isImport: 0,
      // 图文数据
      imageTextData: {},
      // 小程序数据
      appletsData: {},
      // 是否是图文
      isImageText: false,
      // 是否是小程序
      isApplets: false,
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
      tabNum: '1',
      // 周期欢迎语下标
      cycleMessageIndex: 0,
      // 周期欢迎语时间段下标
      cycleTimeIndex: 0,
      // 周期欢迎语素材展示
      cycleMediumList: [
        {
          medium: [
            {
              title: '',
              imageFullPath: '',
              type: '',
              description: '',
              mediumTitle: ''
            }
          ]
        }
      ],
      // 特殊时期欢迎语下标
      specialMessageIndex: 0,
      // 特殊时期欢迎语时间段下标
      specialTimeIndex: 0,
      // 特殊时期欢迎语
      specialMediumList: [
        {
          medium: [
            {
              title: '',
              imageFullPath: '',
              type: '',
              description: '',
              mediumTitle: ''
            }
          ]
        }
      ],
      editWeekList: [],
      isEdit: false,
      startTime: null,
      endTime: null,
      spareEmployeeDis: false,
      btnTextDis: true
    }
  },
  created () {
    this.getChannelCodeGroup()
    this.getTagGroups()
    this.getDepartment()
    this.channelCodeId = this.$route.query.channelCodeId
    if (this.channelCodeId != undefined) {
      this.getChannelCodeDetail()
      this.isEdit = true
    }
  },
  watch: {
    specialTimeList: {
      handler (val) {
        const list = val
        let flagTime = false
        if (list.length > 1) {
          const isCross = this.isCrossData(list, list[list.length - 1])
          if (isCross) {
            flagTime = true
          }
        }
        if (flagTime) {
          return this.$message.error('日期存在冲突')
        }
        let flag = false
        val.map(v => {
          v.timeSlot.map(item => {
            if (item.employeeId !== '' || item.employeeId.length != 0) {
              flag = true
            }
          })
        })
        if (flag) {
          const arr = []
          let obj = {}
          this.specialTimeList.map(item => {
            if (this.drainageEmployee.type == 1) {
              item.timeSlot.map(val => {
                obj = {
                  employeeName: val.employeeSelect != undefined ? val.employeeSelect.label : val.selectMembers.join(' '),
                  employeeId: val.employeeSelect != undefined ? val.employeeSelect.key : val.employeeId.join(' '),
                  max: ''
                }
                arr.push(obj)
              })
            } else {
              let list = []
              item.timeSlot.map(val => {
                list = val.employeeId.length != 0 ? val.employeeId : []
                if (list.length > 0) {}
                list.map((v, i) => {
                  obj = {
                    employeeId: v,
                    employeeName: val.selectMembers[i],
                    max: ''
                  }
                  arr.push(obj)
                })
              })
            }
          })
          this.allMemderList = this.allMemderList.concat(arr)
          const newArr = []
          const obs = {}
          for (let i = 0; i < this.allMemderList.length; i++) {
            if (!obs[this.allMemderList[i].employeeId]) {
              newArr.push(this.allMemderList[i])
              obs[this.allMemderList[i].employeeId] = true
            }
          }
          this.allMemderList = newArr
        }
      },
      deep: true
    }
  },
  methods: {
    moment: moment,
    onChange () {
      this.$forceUpdate()
    },
    clickMore () {
      if (this.btnText == '展开') {
        this.btnText = '收起'
      } else {
        this.btnText = '展开'
      }
    },
    changeTime (val, dateStrings, type) {
      if (type === 'startTime') {
        this.startTime = dateStrings
      } else {
        this.endTime = dateStrings
      }
    },
    getDisabledHours () {
      const hours = []
      const time = this.startTime
      const timeArr = time.split(':')
      for (let i = 0; i < (parseInt(timeArr[0]) + 1); i++) {
        hours.push(i)
      }
      return hours
    },
    getDisabledSeconds (selectedHour, selectedMinute) {
      const time = this.startTime
      const timeArr = time.split(':')
      const second = []
      if (selectedHour == (parseInt(timeArr[0]) + 1) && selectedMinute == parseInt(timeArr[1])) {
        for (let i = 0; i <= parseInt(timeArr[2]); i++) {
          second.push(i)
        }
      }
      return second
    },
    // 详情
    getChannelCodeDetail () {
      channelCodeDetail({
        channelCodeId: this.channelCodeId
      }).then(res => {
        console.log(res)
        const { baseInfo, drainageEmployee, welcomeMessage } = res.data
        const arr = []
        const Marr = []
        this.baseInfo = baseInfo
        this.drainageEmployee.type = drainageEmployee.type
        this.tagList = baseInfo.tags
        this.checkedTags = baseInfo.selectedTags || []
        this.drainageEmployee.employees = drainageEmployee.employees || []
        drainageEmployee.employees.map(item => {
          if (this.drainageEmployee.type == 1) {
            item.timeSlot.map(v => {
              v.employeeId = Number(v.employeeId.join(' '))
            })
            arr.push(item.timeSlot)
          } else {
            Marr.push(item.timeSlot)
          }
        })
        this.singleTimeList = arr
        this.manyTimeList = Marr
        this.drainageEmployee.addMax.status = drainageEmployee.addMax.status
        this.allMemderList = drainageEmployee.addMax.employees || []
        if (this.drainageEmployee.type == 1) {
          this.drainageEmployee.addMax.spareEmployeeIds = Number(drainageEmployee.addMax.spareEmployeeIds.join(' ')) || ''
        } else {
          this.drainageEmployee.addMax.spareEmployeeIds = drainageEmployee.addMax.spareEmployeeIds
        }
        this.drainageEmployee.specialPeriod.status = drainageEmployee.specialPeriod.status
        this.specialTimeList = drainageEmployee.specialPeriod.detail || []
        this.currencyMessage = welcomeMessage.messageDetail[0]
        this.cycleMessage = welcomeMessage.messageDetail[1]
        this.specialMessage = welcomeMessage.messageDetail[2]
        this.welcomeMessage.scanCodePush = welcomeMessage.scanCodePush
        this.mediumDetail = welcomeMessage.messageDetail[0].content
        if (Object.keys(welcomeMessage.messageDetail[0].content).length == 3) {
          this.mediumDetail.type = 2
          this.mediumDetail.mediumTitle = welcomeMessage.messageDetail[0].content.imageName
        } else if (welcomeMessage.messageDetail[0].content.appId == undefined) {
          this.mediumDetail.type = 3
          this.mediumDetail.mediumTitle = welcomeMessage.messageDetail[0].content.title
        } else if (welcomeMessage.messageDetail[0].content.appId != undefined) {
          this.mediumDetail.type = 6
          this.mediumDetail.mediumTitle = welcomeMessage.messageDetail[0].content.title
        }
        const obj = {
          medium: [
            {
              title: '',
              imageFullPath: '',
              type: '',
              description: '',
              mediumTitle: ''
            }
          ]
        }
        this.cycleWeekTagList = []
        if (welcomeMessage.messageDetail[1].detail.length !== 0) {
          welcomeMessage.messageDetail[1].detail.map((item, index) => {
            const week = [
              {
                name: '周一',
                id: 1,
                isChecked: 2
              },
              {
                name: '周二',
                id: 2,
                isChecked: 2
              },
              {
                name: '周三',
                id: 3,
                isChecked: 2
              },
              {
                name: '周四',
                id: 4,
                isChecked: 2
              },
              {
                name: '周五',
                id: 5,
                isChecked: 2
              },
              {
                name: '周六',
                id: 6,
                isChecked: 2
              },
              {
                name: '周日',
                id: 0,
                isChecked: 2
              }
            ]
            this.cycleMediumList.push(obj)
            this.cycleWeekTagList.push(week)
            if (this.cycleWeekTagList[index].length !== 0) {
              this.cycleWeekTagList[index].map(v => {
                item.chooseCycle.map(val => {
                  if (v.id == val) {
                    v.isChecked = 1
                  }
                })
              })
            }
            item.timeSlot.map((val, ind) => {
              this.cycleMediumList[index].medium[ind] = val.content
              if (Object.keys(val.content).length == 3) {
                this.cycleMediumList[index].medium[ind].type = 2
                this.cycleMediumList[index].medium[ind].mediumTitle = val.content.imageName
              } else if (val.content.appId == undefined) {
                this.cycleMediumList[index].medium[ind].type = 3
                this.cycleMediumList[index].medium[ind].mediumTitle = val.content.title
              } else {
                this.cycleMediumList[index].medium[ind].type = 6
                this.cycleMediumList[index].medium[ind].mediumTitle = val.content.title
              }
            })
          })
        }
        if (welcomeMessage.messageDetail[2].detail.length !== 0) {
          welcomeMessage.messageDetail[2].detail.map((item, index) => {
            this.specialMediumList.push(obj)
            item.timeSlot.map((val, ind) => {
              this.specialMediumList[index].medium[ind] = val.content
              if (Object.keys(val.content).length == 3) {
                this.specialMediumList[index].medium[ind].type = 2
              } else if (val.content.appId == undefined) {
                this.specialMediumList[index].medium[ind].type = 3
                this.specialMediumList[index].medium[ind].mediumTitle = val.content.title
              } else {
                this.specialMediumList[index].medium[ind].type = 6
                this.specialMediumList[index].medium[ind].mediumTitle = val.content.title
              }
            })
          })
        }
      })
    },
    // 成员下拉
    getDepartment () {
      department().then(res => {
        this.memberList = res.data.employee
        this.departmentList = res.data.department
      })
    },
    // 渠道码分组
    getChannelCodeGroup () {
      channelCodeGroup().then(res => {
        this.channelCodeGroup = res.data
      })
    },
    // 标签分组
    async getTagGroups () {
      try {
        const { data } = await getContactTagGroup()
        this.tagGroupList = data
        this.tagList = data.map(item => {
          return {
            ...item,
            list: []
          }
        })
        if (this.tagList.length > 5) {
          this.btnTextDis = true
          this.btnText = '展开'
        } else {
          this.btnTextDis = false
          this.btnText = '收起'
        }
      } catch (e) {
        console.log(e)
      }
    },
    // 获取所在分组标签
    async chooseTagGroup (groupId) {
      if (this.edit) {
        return
      }
      const tags = this.tagList.filter(item => {
        return item.groupId == groupId
      })[0]
      const param = {
        groupId: groupId
      }
      try {
        const { data } = await allTag(param)
        tags.list = data.map(item => {
          return {
            tagId: item.id,
            tagName: item.name,
            isSelected: 2
          }
        })
      } catch (e) {
        console.log(e)
      }
    },
    // 选择标签
    chooseTag (item, tagId) {
      const index = this.checkedTags.indexOf(tagId)
      if (index > -1) {
        this.checkedTags.splice(index, 1)
      } else {
        this.checkedTags.push(tagId)
      }
      item.list.filter(inner => {
        if (inner.tagId == tagId) {
          inner.isSelected = index > -1 ? 2 : 1
          return true
        }
      })
    },
    // 新建标签
    addNewTag (id) {
      this.tagModal = true
      this.newTagsGroupId = id
    },
    addGroupTags () {
      if (this.newTagsName == '') {
        this.$message.error('请输入标签名称')
        return
      }
      const reg = /\s+/g
      const name = this.newTagsName.replace(reg, ' ').split(' ')
      let falg = false
      name.map(item => {
        if (item.length > 15) {
          this.$message.error('每个标签最多15个字')
          falg = true
        }
      })
      if (falg) {
        return
      }
      this.btnLoading = true
      addContactTag({
        groupId: this.newTagsGroupId,
        tagName: name
      }).then(res => {
        this.btnLoading = false
        this.newTagsName = ''
        this.tagModal = false
        this.chooseTagGroup(this.newTagsGroupId)
      })
    },
    // 新建标签分组
    addGroup () {
      if (this.newGroupName == '') {
        this.$message.error('请输入分组名称')
        return
      } else if (this.newGroupName.length > 15) {
        this.$message.error('分组名称不得超过15个字符')
        return
      }
      this.btnLoading = true
      addContactTagGroup({
        groupName: this.newGroupName
      }).then(res => {
        this.btnLoading = false
        this.tagGroupModal = false
        this.newGroupName = ''
        this.getTagGroups()
      })
    },
    // 成员选择
    peopleChange (data) {
      const arr = []
      const empName = []
      data.map(item => {
        arr.push(item.employeeId)
        empName.push(item.employeeName)
      })
      this.employeeIdList = arr.join(',')
      if (this.drainageEmployee.type == 2 && this.manyTimeIndex != null) {
        console.log(44)
        this.manyTimeList[this.manyTimeIndex].employeeId = JSON.parse(JSON.stringify(arr))
        this.manyTimeList[this.manyTimeIndex].selectMembers = JSON.parse(JSON.stringify(empName))
      }
      if (this.drainageEmployee.specialPeriod.status == 1 && this.specialPickerIndex != null) {
        console.log(45)
        this.specialTimeList[this.specialPickerIndex].timeSlot[this.specialTimeSlotIndex].employeeId = JSON.parse(JSON.stringify(arr))
        this.specialTimeList[this.specialPickerIndex].timeSlot[this.specialTimeSlotIndex].selectMembers = JSON.parse(JSON.stringify(empName))
      }
      if (this.drainageEmployee.addMax.status == 1 && this.spareEmployeeDis) {
        console.log(55)
        this.drainageEmployee.addMax.spareEmployeeIds = this.employeeIdList
      }
    },
    searchEmp (data) {
      if (data.length === 0) {
        this.employeeIdList = '空'
      } else {
        this.employeeIdList = data[0].id
      }
    },
    /* 欢迎语 start */
    tabChange (value) {
      this.tabNum = value
      this.imageTextData = {}
      this.appletsData = {}
      this.imgUrl = ''
      this.mediumId = ''
    },
    /* 周期欢迎语 tabs 增加 删除 start */
    onEdit (targetKey, action) {
      this[action](targetKey)
    },
    add () {
      this.imgUrl = ''
      this.mediumId = ''
      this.cycleTabIndex++
      const tabsKeys = `周期欢迎语${this.cycleTabIndex}`
      const obj = {
        key: tabsKeys,
        chooseCycle: [],
        timeSlot: []
      }
      const obj1 = {
        medium: [
          {
            title: '',
            imageFullPath: '',
            type: '',
            description: '',
            mediumTitle: ''
          }
        ]
      }
      const tagWeek = [
        {
          name: '周一',
          id: 1,
          isChecked: 2
        },
        {
          name: '周二',
          id: 2,
          isChecked: 2
        },
        {
          name: '周三',
          id: 3,
          isChecked: 2
        },
        {
          name: '周四',
          id: 4,
          isChecked: 2
        },
        {
          name: '周五',
          id: 5,
          isChecked: 2
        },
        {
          name: '周六',
          id: 6,
          isChecked: 2
        },
        {
          name: '周日',
          id: 0,
          isChecked: 2
        }
      ]
      this.cycleWeekTagList.push(tagWeek)
      this.cycleMediumList.push(obj1)
      this.cycleMessage.detail.push(obj)
      this.cycleCheckedWeek.push([])
      this.imageTextData = {}
      this.appletsData = {}
    },
    remove (targetKey) {
      this.imgUrl = ''
      this.mediumId = ''
      this.cycleTabIndex--
      this.cycleMediumList.slice(this.cycleTabIndex, 0)
      const list = this.cycleMessage.detail.filter(item => item.key !== targetKey)
      list.map((val, index) => {
        if (index !== 0) {
          val.chooseCycle = []
          val.timeSlot = []
          val.key = '周期欢迎语' + (index + 1)
        }
      })
      this.cycleMessage.detail = list
    },
    /* 周期欢迎语 tabs 增加 删除 end */
    /* 特殊时期欢迎语 tabs 增加 删除 start */
    onEdits (targetKey, action) {
      this[action + 's'](targetKey)
    },
    adds () {
      this.imgUrl = ''
      this.mediumId = ''
      this.specialTabIndex++
      const tabsKeys = `特殊时期欢迎语${this.specialTabIndex}`
      const obj = {
        key: tabsKeys,
        startDate: '',
        endDate: '',
        timeSlot: []
      }
      const obj1 = {
        medium: [
          {
            title: '',
            imageFullPath: '',
            type: '',
            description: '',
            mediumTitle: ''
          }
        ]
      }
      this.specialMediumList.push(obj1)
      this.specialMessage.detail.push(obj)
      this.specialCheckedWeek = []
      this.imageTextData = {}
      this.appletsData = {}
    },
    removes (targetKey) {
      this.imgUrl = ''
      this.mediumId = ''
      this.specialTabIndex--
      this.specialMediumList.slice(this.specialTabIndex, 0)
      const list = this.specialMessage.detail.filter(item => item.key !== targetKey)
      list.map((val, index) => {
        if (index !== 0) {
          val.startDate = ''
          val.endDate = ''
          val.timeSlot = []
          val.key = '特殊时期欢迎语' + (index + 1)
        }
      })
      this.cycleMessage.detail = list
    },
    /* 特殊时期欢迎语 tabs 增加 删除 end */
    // 周期添加时间段
    addCycleTime (index) {
      this.imgUrl = ''
      this.mediumId = ''
      this.imageTextData = {}
      this.appletsData = {}
      const obj = {
        welcomeContent: '',
        mediumId: '',
        startTime: '',
        endTime: ''
      }
      const obj1 = {
        title: '',
        imageFullPath: '',
        type: '',
        description: '',
        mediumTitle: ''
      }
      this.cycleMessage.detail[index].timeSlot.push(obj)
      this.cycleMediumList[index].medium.push(obj1)
    },
    // 特殊时期添加时间段
    addSpecialMessageTime (index) {
      this.imgUrl = ''
      this.mediumId = ''
      this.imageTextData = {}
      this.appletsData = {}
      const obj = {
        welcomeContent: '',
        mediumId: '',
        startTime: '',
        endTime: ''
      }
      const obj1 = {
        title: '',
        imageFullPath: '',
        type: '',
        description: '',
        mediumTitle: ''
      }
      this.specialMessage.detail[index].timeSlot.push(obj)
      this.specialMediumList[index].medium.push(obj1)
    },
    // 确定欢迎语下标
    defineDateIndex (index) {
      this.specialMessageIndex = index
    },
    // 特殊时期欢迎语 日期
    specialDate (date, strDate) {
      this.specialMessage.detail[this.specialMessageIndex].startDate = strDate[0]
      this.specialMessage.detail[this.specialMessageIndex].endDate = strDate[1]
    },
    imgBoxModal (type) {
      if (type == '图片') {
        this.imgModal = true
        this.mediumType = 2
      } else if (type == '图文') {
        this.imageTextModal = true
        this.mediumType = 3
      } else if (type == '小程序') {
        this.appletsModal = true
        this.mediumType = 6
      }
      this.getMaterialLibrary()
    },
    cycleImgBoxModal (type, pIndex, index) {
      this.cycleMessageIndex = pIndex
      this.cycleTimeIndex = index
      if (type == '图片') {
        this.imgModal = true
        this.mediumType = 2
      } else if (type == '图文') {
        this.imageTextModal = true
        this.mediumType = 3
      } else if (type == '小程序') {
        this.appletsModal = true
        this.mediumType = 6
      }
      this.imgUrl = ''
      this.getMaterialLibrary()
    },
    specialImgBoxModal (type, pIndex, index) {
      this.specialMessageIndex = pIndex
      this.specialTimeIndex = index
      if (type == '图片') {
        this.imgModal = true
        this.mediumType = 2
      } else if (type == '图文') {
        this.imageTextModal = true
        this.mediumType = 3
      } else if (type == '小程序') {
        this.appletsModal = true
        this.mediumType = 6
      }
      this.getMaterialLibrary()
    },
    // 插入客户名称
    insert (type, index, pIndex) {
      if (type == '通用') {
        const textarea = document.getElementById('currency')
        const start = textarea.selectionStart// input 第0个字符到选中的字符
        this.currencyMessage.welcomeContent = this.currencyMessage.welcomeContent.slice(0, start) + '##客户名称##' + this.currencyMessage.welcomeContent.slice(start)
      } else if (type == '周期') {
        const textarea = document.getElementById('cycle')
        const start = textarea.selectionStart// input 第0个字符到选中的字符
        this.cycleMessage.detail[pIndex].timeSlot[index].welcomeContent = this.cycleMessage.detail[pIndex].timeSlot[index].welcomeContent.slice(0, start) + '##客户名称##' + this.cycleMessage.detail[pIndex].timeSlot[index].welcomeContent.slice(start)
      } else if (type == '特殊') {
        const textarea = document.getElementById('special')
        const start = textarea.selectionStart// input 第0个字符到选中的字符
        this.specialMessage.detail[pIndex].timeSlot[index].welcomeContent = this.specialMessage.detail[pIndex].timeSlot[index].welcomeContent.slice(0, start) + '##客户名称##' + this.specialMessage.detail[pIndex].timeSlot[index].welcomeContent.slice(start)
      }
    },
    // 获取素材库
    getMaterialLibrary () {
      materialLibraryList({
        mediumGroupId: this.mediumGroupId,
        searchStr: this.searchStr,
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
    // 图片弹框 选择素材
    definedMedium (id, content) {
      this.imgModal = false
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
        this.mediumDetail.mediumTitle = content.imageName
        this.mediumDetail.type = 2
        this.mediumType = 2
        if (this.tabNum == 1) {
          this.currencyMessage.mediumId = this.mediumId
        } else if (this.tabNum == 2) {
          this.cycleMessage.detail[this.cycleMessageIndex].timeSlot[this.cycleTimeIndex].mediumId = this.mediumId
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex] = this.mediumDetail
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].type = 2
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].mediumTitle = this.mediumTitle
        } else if (this.tabNum == 3) {
          this.specialMessage.detail[this.specialMessageIndex].timeSlot[this.specialTimeIndex].mediumId = this.mediumId
          this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex] = this.mediumDetail
          this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].type = 2
          this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].mediumTitle = this.mediumTitle
        }
      }
    },
    // 素材库分组改变
    changeGroup (value) {
      this.mediumGroupId = value
      this.getMaterialLibrary()
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
      this.upLoadRes = {}
      this.imgUrl = ''
    },
    cycleCloseMedium (pIndex, index) {
      this.mediumId = ''
      this.upLoadRes = {}
      this.imgUrl = ''
      this.cycleMediumList[pIndex].medium[index] = {}
      this.cycleMediumList = [...this.cycleMediumList]
    },
    specialCloseMedium (pIndex, index) {
      this.mediumId = ''
      this.upLoadRes = {}
      this.imgUrl = ''
      this.specialMediumList[pIndex].medium[index] = {}
      this.specialMediumList = [...this.specialMediumList]
    },
    // 上传
    uploadSuccess (data) {
      this.imgUrl = data.fullPath
      const imagePath = data.path
      this.upLoadRes.imageFullPath = data.fullPath
      this.upLoadRes.imagePath = imagePath
      this.upLoadRes.imageName = data.name
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
        this.imgModal = false
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
          this.mediumDetail = this.upLoadRes
          this.mediumDetail.mediumTitle = this.upLoadRes.imageName
          this.mediumDetail.imageFullPath = this.upLoadRes.imageFullPath
          this.mediumId = res.data.id
          if (this.tabNum == 1) {
            this.currencyMessage.mediumId = this.mediumId
          } else if (this.tabNum == 2) {
            this.cycleMessage.detail[this.cycleMessageIndex].timeSlot[this.cycleTimeIndex].mediumId = this.mediumId
            this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex] = this.mediumDetail
            this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].type = 2
            this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].imageFullPath = this.upLoadRes.imageFullPath
            this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].mediumTitle = this.mediumTitle
          } else if (this.tabNum == 3) {
            this.specialMessage.detail[this.specialMessageIndex].timeSlot[this.specialTimeIndex].mediumId = this.mediumId
            this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex] = this.mediumDetail
            this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].type = 2
            this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].mediumTitle = this.mediumTitle
          }
        }
      })
    },
    // 打开本地上传
    uploadModal () {
      this.localUploadModal = true
    },
    // 图文或小程序添加图片
    mediumModal (type) {
      this.imgModal = true
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
        if (this.tabNum == 2) {
          this.cycleMessage.detail[this.cycleMessageIndex].timeSlot[this.cycleTimeIndex].mediumId = this.mediumId
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex] = this.mediumDetail
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].type = 3
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].imageFullPath = this.upLoadRes.imageFullPath
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].mediumTitle = this.mediumTitle
        } else if (this.tabNum == 3) {
          this.specialMessage.detail[this.specialMessageIndex].timeSlot[this.specialTimeIndex].mediumId = this.mediumId
          this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex] = this.mediumDetail
          this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].type = 3
          this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].mediumTitle = this.mediumTitle
        }
      } else {
        this.appletsModal = false
        this.mediumDetail.type = 6
        if (this.tabNum == 2) {
          this.cycleMessage.detail[this.cycleMessageIndex].timeSlot[this.cycleTimeIndex].mediumId = this.mediumId
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex] = this.mediumDetail
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].type = 6
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].imageFullPath = this.upLoadRes.imageFullPath
          this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].mediumTitle = this.mediumTitle
        } else if (this.tabNum == 3) {
          this.specialMessage.detail[this.specialMessageIndex].timeSlot[this.specialTimeIndex].mediumId = this.mediumId
          this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex] = this.mediumDetail
          this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].type = 6
          this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].mediumTitle = this.mediumTitle
        }
      }
    },
    // 取消选择素材
    resetImage () {
      this.imgModal = false
      this.mediumDetail = {}
      this.mediumTitle = ''
      this.mediumId = ''
      this.searchStr = ''
      if (this.isImageText) {
        this.imageTextModal = true
      } else if (this.isApplets) {
        this.appletsModal = true
      } else {
        this.imgModal = false
      }
    },
    // 取消导入
    resetImport () {
      this.importModal = false
      this.mediumDetail = {}
      this.mediumTitle = ''
      this.mediumId = ''
      this.searchStr = ''
      if (this.mediumType == 3) {
        this.imageTextModal = true
      } else {
        this.appletsModal = true
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
    // 添加方式切换
    importChange () {
      this.imageTextData = {}
      this.appletsData = {}
      this.imgUrl = ''
      this.upLoadRes = {}
      this.mediumDetail = {}
      this.mediumTitle = ''
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
              this.mediumDetail.mediumTitle = content.title
              this.imageTextModal = false
              if (this.tabNum == 1) {
                this.currencyMessage.mediumId = this.mediumId
              } else if (this.tabNum == 2) {
                this.cycleMessage.detail[this.cycleMessageIndex].timeSlot[this.cycleTimeIndex].mediumId = this.mediumId
                this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex] = this.mediumDetail
                this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].type = 3
                this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].imageFullPath = this.upLoadRes.imageFullPath
                this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].mediumTitle = this.mediumTitle
              } else if (this.tabNum == 3) {
                this.specialMessage.detail[this.specialMessageIndex].timeSlot[this.specialTimeIndex].mediumId = this.mediumId
                this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex] = this.mediumDetail
                this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].type = 3
                this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].mediumTitle = this.mediumTitle
              }
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
              this.mediumDetail = content
              this.mediumDetail.imageFullPath = this.upLoadRes.imageFullPath
              this.mediumDetail.type = 6
              this.mediumDetail.mediumTitle = content.title
              this.appletsModal = false
              if (this.tabNum == 1) {
                this.currencyMessage.mediumId = this.mediumId
              } else if (this.tabNum == 2) {
                this.cycleMessage.detail[this.cycleMessageIndex].timeSlot[this.cycleTimeIndex].mediumId = this.mediumId
                this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex] = this.mediumDetail
                this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].type = 6
                this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].imageFullPath = this.upLoadRes.imageFullPath
                this.cycleMediumList[this.cycleMessageIndex].medium[this.cycleTimeIndex].mediumTitle = this.mediumTitle
              } else if (this.tabNum == 3) {
                this.specialMessage.detail[this.specialMessageIndex].timeSlot[this.specialTimeIndex].mediumId = this.mediumId
                this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex] = this.mediumDetail
                this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].type = 6
                this.specialMediumList[this.specialMessageIndex].medium[this.specialTimeIndex].mediumTitle = this.mediumTitle
              }
            })
          } else {
            console.log('error submit!!')
            return false
          }
        })
      }
    },
    // 周期欢迎语 选择周期
    cycleWeek (id, ind) {
      const index = this.cycleMessage.detail[ind].chooseCycle.indexOf(id)
      if (index > -1) {
        this.cycleMessage.detail[ind].chooseCycle.splice(index, 1)
      } else {
        this.cycleMessage.detail[ind].chooseCycle.push(id)
      }
      this.cycleWeekTagList[ind].filter(item => {
        if (item.id == id) {
          item.isChecked = index > -1 ? 2 : 1
          return true
        }
      })
    },
    // 导入选择
    mediumImport (id, content) {
      this.mediumId = id
      this.mediumTitle = content.title
      this.mediumDetail = content
      this.mediumDetail.imageFullPath = content.imageFullPath
    },
    cycleDelete (pIndex, index) {
      this.cycleMessage.detail[pIndex].timeSlot.splice(index, 1)
    },
    specialDelete (pIndex, index) {
      this.specialMessage.detail[pIndex].timeSlot.splice(index, 1)
    },
    /* 欢迎语 end */

    /* 引流成员 start */
    // 点击企业成员 获取下标
    recordIndex (index, type) {
      this.manyTimeIndex = index
      this.specialIndex = index
      this.specialPickerIndex = index
      if (type == '人员') {
        this.choosePeopleShow = true
      } else if (type == '备用人员') {
        this.choosePeopleShow = true
        this.spareEmployeeDis = true
        this.manyTimeIndex = null
        this.specialPickerIndex = null
      }
      this.employeeIdList = ''
      this.employees = []
    },
    // 类型改变
    typeChange (value) {
      this.drainageEmployee.employees = []
      this.singleTimeList = [
        {
          employeeId: [],
          departmentId: [],
          startTime: '00:00',
          endTime: '00:00',
          employeeSelect: {
            label: '',
            key: ''
          },
          selectMembers: ''
        }
      ]
      this.manyTimeList = [
        {
          employeeId: [],
          departmentId: [],
          startTime: '00:00',
          endTime: '00:00',
          departmentSelect: [],
          employeeSelect: {
            label: '',
            key: ''
          },
          selectMembers: ''
        }
      ]
    },
    // 选择周期
    chooseWeekTag (id) {
      const index = this.checkedWeek.indexOf(id)
      if (index > -1) {
        this.checkedWeek.splice(index, 1)
      } else {
        this.checkedWeek.push(id)
      }
      this.weekTagList.filter(item => {
        if (item.id == id) {
          item.isChecked = index > -1 ? 2 : 1
          return true
        }
      })
    },
    // 添加单人企业成员
    addSingleTime () {
      const obj = {
        employeeId: [],
        departmentId: [],
        startTime: '',
        endTime: '',
        employeeSelect: {
          label: '',
          key: ''
        },
        selectMembers: ''
      }
      this.singleTimeList.push(obj)
    },
    // 添加多人企业成员
    addManyTime () {
      const obj = {
        employeeId: [],
        departmentId: [],
        startTime: '',
        endTime: '',
        selectDepartment: [],
        departmentSelect: [],
        selectMembers: []
      }
      this.manyTimeList.push(obj)
    },
    // 单人 删除
    deleteSingle (index, type) {
      if (type == '单人') {
        this.singleTimeList.splice(index, 1)
      } else {
        this.manyTimeList.splice(index, 1)
      }
    },
    // 编辑
    editEmployess (data, pIndex, index) {
      this.isEditEmp = true
      const week = data.week
      this.checkedWeek.push(week)
      this.weekTagList.map(item => {
        if (item.id == week) {
          item.isChecked = 1
        }
      })
      this.memberModal = true
      if (this.drainageEmployee.type == 1) {
        this.singleTimeList = JSON.parse(JSON.stringify(data.timeSlot))
      } else {
        this.manyTimeList = JSON.parse(JSON.stringify(data.timeSlot))
      }
    },
    // 引流添加确定
    drainageDefine () {
      let flag = false
      let falg = false
      if (this.drainageEmployee.type == 1) {
        const list = this.singleTimeList
        let flagTime = false
        if (list.length > 1) {
          const isCross = this.isCrossTime(list, list[list.length - 1])
          if (isCross) {
            flagTime = true
          }
        }
        if (flagTime) {
          return this.$message.error('时间段存在冲突')
        }
        if (this.isEditEmp) {
          this.singleTimeList.map(item => {
            item.employeeId = item.employeeSelect.key
            item.selectMembers = item.employeeSelect.label
          })
          if (this.checkedWeek.length == 7) {
            let obj = {}
            this.checkedWeek.map(item => {
              obj = {
                week: item,
                timeSlot: JSON.parse(JSON.stringify(this.singleTimeList))
              }
              this.dataList.push(obj)
            })
            this.drainageEmployee.employees = this.dataList
          } else {
            this.drainageEmployee.employees.map(item => {
              this.checkedWeek.map(val => {
                if (item.week == val) {
                  item.timeSlot = JSON.parse(JSON.stringify(this.singleTimeList))
                }
              })
            })
          }
        } else {
          this.singleTimeList.map(item => {
            item.employeeId = item.employeeSelect.key
            item.selectMembers = item.employeeSelect.label
            if (item.employeeId == '') {
              flag = true
            }
          })
          if (flag) {
            this.$message.error('请选择成员')
            return
          }
          if (this.checkedWeek.length == 0) {
            this.$message.error('请选择适用周期')
            return
          }
          let obj = {}
          this.checkedWeek.map(item => {
            obj = {
              week: item,
              timeSlot: this.singleTimeList
            }
            this.dataList.push(obj)
          })
          if (this.drainageEmployee.employees.length !== 0) {
            const arr = []
            this.dataList.map(val => {
              let flag = false
              this.drainageEmployee.employees.map(item => {
                if (val.week == item.week) {
                  flag = true
                  item.timeSlot = val.timeSlot
                }
              })
              if (!flag) {
                arr.push(val)
              }
            })
            this.drainageEmployee.employees = this.drainageEmployee.employees.concat(arr)
          } else {
            this.drainageEmployee.employees = this.dataList
          }
        }
      } else {
        const list = this.manyTimeList
        let flagTime = false
        if (list.length > 1) {
          const isCross = this.isCrossTime(list, list[list.length - 1])
          if (isCross) {
            flagTime = true
          }
        }
        if (flagTime) {
          return this.$message.error('时间段存在冲突')
        }
        if (this.isEditEmp) {
          this.manyTimeList.map(item => {
            const deArr = []
            const name = []
            item.departmentSelect.map(val => {
              deArr.push(val.value)
              name.push(val.label)
            })
            item.departmentId = deArr
            item.selectDepartment = name
          })
          if (this.checkedWeek.length == 7) {
            let obj = {}
            this.checkedWeek.map(item => {
              obj = {
                week: item,
                timeSlot: JSON.parse(JSON.stringify(this.manyTimeList))
              }
              this.dataList.push(obj)
            })
            this.drainageEmployee.employees = this.dataList
          } else {
            this.drainageEmployee.employees.map(item => {
              this.checkedWeek.map(val => {
                if (item.week == val) {
                  item.timeSlot = JSON.parse(JSON.stringify(this.manyTimeList))
                }
              })
            })
          }
        } else {
          this.manyTimeList.map(item => {
            const deArr = []
            const name = []
            item.departmentSelect.map(val => {
              deArr.push(val.value)
              name.push(val.label)
            })
            item.departmentId = deArr
            item.selectDepartment = name
            if (item.employeeId.length == 0 && item.departmentId.length == 0) {
              falg = true
            }
          })
          if (falg) {
            this.$message.error('请选择成员或企业部门')
            return
          }
          if (this.checkedWeek.length == 0) {
            this.$message.error('请选择适用周期')
            return
          }
          let obj = {}
          this.checkedWeek.map(item => {
            obj = {
              week: item,
              timeSlot: this.manyTimeList
            }
            this.dataList.push(obj)
          })
          if (this.drainageEmployee.employees.length !== 0) {
            const arr = []
            this.dataList.map(val => {
              let flag = false
              this.drainageEmployee.employees.map(item => {
                if (val.week == item.week) {
                  flag = true
                  item.timeSlot = val.timeSlot
                }
              })
              if (!flag) {
                arr.push(val)
              }
            })
            this.drainageEmployee.employees = this.drainageEmployee.employees.concat(arr)
          } else {
            this.drainageEmployee.employees = this.dataList
          }
        }
      }
      this.memberModal = false
      this.dataList = []
      this.manyTimeIndex = null
      this.singleTimeList = [
        {
          employeeId: [],
          departmentId: [],
          startTime: '00:00',
          endTime: '00:00',
          employeeSelect: {
            label: '',
            key: ''
          },
          selectMembers: ''
        }
      ]
      this.manyTimeList = [
        {
          startTime: '00:00',
          endTime: '00:00',
          employeeId: '',
          selectMembers: [],
          departmentId: [],
          departmentSelect: [],
          employeeSelect: {
            label: '',
            key: ''
          }
        }
      ]
      this.weekTagList = [
        {
          name: '周一',
          id: 1,
          isChecked: 2
        },
        {
          name: '周二',
          id: 2,
          isChecked: 2
        },
        {
          name: '周三',
          id: 3,
          isChecked: 2
        },
        {
          name: '周四',
          id: 4,
          isChecked: 2
        },
        {
          name: '周五',
          id: 5,
          isChecked: 2
        },
        {
          name: '周六',
          id: 6,
          isChecked: 2
        },
        {
          name: '周日',
          id: 0,
          isChecked: 2
        }
      ]
      this.checkedWeek = []
      const arr = []
      let obj = {}
      this.drainageEmployee.employees.map(item => {
        if (this.drainageEmployee.type == 1) {
          item.timeSlot.map(val => {
            val.employeeId = val.employeeSelect.key
            val.selectMembers = val.employeeSelect.label
            obj = {
              employeeName: val.selectMembers,
              employeeId: val.employeeId,
              max: ''
            }
            arr.push(obj)
          })
        } else {
          item.timeSlot.map(val => {
            val.employeeId.map((v, i) => {
              obj = {
                employeeId: v,
                employeeName: val.selectMembers[i],
                max: ''
              }
              arr.push(obj)
            })
          })
        }
      })
      this.allMemderList = this.allMemderList.concat(arr)
      const newArr = []
      const obs = {}
      for (let i = 0; i < this.allMemderList.length; i++) {
        if (!obs[this.allMemderList[i].employeeId]) {
          newArr.push(this.allMemderList[i])
          obs[this.allMemderList[i].employeeId] = true
        }
      }
      this.allMemderList = newArr
    },
    // addTime (data) {
    //   const list = data
    //   let flagTime = false
    //   if (list.length > 1) {
    //     const isCross = this.isCrossTime(list, list[list.length - 1])
    //     if (isCross) {
    //       flagTime = true
    //     }
    //   }
    //   if (flagTime) {
    //     return this.$message.error('时间段存在冲突')
    //   }
    // },
    isCrossTime (list, item) {
      function transToNum (time) {
        return Number(time.split(':').join(''))
      }
      const startTime = transToNum(item.startTime)
      const endTime = transToNum(item.endTime)
      if (list.length > 2) {
        for (let i = 1; i < list.length; i++) {
          const current = list[i]
          console.log(current)
          const curStartTime = transToNum(current.startTime)
          const curEndTime = transToNum(current.endTime)
          if (startTime < curEndTime || endTime < curStartTime) {
            return true
          }
          return false
        }
      }
    },
    isCrossData (list, item) {
      function transToNum (time) {
        const dateStr = time.replace(/-/g, '/')
        return new Date(Date.parse(dateStr)).getTime()
      }
      const startDate = transToNum(item.startDate)
      const endDate = transToNum(item.endDate)
      for (let i = 0; i < list.length + 1; i++) {
        const current = list[i]
        const curStartDate = transToNum(current.startDate)
        const curEndDate = transToNum(current.endDate)
        if (!isNaN(startDate) && !isNaN(endDate)) {
          if (startDate > curEndDate || endDate < curStartDate) {
            return false
          }
        } else {
          return false
        }
        return true
      }
    },
    // 引流 取消
    resetDrainage () {
      this.memberModal = false
      this.singleTimeList = [
        {
          employeeId: [],
          departmentId: [],
          startTime: '00:00',
          endTime: '00:00',
          employeeSelect: {
            label: '',
            key: ''
          },
          selectMembers: ''
        }
      ]
      this.manyTimeList = [
        {
          startTime: '00:00',
          endTime: '00:00',
          employeeId: '',
          selectMembers: [],
          departmentId: [],
          departmentSelect: [],
          employeeSelect: {
            label: '',
            key: ''
          }
        }
      ]
      this.weekTagList = [
        {
          name: '周一',
          id: 1,
          isChecked: 2
        },
        {
          name: '周二',
          id: 2,
          isChecked: 2
        },
        {
          name: '周三',
          id: 3,
          isChecked: 2
        },
        {
          name: '周四',
          id: 4,
          isChecked: 2
        },
        {
          name: '周五',
          id: 5,
          isChecked: 2
        },
        {
          name: '周六',
          id: 6,
          isChecked: 2
        },
        {
          name: '周日',
          id: 0,
          isChecked: 2
        }
      ]
      this.checkedWeek = []
    },
    // 特殊时期 开关
    onSpecialChange (value) {
      if (value) {
        this.drainageEmployee.specialPeriod.status = 1
      } else {
        this.drainageEmployee.specialPeriod.status = 2
      }
    },
    // 限制数字
    inputNum (value) {
      const reg = /^(0|[1-9][0-9]*)$/
      if (!reg.test(value)) {
        this.$message.error('请输入正确数字')
      }
    },
    // 添加上线
    onAddMaxChange () {
    },
    // 特殊时期删除时间
    deleteSpecialTime (pIndex, index) {
      this.specialTimeList[pIndex].timeSlot.splice(index, 1)
    },
    // 特殊时期删除时期
    deleteSpecial (i) {
      this.specialTimeList.splice(i, 1)
    },
    // 特殊时期时间添加
    addSpecialTime (i) {
      const obj = {
        startTime: '',
        endTime: '',
        employeeId: '',
        selectMembers: [],
        departmentId: [],
        employeeSelect: {
          label: '',
          key: ''
        }
      }
      this.specialTimeList[i].timeSlot.push(obj)
    },
    clickPicker (index) {
      this.specialPickerIndex = index
    },
    // 特殊时期日期
    changeData (data, dataString) {
      this.specialTimeList[this.specialPickerIndex].startDate = dataString[0]
      this.specialTimeList[this.specialPickerIndex].endDate = dataString[1]
    },
    // 选择企业成员
    specialMember (i, index) {
      this.choosePeopleShow = true
      this.specialPickerIndex = i
      this.specialTimeSlotIndex = index
      this.employeeIdList = ''
      this.employees = []
    },
    // 添加时期
    addPeriod () {
      const obj = {
        startDate: '',
        endDate: '',
        timeSlot: [
          {
            startTime: '00:00',
            endTime: '00:00',
            employeeId: '',
            selectMembers: [],
            departmentId: [],
            employeeSelect: {
              label: '',
              key: ''
            }
          }
        ]
      }
      this.specialTimeList.push(obj)
    },
    /* 引流成员 end */
    isCrossMessageTime (list, item) {
      function transToNum (time) {
        return Number(time.split(':').join(''))
      }
      const startTime = transToNum(item.startTime)
      const endTime = transToNum(item.endTime)
      for (let i = 0; i < list.length; i++) {
        const current = list[i]
        const curStartTime = transToNum(current.startTime)
        const curEndTime = transToNum(current.endTime)
        if (startTime > curEndTime || endTime < curStartTime) {
          return false
        }
        return true
      }
    },
    // 创建渠道码
    defineChannelCode () {
      const arr = []
      this.specialTimeList = this.specialTimeList.map(item => {
        if (this.drainageEmployee.type == 1) {
          item.timeSlot = item.timeSlot.map(val => {
            val.employeeId = val.employeeSelect == undefined ? val.employeeId : [val.employeeSelect.key + '']
            val.selectMembers = val.employeeSelect == undefined ? val.selectMembers : val.employeeSelect.label
            return val
          })
        } else {
          item.timeSlot.map(val => {
            val.departmentId.map(v => {
              arr.push(v.value)
            })
            val.departmentId = arr
          })
        }
        return item
      })
      console.log(2, this.specialTimeList)
      const specialPeriod = {
        status: this.drainageEmployee.specialPeriod.status,
        detail: this.specialTimeList
      }
      const addMax = {
        status: this.drainageEmployee.addMax.status,
        employees: this.allMemderList,
        spareEmployeeIds: (this.drainageEmployee.addMax.spareEmployeeIds + '').split(',').map(Number)
      }
      this.drainageEmployee.employees.map(item => {
        if (this.drainageEmployee.type == 1) {
          item.timeSlot.map(val => {
            val.employeeId = (val.employeeId + '').split(',').map(Number)
          })
        }
      })
      if (this.drainageEmployee.employees.length < 7) {
        this.$message.error('周成员缺失！')
        return
      }
      const drainageEmployee = {
        type: this.drainageEmployee.type,
        employees: this.drainageEmployee.employees,
        specialPeriod: specialPeriod,
        addMax: addMax
      }
      this.currencyMessage.mediumId = this.currencyMessage.mediumId + ''
      this.cycleMessage.detail.map(item => {
        item.timeSlot.map(val => {
          val.mediumId = val.mediumId + ''
        })
      })
      this.specialMessage.detail.map(item => {
        item.timeSlot.map(val => {
          val.mediumId = val.mediumId + ''
        })
      })
      let falg = false
      if (this.welcomeMessage.scanCodePush == 1) {
        if (this.currencyMessage.welcomeContent == '') {
          this.$message.error('通用欢迎语内容不能为空')
          return
        }
        if (this.cycleMessage.status == 1) {
          this.cycleMessage.detail.map((item, index) => {
            if (item.chooseCycle.length == 0) {
              this.$message.error(`请填写周期欢迎语${index + 1}的周期！`)
              falg = true
            }
            const list = item.timeSlot
            let flagTime = false
            if (list.length > 1) {
              const isCross = this.isCrossMessageTime(list, list[list.length - 1])
              if (isCross) {
                flagTime = true
                falg = true
              }
            }
            if (flagTime) {
              this.$message.error('周期欢迎语时间段存在冲突')
            }
            item.timeSlot.map((val, ind) => {
              if (val.startTime == '' || val.endTime == '') {
                this.$message.error(`请填写周期欢迎语${index + 1}的时间段${ind + 1}！`)
                falg = true
              }
            })
          })
        }
        if (this.specialMessage.status == 1) {
          this.specialMessage.detail.map((item, index) => {
            if (item.startDate == '' || item.endDate == '') {
              this.$message.error(`请填写特殊时期欢迎语${index + 1}的日期！`)
              falg = true
            }
            const list = item.timeSlot
            let flagTime = false
            if (list.length > 1) {
              const isCross = this.isCrossMessageTime(list, list[list.length - 1])
              if (isCross) {
                flagTime = true
                falg = true
              }
            }
            if (flagTime) {
              this.$message.error('特殊时期欢迎语时间段存在冲突')
            }
            item.timeSlot.map((val, ind) => {
              if (val.startTime == '' || val.endTime == '') {
                this.$message.error(`请填写特殊时期欢迎语${index + 1}的时间段${ind + 1}！`)
                falg = true
              }
            })
          })
        }
      }
      if (falg) {
        return
      }
      const messageDetail = []
      messageDetail[0] = this.currencyMessage
      messageDetail[1] = this.cycleMessage
      messageDetail[2] = this.specialMessage
      const welcomeMessage = {
        scanCodePush: this.welcomeMessage.scanCodePush,
        messageDetail: messageDetail
      }
      this.$refs.baseInfoRules.validate(valid => {
        if (valid) {
          const baseInfo = {
            groupId: this.baseInfo.groupId,
            name: this.baseInfo.name,
            autoAddFriend: this.baseInfo.autoAddFriend,
            tags: this.checkedTags
          }
          this.btnLoading = true
          if (this.isEdit) {
            channelCodeUpdate({
              channelCodeId: this.channelCodeId,
              baseInfo: baseInfo,
              drainageEmployee: drainageEmployee,
              welcomeMessage: welcomeMessage
            }).then(res => {
              this.btnLoading = false
              this.$message.success('修改成功')
              this.$router.push('/channelCode/index')
            }).catch(res => {
              this.btnLoading = false
            })
          } else {
            channelCodeAdd({
              baseInfo: baseInfo,
              drainageEmployee: drainageEmployee,
              welcomeMessage: welcomeMessage
            }).then(res => {
              this.btnLoading = false
              this.$message.success('创建成功')
              this.$router.push('/channelCode/index')
            }).catch(res => {
              this.btnLoading = false
            })
          }
        } else {
          console.log('error submit!!')
          return false
        }
      })
    }
  }
}
</script>

<style lang="less" scoped>
.wrapper {
  .table {
    width: 1200px;
    height: auto;
    margin-left: 80px;
    display: flex;
     border-bottom: 1px solid #E8E8E8;
    div {
      width: 14%;
      display: flex;
      flex-direction: column;
      .title{
        width: 100%;
        height: 50px;
        line-height: 50px;
        text-align: center;
        background: #FAFAFA;
        border-bottom: 1px solid #E8E8E8;
      }
      .content {
        width: 100%;
        min-height: 80px;
        background: #fff;
        padding-bottom: 15px;
        position: relative;
        .anticon {
          position: absolute;
          top: -33px;
          right: 50px;
        }
        div {
          width: 100%;
          display: flex;
          flex-direction: column;
          align-items: center;
          padding: 5px;
          border-bottom: 1px solid #E8E8E8;
          p{
            margin: 0;
          }
          .name {
            width: 100%;
            margin: 0;
            overflow: hidden;
            span {
              display: inline-block;
              width: 100px;
              overflow: hidden;
              text-overflow:ellipsis;
              white-space: nowrap;
            }
          }
          .department {
            width: 100%;
            border: 1px solid #D9D9D9;
            border-radius: 4px;
            height: auto;
            background: #F5F5F5;
            display: flex;
            flex-wrap: wrap;
            span {
              padding: 0 5px;
              width: 100px;
              text-align: center;
              overflow: hidden;
              text-overflow:ellipsis;
              white-space: nowrap;
            }
          }
        }
      }
    }
  }
  .special-box {
    width: 650px;
    height: auto;
    background: #f7f5f5;
    padding: 10px;
    border-bottom: 1px dashed #e8e8e8
  }
  .label{
    width: 800px;
    position: relative;
    border: 1px solid #e8e8e8;
    padding: 15px;
    .tags-active {
      padding: 15px;
      height: auto;
      overflow: auto;
      .tag-item {
        padding-top: 10px;
        padding-left: 10px;
        display: flex;
        flex-wrap: wrap;
        .group-name {
          margin-right: 20px;
          margin-bottom: 5px;
          flex: 0 0 100px;
        }
        .tag-content {
          flex: 1
        }
        .tag {
          margin-bottom: 10px;
        }
        .add-new {
          margin-bottom: 10px;
        }
      }
    }
    .tags-wrapper {
      height: 280px;
      padding: 0;
      overflow: hidden;
      .tag-item {
        padding-top: 10px;
        padding-left: 10px;
        display: flex;
        flex-wrap: wrap;
        .group-name {
          margin-right: 20px;
          margin-bottom: 5px;
          flex: 0 0 100px;
        }
        .tag-content {
          flex: 1
        }
        .tag {
          margin-bottom: 10px;
        }
        .add-new {
          margin-bottom: 10px;
        }
      }
    .more-btn {
      position: absolute;
      bottom: -35px;
      left: 50%;
    }
  }
  }
  .box {
    width: 100%;
    display: flex;
    .left {
      width: 40%;
      height: auto;
      background: #ffffff;
      box-shadow: 0px 0px 13px 0px rgba(47,83,151,0.1);
      padding: 0 15px;
      p{
        height: 53px;
        line-height: 53px;
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        border-bottom: 1px solid #eaeaea;
      }
       .dialogue-box {
        width: 100%;
        padding: 10px;
        display: flex;
        .portrait {
          .user {
            color: #1890ff;
            font-size: 35px;
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
      width: 48%;
      padding: 0 30px;
      .medium-btn {
        margin-top: 20px;
        display: flex;
        align-items: center;
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
  .define-btn {
    background: #fff;
    width: 100%;
    height: 100px;
    margin: 20px auto;
    display: flex;
    align-items: center;
    justify-content: center;
    .ant-btn{
      margin-left: 20px;
    }
  }
  .message-box {
    padding-bottom: 40px;
    border-bottom: 1px solid #e8e8e8;
    margin-bottom: 20px;
  }
}
</style>
