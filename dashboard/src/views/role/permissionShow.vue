<template>
  <div>
    <a-card title="权限设置" class="card">
      <div slot="extra">
        <a-button class="cancel" @click="cancel">取消</a-button>
        <a-button v-permission="'/role/permissionShow@save'" type="primary" :loading="loading" @click="submit">保存权限</a-button>
      </div>
      <div class="wrapper" v-for="(item, index) in info" :key="index">
        <div class="first-wrapper">
          <div class="first-title">
            <a-checkbox
              :checked="item.checked == '2'"
              :indeterminate="item.checked == '3'"
              @change="checkClick($event, item)"></a-checkbox>
            <a-button type="link">{{ item.name }}</a-button>
          </div>
        </div>
        <!-- 二级菜单 -->
        <div class="second-wrapper">
          <div v-for="(secondTitle, secondIndex) in item.children" :key="'secondTitle' + secondIndex" :class="['second-item', secondTitle.flag ? 'active' : '']">
            <a-checkbox
              :checked="secondTitle.checked == '2'"
              :indeterminate="secondTitle.checked == '3'"
              @change="checkClick($event, secondTitle, item)"></a-checkbox>
            <a-button type="link" :class="secondTitle.flag ? 'active' : ''" @click="itemClick(item, secondIndex)">{{ secondTitle.name }}</a-button>

          </div>
        </div>
        <!-- 三级菜单 -->
        <div v-for="(second, secondIndex) in item.children" :key="'second' + secondIndex">
          <div class="permission" v-if="second.flag">
            <div class="third-wrapper">
              <div
                v-for="(third, thirdIndex) in second.children"
                :key="'thirdIndex' + thirdIndex"
                class="third-content">
                <div class="third-item">
                  <a-checkbox
                    :checked="third.checked == '2'"
                    :indeterminate="third.checked == '3'"
                    @change="checkClick($event, third, second, item)"></a-checkbox>
                  <a-button type="link" :class="[third.flag ? 'active' : '', third.isPageMenu == 2 ? 'light' : '']" @click="itemClick(second, thirdIndex)">{{ third.name }}</a-button>
                </div>
              </div>
            </div>
            <div class="fourth-wrapper">
              <div class="inner">
                <div
                  v-for="(third, thirdIndex) in second.children"
                  :key="'thir' + thirdIndex"
                  class="fourth-content">
                  <div v-if="third.flag">
                    <div
                      class="fourth-item"
                      v-for="(fourth, fourthIndex) in third.children"
                      :key="'fourth' + fourthIndex">
                      <a-checkbox
                        :checked="fourth.checked == '2'"
                        :indeterminate="fourth.checked == '3'"
                        @change="checkClick($event, fourth, third, second, item)"></a-checkbox>
                      <a-button type="link" :class="[fourth.flag ? 'active' : '', fourth.isPageMenu == 2 ? 'light' : '']" @click="itemClick(third, fourthIndex)">{{ fourth.name }}</a-button>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            <div class="fif-wrapper">
              <div class="inner">
                <div
                  v-for="(third, thirdIndex) in second.children"
                  :key="'thi' + thirdIndex"
                  class="fourth-content">
                  <div v-if="third.flag">
                    <div
                      class="fourth-item"
                      v-for="(fourth, fourthIndex) in third.children"
                      :key="'fourt' + fourthIndex">
                      <div v-if="fourth.flag">
                        <div
                          class="fif-item"
                          v-for="(fif, fifIndex) in fourth.children"
                          :key="'fif' + fifIndex">
                          <a-checkbox
                            :checked="fif.checked == '2'"
                            :indeterminate="fif.checked == '3'"
                            @change="checkClick($event, fif, fourth, third, second, item)"></a-checkbox>
                          <a-button type="link" :class="fif.isPageMenu == 2 ? 'light' : ''">{{ fif.name }}</a-button>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </a-card>
  </div>
</template>

