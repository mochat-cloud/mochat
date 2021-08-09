import Vue from 'vue'

// base library
import {
  ConfigProvider,
  Layout,
  Input,
  InputNumber,
  Button,
  Switch,
  Radio,
  Checkbox,
  Select,
  Card,
  Form,
  FormModel,
  Row,
  Col,
  Modal,
  Table,
  Tabs,
  Icon,
  Badge,
  Popover,
  Dropdown,
  List,
  Avatar,
  Breadcrumb,
  Steps,
  Spin,
  Menu,
  Drawer,
  Tooltip,
  Alert,
  Tag,
  Divider,
  DatePicker,
  TimePicker,
  Upload,
  Progress,
  Skeleton,
  Popconfirm,
  PageHeader,
  Result,
  Statistic,
  Descriptions,
  message,
  notification,
  Tree,
  Pagination,
  TreeSelect,
  Timeline
} from 'ant-design-vue'
import ECharts from 'vue-echarts'
import 'echarts/lib/chart/line'
import 'echarts/lib/component/tooltip'
import 'echarts/lib/component/legend'
import 'echarts/lib/component/dataZoom'
// import 'echarts/lib/chart/bar'
// import 'echarts/lib/chart/pie'
// import 'echarts/lib/component/markPoint'
// import 'echarts/lib/component/markLine'
// import 'echarts/lib/component/graphic'
// import 'echarts/lib/component/title'

// ext library
import VueCropper from 'vue-cropper'
import VueClipboard from 'vue-clipboard2'
import PageLoading from '@/components/PageLoading'
import { PageHeaderWrapper } from '@ant-design-vue/pro-layout'
import './directives'

Vue.use(ConfigProvider)
Vue.use(Layout)
Vue.use(Input)
Vue.use(InputNumber)
Vue.use(Button)
Vue.use(Switch)
Vue.use(Radio)
Vue.use(Checkbox)
Vue.use(Select)
Vue.use(Card)
Vue.use(Form)
Vue.use(FormModel)
Vue.use(Row)
Vue.use(Col)
Vue.use(Modal)
Vue.use(Table)
Vue.use(Tabs)
Vue.use(Icon)
Vue.use(Badge)
Vue.use(Popover)
Vue.use(Dropdown)
Vue.use(List)
Vue.use(Avatar)
Vue.use(Breadcrumb)
Vue.use(Steps)
Vue.use(Spin)
Vue.use(Menu)
Vue.use(Drawer)
Vue.use(Tooltip)
Vue.use(Alert)
Vue.use(Tag)
Vue.use(Divider)
Vue.use(DatePicker)
Vue.use(TimePicker)
Vue.use(Upload)
Vue.use(Progress)
Vue.use(Skeleton)
Vue.use(Popconfirm)
Vue.use(PageHeader)
Vue.use(Result)
Vue.use(Statistic)
Vue.use(Descriptions)
Vue.use(Tree)
Vue.use(Pagination)
Vue.use(TreeSelect)
Vue.use(Timeline)

Vue.prototype.$confirm = Modal.confirm
Vue.prototype.$message = message
Vue.prototype.$notification = notification
Vue.prototype.$info = Modal.info
Vue.prototype.$success = Modal.success
Vue.prototype.$error = Modal.error
Vue.prototype.$warning = Modal.warning

Vue.component('v-chart', ECharts)
Vue.use(PageLoading)
Vue.use(VueCropper)
Vue.use(VueClipboard)
Vue.use(PageHeaderWrapper)
