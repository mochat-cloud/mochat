<template>
  <div :class="prefixCls">
    <ul>
      <li v-for="(icon, key) in icons" :key="key" :class="{ 'active': selectedIcon==icon, 'disabled': usedIconList.indexOf(icon) > -1 }" @click="handleSelectedIcon(icon)" >
        <a-icon :type="icon" :style="{ fontSize: '20px' }" />
      </li>
    </ul>
  </div>
</template>

<script>
import icons from './icons'

export default {
  name: 'IconSelect',
  props: {
    prefixCls: {
      type: String,
      default: 'ant-pro-icon-selector'
    },
    // eslint-disable-next-line
    value: {
      type: String
    },
    usedIconList: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      selectedIcon: this.value || '',
      currentTab: 'directional',
      icons
    }
  },
  watch: {
    value (val) {
      this.selectedIcon = val
    }
  },
  created () {
  },
  methods: {
    handleSelectedIcon (icon) {
      if (this.usedIconList.indexOf(icon) > -1) {
        this.$message.warn('该图标已占用')
        return
      }
      this.selectedIcon = icon
      this.$emit('change', icon)
    }
  }
}
</script>

<style lang="less" scoped>
  @import "../index.less";

  ul{
    list-style: none;
    padding: 0;
    overflow-y: scroll;
    height: 250px;

    li{
      display: inline-block;
      padding: @padding-sm;
      margin: 3px 2px;
      border-radius: @border-radius-base;

      &:hover, &.active{
        // box-shadow: 0px 0px 5px 2px @primary-color;
        cursor: pointer;
        color: @white;
        background-color: @primary-color;
      }
      &.disabled {
        background-color:rgb(179, 178, 178)
      }
    }
  }
</style>
