## 阿里云nacos配置监控组件
> 本组件是基于 `neatlife/php-nacos`基础上开发而来： https://github.com/neatlife/php-nacos 

* 服务参考地址：https://help.aliyun.com/product/123350.html
* nacos开发文档：https://nacos.io/zh-cn/docs/open-api.html
* 本组件可以用于laravel 框架，也可以用于非laravel框架。
* 组件运行于命令行，将阿里云账号AK,SK,host,group,nameSpaceId添加到系统环境变量中.
* docker-compose.yml 添加方式环境变量参考：
```$xslt
    env_file:
      - .env_nacos
```
* .env_nacos 文件记载6个配置项值（配置到生产环境服务器上）：
    * 前面3个是微服务参数，后面3个是生成配置的参数.
    * 这些配置项的参数具体意义需要访问 https://help.aliyun.com/product/123350.html 学习一下。
```$xslt
ALI_MSE_AK=fdasfdfdkkfdaskfsdkf                 #阿里云MES服务的AccessKey 
ALI_MSE_SK=fdfdsafsdffdsfadsf                   #阿里云MES服务的SecretKey 
ALI_MSE_HOST=http://xxxx.alicloud.com:8888/     #mes服务地址，包括端口

ALI_MSE_DATA_ID=app_name                        #配置项ID，对于PHP项目其实就是项目名称
ALI_MSE_GROUP=group_name                            #配置分组ID
ALI_MSE_NAME_SPACE_ID=4321543215-54325435423-5432   #命名空间ID
```

### 安装
* 首先在项目`composer.json` 文件的根节点下添加`repositories`对象,组件的git地址作为源，并且优先级要高于 `packagist`,如：
```javascript
{
    "repositories": {
        "0": {
            "type": "git",
            "url": "https://codes.your-codes-hub.domain/alicloud/n8config-monitor.git"  //您的私有代码库
        },   
        "packagist": {
            "type": "composer",
            "url": "https://mirrors.aliyun.com/composer/"
        }
    }
    
    .....
}
```

* 执行组件安装命令：`composer require alicloud/n8config-monitor`
    * 如果报无安装权限，则执行 `composer config secure-http false`,表示关闭Https访问限制。
    * 如果`guzzlehttp/guzzle`版本低于`6.5`请升级 `composer update guzzlehttp/guzzle 6.5`

### 启用

#### 确定配置
* 确保 ALI_MSE_AK,ALI_MSE_SK,ALI_MSE_HOST,ALI_MSE_GROUP,GROUP,ALI_MSE_NAME_SPACE_ID(非必须)已经添加到系统环境变量
* 指定配置快照文件存储目录
* 指定配置落地文件， laravel项目即，.env 文件路径
    
#### 编写 console 命令
* Laravel 项目参考：
```PHP

/**
 * 阿里云 MES nacos 配置监控命令
 * https://packagist.org/packages/verystar/aliyun-acm
 */
Artisan::command('alicloud-mes:listenconfig', function(){
    $this->info('阿里云MES配置监控');
    
    //下面两个路径如果不使用默认，则需要确保存在
    $changeToEnvFile = base_path('.env_test');      //env文件路径(确定没问题后修改为 .env)
    $snapshotPath = storage_path();                 //配置快照存储路径
    
    //实例化，并启动监听
    try {
        $monitorInstance  = app(\Alicloud\ConfigMonitor\MonitorHandle::class, [
            'snapshotPath'  => $snapshotPath,
            'changeToEnvFile' => $changeToEnvFile,
            'pullingSenonds' => 30,
            'env'           => env('APP_ENV'),
        ]);
        $monitorInstance->listenNotify();  //开启监控
    } catch(\Exception $err) {
        $errorinfo = $err->getMessage();
        \Illuminate\Support\Facades\Log::error($errorinfo,[
            //'storage_path' => $snapshotPath,
            //'env_test' => $changeToEnvFile
        ]);
    }
    
})->describe('阿里云MES配置监控');


```

#### 运行
```PHP
    JZTech-xxx: huangwh$ php artisan alicloud-mes:listenconfig
    阿里云MES配置监控
    [2022-01-07 11:42:48] nacos-client.INFO: 配置监听中，长轮询时间为10秒 ...   [] []
    [2022-01-07 11:42:48] nacos-client.INFO: 监听到配置有变更，已经更新到本地ENV文件...   [] []
    [2022-01-07 11:42:48] nacos-client.INFO: listener loop count: 1 [] []
    [2022-01-07 11:42:58] nacos-client.INFO: 配置无变化，监听中...  [] []
    [2022-01-07 11:42:58] nacos-client.INFO: listener loop count: 2 [] []
    ...
```

#### 进程常驻
* 建议使用 `supervisor`维护进程常驻。参考：
```$xslt
    [program:monitor]
    directory=/var/www/html
    command=php artisan alicloud-mes:listenconfig
    autostart=true
    autorestart=true
    redirect_stderr=true
    numprocs=2
    stdout_logfile=./storage/logs/n8config-listenconfig.log
    stdout_logfile_maxbytes = 50MB
    stdout_logfile_backups = 3
    process_name=%(program_name)s_%(process_num)02d

```

