<template>
  <div class="quill_box">
    <div class="rich_text_row">
      <div class="title">标题：</div>
      <a-input placeholder="请输入标题" style="width: 280px;" v-model="richText.title" />
    </div>
    <div class="rich_text_row">
      <div class="title">作者：</div>
      <a-input placeholder="请输入作者" style="width: 280px;" v-model="richText.author" />
    </div>
    <div class="rich_text_row">
      <div class="title">文章描述：</div>
      <a-input placeholder="请输入文章描述" style="width: 520px;" v-model="richText.desc" />
    </div>
    <div class="rich_text_row" style="margin-bottom: 10px;">
      <span>链接封面：</span>
      <div class="input">
        <m-upload :def="false" ref="coverImg" text="请上传封面图片" v-model="richText.cover_url"/>
      </div>
    </div>
    <template>
      <quill-editor
        class="quill"
        v-model="richText.content"
        ref="myQuillEditor"
        :options="editorOption"
        @blur="onEditorBlur($event)"
        @focus="onEditorFocus($event)"
        @change="onEditorChange($event)">
      </quill-editor>
    </template>
    <div class="rich_foot">
      <a-button type="primary" @click="saveArticle">保存文章</a-button>
    </div>
  </div>
</template>
<script>
import { quillEditor } from 'vue-quill-editor' // 调用编辑器
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'
export default {
  components: { quillEditor },
  data () {
    return {
      editorOption: {},
      content: '',
      richText: {
        //  标题
        title: '',
        // 作者
        author: '',
        // 摘要
        desc: '',
        cover_url: '',
        //  内容
        content: ''
      }
    }
  },
  methods: {
    show () {
    },
    // customContent
    setUpCustomData (data) {
      this.richText = JSON.parse(JSON.stringify(data))
      this.$refs.coverImg.setUrl(this.richText.cover_url)
    },
    saveArticle () {
      if (this.richText.title == '') {
        this.$message.warning('文章标题不能为空')
        return false
      }
      if (this.richText.author == '') {
        this.$message.warning('文章作者不能为空')
        return false
      }
      if (this.richText.desc == '') {
        this.$message.warning('文章摘要不能为空')
        return false
      }
      if (this.richText.cover_url == '') {
        this.$message.warning('文章封面不能为空')
        return false
      }
      if (this.richText.content == '') {
        this.$message.warning('文章内容不能为空')
        return false
      }
      this.$emit('change', this.richText)
    },
    onEditorBlur () {}, // 失去焦点事件
    onEditorFocus () {}, // 获得焦点事件
    onEditorChange () {} // 内容改变事件
  }
}
</script>
<style lang="less" scoped>
.quill_box{
  width: 100%;
  height: 750px;
  position: relative;
}
.rich_foot{
  position: absolute;
  bottom: -10px;
  display: flex;
  justify-content:flex-end;
}
.quill{
  height: 350px;
}
.rich_text_row{
  display: flex;
  margin-top: 20px;
  .title{
    width: 75px;
    text-align: right;
    line-height: 34px;
  }
}
.rich_text_row:first-child{
  margin-top: 0;
}
</style>
