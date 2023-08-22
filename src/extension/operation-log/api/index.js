import request from '@/utils/request'

export function getList(params) {
  return request({
    url: '/operation-log',
    method: 'get',
    params
  })
}

export function getDetail(id) {
  return request({
    url: `/operation-log/${id}`,
    method: 'get'
  })
}

export function deleteLog(id) {
  return request({
    url: `/operation-log/${id}`,
    method: 'delete'
  })
}

export function clearLog(data) {
  return request({
    url: '/operation-log/clear',
    method: 'delete',
    data
  })
}
