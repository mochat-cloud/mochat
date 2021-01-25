import request from '@/utils/request'

// 素材库-素材库列表
export function materialLibraryList (params) {
  return request({
    url: '/medium/index',
    method: 'get',
    params: params
  })
}

// 素材库-素材库查看
export function getMaterialLibrary (params) {
  return request({
    url: '/medium/show',
    method: 'get',
    params: params
  })
}

// 素材库-素材库添加
export function addMaterialLibrary (params) {
  return request({
    url: '/medium/store',
    method: 'post',
    data: params
  })
}

// 素材库-素材库删除
export function delMaterialLibrary (params) {
  return request({
    url: '/medium/destroy',
    method: 'delete',
    data: params
  })
}

// 素材库-移动分组
export function moveGroup (params) {
  return request({
    url: '/medium/groupUpdate',
    method: 'put',
    data: params
  })
}

// 素材库-修改素材库
export function editMaterialLibrary (params) {
  return request({
    url: '/medium/update',
    method: 'put',
    data: params
  })
}

// 素材库分组-素材库分组列表
export function mediumGroup (params) {
  return request({
    url: '/mediumGroup/index',
    method: 'get',
    params: params
  })
}

// 素材库分组-素材库分组添加
export function addMediumGroup (params) {
  return request({
    url: '/mediumGroup/store',
    method: 'post',
    data: params
  })
}

// 素材库分组-素材库分组修改
export function editMediumGroup (params) {
  return request({
    url: '/mediumGroup/update',
    method: 'put',
    data: params
  })
}

// 素材库分组-素材库分组删除
export function delMediumGroup (params) {
  return request({
    url: '/mediumGroup/destroy',
    method: 'delete',
    data: params
  })
}

// 上传
export function upLoad (params) {
  return request({
    url: '/common/upload',
    method: 'post',
    data: params
  })
}
