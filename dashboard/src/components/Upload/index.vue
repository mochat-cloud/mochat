<template>
  <div class="upload">
    <a-upload
      v-if="!url"
      name="file"
      :listType="type === 'btn' ? 'text' : 'picture-card'"
      class="avatar-uploader"
      :show-upload-list="false"
      :beforeUpload="uploadChange"
      accept="image/*"
    >
      <div v-if="type === 'btn'">
        <a-button>
          {{ text }}
        </a-button>
      </div>
      <div v-else>
        <a-icon type="plus"/>
        <div class="ant-upload-text">
          {{ text }}
        </div>
      </div>
    </a-upload>
    <div v-else>
      <img :src="url" v-if="url && preview">
      <a-button @click="reset">重新上传</a-button>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      url: ''
    }
  },
  model: {
    prop: 'modelVal',
    event: 'change'
  },
  props: {
    type: {
      default: 'block',
      type: String
    },
    preview: {
      default: true,
      type: Boolean
    },
    modelVal: {
      default: '',
      type: String
    },
    def: {
      default: '../../assets/default-cover.png',
      type: [Boolean, String]
    },
    text: {
      default: '上传文件',
      type: String
    }
  },
  mounted () {
    if (this.def) {
      this.getBase64('../../assets/default-cover.png').then(res => {
        this.url = res
        this.$emit('change', this.url)
      })
    }
  },
  methods: {
    reset () {
      this.url = ''
      this.$emit('change', '')
    },
    setUrl (url) {
      this.getBase64(url).then(res => {
        this.url = res
      })
    },
    getBase64 (img, time = '3412431') {
      return new Promise(resolve => {
        if (!img) {
          resolve('')
          return false
        }
        const rep = /base64/g
        if (rep.test(img)) {
          resolve(img)
          return false
        }

        img = `${img}?time=${time}`

        function getBase64Image (img, width, height) {
          const canvas = document.createElement('canvas')
          // eslint-disable-next-line
          canvas.width = width ? width : img.width
          // eslint-disable-next-line
          canvas.height = height ? height : img.height
          const ctx = canvas.getContext('2d')
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height)

          return canvas.toDataURL()
        }
        const image = new Image()
        image.crossOrigin = 'Anonymous'
        image.src = img
        if (img) {
          image.onerror = () => {
            // this.getBase64(img, Date.parse(new Date()))
          }
          image.onload = () => {
            resolve(getBase64Image(image))
          }
        }
      })
    },
    fileToBase64 (blob, cb) {
      const reader = new FileReader()

      reader.onload = function (evt) {
        const base64 = evt.target.result
        cb(base64)
      }
      reader.readAsDataURL(blob)
    },
    uploadChange (e) {
      const rep = /image/g
      if (!rep.test(e.type)) {
        this.$message.error('请上传图片')
        return false
      }

      this.fileToBase64(e, (base64Url) => {
        this.url = base64Url
        this.$emit('change', this.url)
      })
      return false
    }
  },
  watch: {
    url (value) {
      this.$emit('change', value)
    }
  }
}
</script>

<style lang="less" scoped>
img {
  width: 130px;
  height: 130px;
  margin-right: 15px;
}
</style>
