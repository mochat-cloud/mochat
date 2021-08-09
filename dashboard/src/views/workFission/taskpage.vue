<template>
  <a-card>
    <a-tabs type="card" v-model="tabPage" @change="switchPage">
      <a-tab-pane :key="1" tab="企微任务宝">
        <workFissionIndex />
      </a-tab-pane>
      <a-tab-pane :key="2" tab="数据详情" force-render>
        <workFissionDataShow />
      </a-tab-pane>
    </a-tabs>
  </a-card>
</template>
<script>
import workFissionIndex from '@/views/workFission/index'
import workFissionDataShow from '@/views/workFission/dataShow'
export default {
  components: {
    workFissionIndex,
    workFissionDataShow
  },
  data () {
    return {
      tabPage: 1
    }
  },
  methods: {
    switchPage () {
    }
  }
}
</script>
