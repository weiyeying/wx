browser-detector
===================
###主要作用
* 微信开发扩展插件



### 安装
    
    composer require wx/wxext

### 使用

    use wx\wxext;
    use apanly\BrowserDetector\Os;
    use apanly\BrowserDetector\Device;
    
    $browser = new Browser();
    
    if ($browser->getName() === Browser::IE && $browser->getVersion() < 11) {
        echo 'Please upgrade your browser.';
    }
    
    $os = new Os();
    
    if ($os->getName() === Os::IOS) {
        echo 'You are using an iOS device.';
    }
    
    
    $device = new Device();
    
    if ($device->getName() === Device::IPAD) {
        echo 'You are using an iPad.';
    }

###说明
本项目大部分源码clone 参考资料的sinergi/php-browser-detector，主要是原项目无法适应中国国情，需要修改，故此另开一个项目

###Lecense
PHP Browser is licensed under [The MIT License (MIT)](LICENSE).


###参考资料
* [sinergi/php-browser-detector](https://github.com/sinergi/php-browser-detector)
* [用户代理检测和浏览器Ua详细分析](http://www.cnblogs.com/hykun/p/Ua.html)


