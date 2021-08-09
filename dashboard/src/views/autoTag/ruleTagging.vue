<template>
  <a-card>
    <a-tabs type="card" v-model="tabPage" @change="switchPage">
      <a-tab-pane :key="1" tab="关键词打标签">
        <keywordIndex />
      </a-tab-pane>
      <a-tab-pane :key="2" tab="客户入群行为打标签" force-render>
        <joinRoomIndex />
      </a-tab-pane>
      <a-tab-pane :key="3" tab="分时段打标签">
        <dayPartIndex />
      </a-tab-pane>
    </a-tabs>
  </a-card>
</template>
<script>
import keywordIndex from '@/views/autoTag/keywordIndex'
import joinRoomIndex from '@/views/autoTag/joinRoomIndex'
import dayPartIndex from '@/views/autoTag/dayPartIndex'
export default {
  components: {
    keywordIndex,
    joinRoomIndex,
    dayPartIndex
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
