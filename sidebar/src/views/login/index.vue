<script>
export default {
  data () {
    return {
    }
  },
  created () {
    this.login()
  },
  methods: {
    login () {
      const { target, agentId } = this.$route.query
      const apiBaseUrl = process.env.VUE_APP_API_BASE_URL
      window.location.href = apiBaseUrl + '/sidebar/agent/auth?agentId=' + agentId + '&target=' + encodeURIComponent(target)
    }
  }
}
</script>
