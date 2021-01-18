<template>
  <div class="people-wrapper">
    <div class="people-left">
      <a-input-search
        class="add-input"
        v-model.trim="searchKeyWords"
        :maxLength="15"
        @search="getDepartmentList" />
      <a-tree
        v-model="checkedKeys"
        show-icon
        checkable
        :replaceFields="replaceFields"
        :checkStrictly="false"
        :expanded-keys="expandedKeys"
        :selected-keys="selectedKeys"
        :tree-data="treeData"
        @check="onCheck"
        @expand="onExpand"
      >
        <a-icon slot="folder" type="folder-open" />
      </a-tree>
    </div>
    <div class="people-right">
      <div class="total">已选择成员</div>
      <ul>
        <li class="number" v-for="(item, index) in choosePeopleKey" :key="index">
          <span class="title">{{ item.employeeName }}</span>
          <a-icon class="close" @click="deletePeople(item, index)" type="close" />
        </li>
      </ul>
    </div>
  </div>
</template>
<script>
import { departmentList, workDepartmentList } from '@/api/workRoom'
import { mapGetters } from 'vuex'

export default {
  name: 'Department',
  props: {
    memberKey: {
      type: Array,
      default: () => []
    },
    isChecked: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      searchKeyWords: '',
      checkedKeys: [],
      replaceFields: {
        title: 'name',
        key: 'id',
        children: 'son'
      },
      expandedKeys: [],
      selectedKeys: [],
      treeData: [],
      lastCheckdKeys: [],
      choosePeopleKey: [],
      departmentIds: [],
      flatAry: [],
      unchecked: []
    }
  },
  watch: {
    memberKey (value) {
      this.choosePeopleKey = value
    },
    choosePeopleKey (data) {
      this.$emit('change', data)
    },
    isChecked (value) {
      this.checkedKeys = value
    }
  },
  computed: {
    ...mapGetters(['corpName'])
  },
  created () {
    this.choosePeopleKey = this.memberKey
    this.checkedKeys = this.isChecked
    const time = this.corpName ? 0 : 2000
    setTimeout(() => {
      this.getDepartmentList()
    }, time)
  },
  methods: {
    // 部门
    async getDepartmentList () {
      const param = {
        searchKeyWords: this.searchKeyWords
      }
      try {
        const { data: { department, employee } } = await departmentList(param)
        if (this.searchKeyWords !== '') {
          this.$emit('search', employee)
        }
        this.department = department || []
        this.employee = employee || []
        this.treeData = [{
          name: this.corpName,
          nameEn: 'company',
          id: 'company',
          slots: {
            icon: 'folder'
          },
          son: department
        }]
      } catch (e) {
        console.log(e)
      }
    },
    // 勾选成员
    onCheck (checkedKeys) {
      const newLength = checkedKeys.length
      const oldLength = this.lastCheckdKeys.length
      let addAry = []
      let lossAry = []
      if (newLength > oldLength) {
        addAry = checkedKeys.filter(item => {
          return this.lastCheckdKeys.indexOf(item) == -1 && item !== 'company'
        })
        this.getWorkDepartmentList(addAry.join(','))
      } else {
        lossAry = this.lastCheckdKeys.filter(item => {
          return checkedKeys.indexOf(item) == -1 && item !== 'company'
        })
        this.choosePeopleKey = this.choosePeopleKey.filter(item => {
          return !lossAry.includes(item.departmentId)
        })
      }
      this.lastCheckdKeys = checkedKeys
    },
    onExpand (expandedKeys) {
      this.expandedKeys = expandedKeys
    },
    // 删除成员
    deletePeople (data, index) {
      this.choosePeopleKey.splice(index, 1)
      const departments = this.departmentIds[data.employeeId]
      departments.map(departmentId => {
        const ary = this.choosePeopleKey.filter(item => {
          return item.departmentId == departmentId
        })
        if (ary.length == 0) {
          const companyIdex = this.checkedKeys.indexOf('company')
          if (companyIdex > -1) {
            this.checkedKeys.splice(companyIdex, 1)
          }
          this.dealDeleteData(departmentId)
          this.unchecked.map(innerItem => {
            const index = this.checkedKeys.indexOf(innerItem)
            if (index > -1) {
              this.checkedKeys.splice(index, 1)
            }
          })
        }
      })
      if (this.choosePeopleKey.length == 0) {
        this.checkedKeys = []
      }
    },
    dealDeleteData (departmentId) {
      const data = this.treeData[0]
      this.flatAry = []
      this.unchecked = []
      this.flat(data.son, departmentId)
      const length = this.unchecked.length
      if (length > 1) {
        this.filterId(this.unchecked[length - 1])
      }
    },
    filterId (id) {
      this.flatAry.filter(item => {
        if (item.id == id) {
          this.unchecked.push(id)
          if (item.parentId != 0) {
            this.filterId(item.parentId)
          }
          return true
        }
      })
    },
    flat (data, departmentId) {
      data.forEach(item => {
        this.flatAry.push(item)
        if (item.id == departmentId) {
          if (item.parentId != 0) {
            this.unchecked = [item.id, item.parentId]
          } else {
            this.unchecked = [item.id]
          }
        }
        if (item.son) {
          this.flat(item.son, departmentId)
        }
      })
    },
    // 部门成员
    async getWorkDepartmentList (departmentIds) {
      this.departmentIds = {}
      try {
        let { data } = await workDepartmentList({ departmentIds })
        data = data || []
        this.choosePeopleKey = [
          ...this.choosePeopleKey,
          ...data
        ]
        const ary = []
        const ids = []
        this.choosePeopleKey.forEach(item => {
          const flag = ids.indexOf(item.employeeId)
          if (flag > -1) {
            this.departmentIds[item.employeeId].push(item.departmentId)
          } else {
            this.departmentIds[item.employeeId] = [item.departmentId]
            ids.push(item.employeeId)
            ary.push(item)
          }
        })
        this.choosePeopleKey = ary
      } catch (e) {
        console.log(e)
      }
    }
  }
}
</script>
<style lang='less' scoped>
  .people-wrapper {
    min-height: 200px;
    display: flex;
    flex-direction: row;
    .people-left {
      flex: 0 0 50%;
      border-right: 2px solid rgba(0, 0, 0, 0.3);
      .add-input {
        max-width: 98%;
      }
    }
    .people-right{
      flex: 0 0 50%;
      .total {
        padding: 8px;
      }
      .number {
        display: flex;
        .title {
          flex: 1
        }
        .close {
          flex: 0 0 50px
        }
      }
    }
  }
</style>
