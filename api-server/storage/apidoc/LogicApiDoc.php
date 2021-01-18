<?php

##============ 业务类定义 ===========##

/**
 * @apiDefine MediumContentRequest
 * @apiParamExample {json} content.文本:
 *  {
 *      "title": "咨询",
 *      "content": "你你哈哈吗"
 *  }
 *
 * @apiParamExample {json} content.图片:
 *  {
 *      "imagePath": "/path/a.jpg",
 *      "imageName": "a.jpg"
 *  }
 *
 * @apiParamExample {json} content.图文:
 *  {
 *      "title": "..",
 *      "description": "..",
 *      "imagePath": "/path/a.jpg",
 *      "imageLink": "https://...",
 *      "imageName": "a.jpg"
 *  }
 *
 * @apiParamExample {json} content.音频:
 *  {
 *      "voicePath": "/path/a.mp3"
 *      "voiceName": "a.mp3"
 *  }
 *
 * @apiParamExample {json} content.视频:
 *  {
 *      "videoPath": "/path/a.mp4",
 *      "videoName": "a.mp4"
 *  }
 *
 * @apiParamExample {json} content.小程序:
 *  {
 *      "appid": "asdfasdfkasdlfj234",
 *      "page": "/index/index",
 *      "title": "..",
 *      "imagePath": "/path/a.jpg"
 *      "imageName": "a.jpg"
 *  }
 *
 * @apiParamExample {json} content.文件:
 *  {
 *      "filePath": "/path/a.xls",
 *      "fileName": "a.xls"
 *  }
 */

/**
 * @apiDefine MediumContentResponse
 * @apiSuccessExample {json} content.文本:
 *  {
 *      "title": "咨询",
 *      "content": "你你哈哈吗"
 *  }
 *
 * @apiSuccessExample {json} content.图片:
 *  {
 *      "imagePath": "/path/a.jpg",
 *      "imageFullPath": "https://path/a.jpg",
 *      "imageName": "a.jpg"
 *  }
 *
 * @apiSuccessExample {json} content.图文:
 *  {
 *      "title": "..",
 *      "description": "..",
 *      "imagePath": "/path/a.jpg",
 *      "imageFullPath": "https://path/a.jpg",
 *      "imageLink": "https://...",
 *      "imageName": "a.jpg"
 *  }
 *
 * @apiSuccessExample {json} content.音频:
 *  {
 *      "voicePath": "/path/a.mp3",
 *      "voiceFullPath": "https://path/a.mp3",
 *      "voiceName": "a.mp3"
 *  }
 *
 * @apiSuccessExample {json} content.视频:
 *  {
 *      "videoPath": "/path/a.mp4",
 *      "videoFullPath": "https://path/a.mp4",
 *      "videoName": "a.mp4"
 *  }
 *
 * @apiSuccessExample {json} content.小程序:
 *  {
 *      "appid": "asdfasdfkasdlfj234",
 *      "page": "/index/index",
 *      "title": "..",
 *      "imagePath": "/path/a.jpg",
 *      "imageFullPath": "https://path/a.jpg"
 *  }
 *
 * @apiSuccessExample {json} content.文件:
 *  {
 *      "filePath": "/path/a.xls",
 *      "fileFullPath": "https://path/a.xls"
 *      "fileName": "a.xls"
 *  }
 */