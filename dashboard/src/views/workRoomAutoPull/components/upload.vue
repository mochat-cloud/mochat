<template>
  <a-upload
    name="file"
    list-type="picture-card"
    class="avatar-uploader"
    :headers="headers"
    method="post"
    :show-upload-list="false"
    :action="uploadApi"
    :before-upload="beforeUpload"
    @change="handleChange"
  >
    <div v-if="imageUrl" class="img-wrapper">
      <img class="img" :src="imageUrl" alt="avatar" />
    </div>
    <div v-else>
      <a-icon :type="loading ? 'loading' : 'plus'" />
    </div>
  </a-upload>
</template>
<script>
import storage from 'store'

export default {
  props: {
    imageUrl: {
      type: String,
      default: ''
    },
    fileType: {
      type: Number || Array,
      default: 1
    }
  },
  data () {
    return {
      loading: false,
      // imageUrl: '',
      FileTypeArr: [
        '',
        ['jpg', 'png', 'jpeg'],
        ['mp4', 'avi', '3gp', 'flv'],
        ['mp3', 'mp4', 'wav', 'ogg'],
        ['pdf'],
        ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf']
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
      if (info.file.status === 'uploading') {
        this.loading = true
        return
      }
      if (info.file.status === 'done') {
        const data = info.file.response.data
        this.$emit('success', data)
        this.loading = false
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
