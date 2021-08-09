<template>
  <div class="enter-text">
    <div class="textarea">
      <textarea
        v-model="text"
        placeholder="请输入"
        ref="textarea"
      />
      <span class="word-count">
        {{ text.length }}/1500
      </span>
    </div>
  </div>
</template>

<script>
export default {
  model: {
    prop: 'modelVal',
    event: 'change'
  },
  props: ['modelVal', 'defText'],
  data () {
    return {
      text: '@用户昵称 ，欢迎加入xxx活动的福利群哟～仅需完成以下三步即可领取奖品哦\n' +
        '  参与流程：\n' +
        '  ① 点击下方活动链接保存专属海报\n' +
        '  ② 将专属海报分享给x位好友哦\n' +
        '  ③ 好友长按扫描海报的二维码加入群聊就算助力成功哦～\n' +
        '\n' +
        '  注意事项：请勿直接转发活动链接给好友，是无法成功记录数据的哦～'
    }
  },
  mounted () {
    this.$emit('change', this.text)

    if (this.defText === false) {
      this.text = ''
    }
  },
  methods: {
    /**
     * 向文本域插入文本
     */
    addUserName (value) {
      const textarea = this.$refs.textarea
      const startPos = textarea.selectionStart
      // const endPos = textarea.selectionEnd
      const restoreTop = textarea.scrollTop
      // this.text = this.text.substring(0, startPos) + value + this.text.substring(endPos, this.text.length)
      this.text = value
      if (restoreTop > 0) {
        textarea.scrollTop = restoreTop
      }
      textarea.focus()
      textarea.selectionStart = startPos + value.length
      textarea.selectionEnd = startPos + value.length
    }
  },
  watch: {
    text (value) {
      this.$emit('change', value)
    }
  }
}
</script>

<style lang="less" scoped>
.enter-text {
  width: 100%;
}

.textarea {
  overflow-y: auto;
  overflow-x: hidden;
  white-space: pre-wrap;
  word-break: break-all;

  textarea {
    width: 100%;
    height: 150px;
    padding: 6px 13px;
    border: none;
    background: #fbfbfb;
    outline: none;
    resize: none;
  }

  .word-count {
    font-size: 13px;
    color: rgba(0, 0, 0, .25);
    margin-left: 10px;
  }
}
</style>
