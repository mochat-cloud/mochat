<script>
export default {
  data () {
    return {
    }
  },
  created () {
    this.oldRouteRedirect()
  },
  methods: {
    oldRouteRedirect () {
      const { agentId, pageFlag } = this.$route.query

      if (!pageFlag) {
        return
      }

      let routePath = ''
      if (pageFlag === 'customer') {
        routePath = 'contact'
      } else if (pageFlag === 'mediumGroup') {
        routePath = 'medium'
      } else {
        routePath = pageFlag
      }
      const location = { path: `/${routePath}`, query: { agentId: agentId } }
      this.$router.push(location)
    }
  }
}
</script>
