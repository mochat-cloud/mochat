<template>
  <a-upload
    name="file"
    list-type="picture-card"
    :class="btnType ? 'avatar-uploader' : 'btn-type'"
    :headers="headers"
    method="post"
    :show-upload-list="false"
    :action="uploadApi"
    :before-upload="beforeUpload"
    @change="handleChange"
  >
    <div v-if="btnType">
      <div v-if="imageUrl" class="img-wrapper">
        <img class="img" :src="imageUrl" alt="avatar" />
      </div>
      <div v-else>
        <a-icon :type="loading ? 'loading' : 'plus'" />
      </div>
    </div>
    <a-button v-else type="link">重新上传</a-button>
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
    },
    btnType: {
      type: Boolean,
      default: true
    }
  },
  data () {
    return {
      loading: false,
      // imageUrl: '',
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
.btn-type {
  width: 0;
  height: 0;
  .ant-upload {
    width: 0;
    height: 0;
    border: 0;
    background: #fff;
  }
}
</style>
