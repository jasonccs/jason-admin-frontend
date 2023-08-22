import Layout from '@/layout'

// 日志
const route = {
  path: '/operation-log/index',
  component: Layout,
  redirect: '/operation-log/list',
  alwaysShow: true,
  name: 'OperationLog',
  meta: {
    title: 'OperationLog',
    icon: 'el-icon-document-add',
    roles: [
      'larke-admin.operation-log.index'
    ]
  },
  sort: 101000,
  children: [
    {
      path: '/operation-log/list',
      component: () => import('./views/index'),
      name: 'OperationLogList',
      meta: {
        title: 'OperationLogList',
        icon: 'el-icon-document-add',
        roles: [
          'larke-admin.operation-log.index'
        ]
      }
    }

  ]
}

export default route
