<template>
  <div :class="wrpCls">
    <a-select :value="corpName" class="select" placeholder="请选择企业" @change="handleChange">
      <a-select-option v-for="(item, index) in options" :value="item.corpName" :key="index">
        {{ item.corpName }}
      </a-select-option>
    </a-select>
    <avatar-dropdown :menu="showMenu" :class="prefixCls" />
  </div>
</template>

<script>
import AvatarDropdown from './AvatarDropdown'
import { corpSelect, corpBind } from '@/api/login'
// import store from '@/store'
import { mapGetters } from 'vuex'
export default {
  name: 'RightContent',
  components: {
    AvatarDropdown
  },
  props: {
    prefixCls: {
      type: String,
      default: 'ant-pro-global-header-index-action'
    },
    isMobile: {
      type: Boolean,
      default: () => false
    },
    theme: {
      type: String,
      required: true
    }
  },
  data () {
    return {
      showMenu: true,
      // corpId: undefined,
      options: []
    }
  },
  computed: {
    wrpCls () {
      return {
        'ant-pro-global-header-index-right': true,
        'ant-pro-global-header-index-light': true
      }
    },
    ...mapGetters(['corpName'])
  },
  mounted () {
    this.getList()
  },
  methods: {
    async getList () {
      try {
        const { data } = await corpSelect()
        this.options = data
      } catch (e) {
        console.log(e)
      }
    },
    async handleChange (corpName) {
      const ary = this.options.filter(item => {
        return item.corpName == corpName
      })
      const corpId = ary[0].corpId
      try {
        await corpBind({ corpId })
        window.location.reload()
        // store.commit('SET_CORP_ID', corpId)
        // store.commit('SET_CORP_NAME', corpName)
      } catch (err) {
        console.log(err)
      }
    }
  }
}
</script>
<style lang='less' scoped>
.select {
  width: 150px
}
</style>
