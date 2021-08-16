<template>
  <div>
    <a-upload
      name="avatar"
      list-type="picture-card"
      class="avatar-uploader"
      :show-upload-list="false"
      accept="image/*"
      :beforeUpload="uploadChange"
    >
      <img :src="url" alt="" v-if="url!=''" class="qr_code">
      <div v-else>
        <a-icon type="plus"/>
        <div class="ant-upload-text">上传二维码</div>
      </div>
    </a-upload>
  </div>
</template>
<script>
export default {
  data () {
    return {
      url: ''
    }
  },
  methods: {
    fileToBase64 (blob, cb) {
      const reader = new FileReader()
      reader.onload = function (evt) {
        const base64 = evt.target.result
        cb(base64)
      }
      reader.readAsDataURL(blob)
    },
    uploadChange (e) {
      this.fileToBase64(e, (base64Url) => {
        this.url = base64Url
        this.$emit('change', this.url)
      })
      return false
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
    }
  }
}
</script>
<style>
.qr_code{
  width: 85px;
  height: 85px;
}
</style>