<script>
import { rolePermission, rolePermissionStore } from '@/api/role'
export default {
  data () {
    return {
      roleId: '',
      info: [],
      permissionData: [],
      loading: false
    }
  },
  created () {
    this.roleId = this.$route.query.roleId
    rolePermission({ roleId: this.roleId }).then(res => {
      this.info = this.dealData(res.data)
    })
  },
  methods: {
    dealData (data) {
      return data.map(item => {
        this.itemClick(item, 0)
        return item
      })
    },
    itemClick (item, index) {
      if (item.children) {
        item.children.forEach((second, seindex) => {
          if (seindex == index) {
            second.flag = true
          } else {
            second.flag = false
          }
          // 三级
          if (second.children) {
            second.children.forEach((third, thindex) => {
              if (thindex == 0) {
                third.flag = true
              } else {
                third.flag = false
              }
              // 四级
              if (third.children) {
                third.children.forEach((fourth, foindex) => {
                  if (foindex == 0) {
                    fourth.flag = true
                  } else {
                    fourth.flag = false
                  }
                })
              }
            })
          }
        })
      }
    },
    // 父子传递状态
    dealCheck (e, item) {
      let checked = ''
      if (e.target.checked) {
        item.checked = '2'
        checked = '2'
      } else {
        item.checked = '1'
        checked = '1'
      }
      if (item.children) {
        // 二级
        item.children.forEach((second, seindex) => {
          second.checked = checked
          // 三级
          if (second.children) {
            second.children.forEach((third, thindex) => {
              third.checked = checked
              // 四级
              if (third.children) {
                third.children.forEach((fourth, foindex) => {
                  fourth.checked = checked
                  // 五级(四级功能权限)
                  if (fourth.children) {
                    fourth.children.forEach((fif, fifindex) => {
                      fif.checked = checked
                    })
                  }
                })
              }
            })
          }
        })
      }
    },
    checkClick (e, ...arg) {
      this.dealCheck(e, arg[0])
      const checked = e.target.checked
      // 第一个为当前元素，以后为父级元素
      arg.forEach((item, index) => {
        if (index > 0 && item.children) {
          if (checked) {
            const flag = item.children.every(paren => {
              return paren.checked == '2'
            })
            item.checked = flag ? '2' : '3'
          } else {
            const flag = item.children.some(paren => {
              return paren.checked == '2' || paren.checked == '3'
            })
            item.checked = flag ? '3' : '1'
          }
        }
      })
    },
    cancel () {
      this.$router.push({ path: '/role/index' })
    },
    dealParams () {
      this.permissionData = []
      this.info.forEach(item => {
        if (item.checked == '2') {
          this.permissionData.push(item.id)
        }
        if (item.children) {
          item.children.forEach(second => {
            if (second.checked == '2') {
              this.permissionData.push(second.id)
            }
            // 三级
            if (second.children) {
              second.children.forEach(third => {
                if (third.checked == '2') {
                  this.permissionData.push(third.id)
                }
                // 四级
                if (third.children) {
                  third.children.forEach(fourth => {
                    if (fourth.checked == '2') {
                      this.permissionData.push(fourth.id)
                    }
                    // 五级
                    if (fourth.children) {
                      fourth.children.forEach(fif => {
                        if (fif.checked == '2') {
                          this.permissionData.push(fif.id)
                        }
                      })
                    }
                  })
                }
              })
            }
          })
        }
      })
    },
    async submit () {
      this.dealParams()
      const { roleId, permissionData } = this
      if (!permissionData || permissionData.length == 0) {
        this.$message.warn('请设置权限')
        return
      }
      try {
        this.loading = true
        await rolePermissionStore({
          roleId,
          menuIds: permissionData
        })
        this.loading = false
        this.cancel()
      } catch (e) {
        console.log(e)
        this.loading = false
      }
    }
  }
}
</script>

<style lang="less" scoped>
.wrapper {
  margin-top: -24px;
  margin-bottom: 50px;
  button {
    color: rgba(0, 0, 0, 0.65);
    border: none;
  }
  .light {
    color: rgba(82, 80, 80, 0.5)
  }
  .active {
    color: #1890ff;
  }
  .first-wrapper {
    height: 63px;
    background: #f0f0f2;
    line-height: 63px;
    padding-left: 10px;
  }
  .second-wrapper {
    height: 63px;
    line-height: 63px;
    background: #f8f8f8;
    padding-left: 30px;
    display: flex;
    align-items: center;
  }
  .permission {
    display: flex;
    .third-wrapper {
      padding-left: 50px;
    }
    .third-wrapper,.fourth-wrapper,.fif-wrapper {
      flex: 1;
      padding-left: 50px;
    }
    .third-wrapper,.fourth-wrapper {
      border-right: 1px solid #edeef1;
    }
  }
}

.cancel {
  margin-right: 20px
}
</style>
