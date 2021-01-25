<template>
  <a-upload
    name="file"
    :headers="headers"
    :multiple="false"
    method="post"
    :action="uploadApi"
    :file-list="fileList"
    :before-upload="beforeUpload"
    @change="handleChange"
  >
    <slot></slot>
  </a-upload>
</template>
<script>
import storage from 'store'

export default {
  data () {
    return {
      fileList: [],
      uploadApi: process.env.VUE_APP_API_BASE_URL + '/agent/txtVerifyUpload'
    }
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
  created () {
  },
  methods: {
    handleChange (info) {
      this.fileList = [info.file]
      if (info.file.status === 'done') {
        const data = info.file.response.data
        this.$emit('success', data)
        this.loading = false
      }
      if (info.file.status === 'error') {
        this.fileList = []
        let message = ''
        if (info.file.response) {
          message = info.file.response.msg
        }
        message = message || '网络错误请稍后重试！'
        this.$message.error(message)
      }
      if (info.file.status === 'removed') {
        this.fileList = []
      }
    },
    beforeUpload (file) {
      const fileSuffix = this._getFileSuffix(file.name).toLowerCase()
      const flag = fileSuffix == 'txt'
      if (!flag) {
        this.$message.error(`您只能上传txt类型文件`)
      }
      return flag
    },
    // 获取文件后缀
    _getFileSuffix (imgName) {
      return imgName.substring(imgName.lastIndexOf('.') + 1)
    }
  }
}
</script>
<style lang="less">
.avatar-uploader > .ant-upload {
  width: 100px;
  height: 100px;
}
.ant-upload-select-picture-card i {
  font-size: 32px;
  color: #999;
}

.img-wrapper {
  .img {
    width: 100px;
    height: 100px;
  }
}
</style>
