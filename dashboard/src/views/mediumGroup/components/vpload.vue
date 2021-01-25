<template>
  <a-upload
    name="file"
    :headers="headers"
    :multiple="true"
    method="post"
    :show-upload-list="false"
    :action="uploadApi"
    :before-upload="beforeUpload"
    @change="handleChange"
  >
    <a-button> <a-icon type="upload" /> 上传 </a-button>
  </a-upload>
</template>
<script>
import storage from 'store'

export default {
  props: {
    fileType: {
      type: Number || Array,
      default: 1
    }
  },
  data () {
    return {
      loading: false,
      FileTypeArr: [
        '',
        ['jpg', 'png', 'jpeg'],
        ['mp3', 'amr'],
        ['mp4'],
        ['doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt', 'pdf', 'Xmind']
      ],
      uploadApi: process.env.VUE_APP_API_BASE_URL + '/common/upload'
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
      console.log(info)
      if (info.file.status === 'done') {
        const data = info.file.response.data
        this.$emit('successDefine', data)
      }
      if (info.file.status === 'error') {
        if (info.file.response) {
          const data = info.file.response
          this.$message.error(data.msg)
        }
      }
    },
    beforeUpload (file) {
      const fileSuffix = this._getFileSuffix(file.name).toLowerCase()
      const fileType = file.type.split('/')[0]
      const file2M = file.size / 1024 / 1024 < 2
      const file10M = file.size / 1024 / 1024 < 10
      let ary = []
      if (this.fileType instanceof Array) {
        this.fileType.map(item => {
          ary.concat(this.FileTypeArr[item])
        })
      } else {
        ary = this.FileTypeArr[this.fileType]
      }
      const flag = ary.includes(fileSuffix)
      if (!flag) {
        this.$message.error(`您只能上传以下类型： ${ary.join(',')}`)
        return false
      }
      if (fileType === 'video') {
        if (!file10M) {
          this.$message.error('上传文件过大')
        }
        return flag && file10M
      }

      if (!file2M) {
        this.$message.error('上传文件过大')
      }
      return flag && file2M
    },
    // 获取文件后缀
    _getFileSuffix (imgName) {
      return imgName.substring(imgName.lastIndexOf('.') + 1)
    }
  }
}
</script>
<style lang="less">

</style>
