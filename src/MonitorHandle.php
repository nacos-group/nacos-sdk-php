<?php
/**
 * Created by PhpStorm.
 * User: huangwh
 * Date: 2021/12/30
 * Time: 10:10
 */

namespace Alicloud\ConfigMonitor;
use Alicloud\ConfigMonitor\nacos\listener\config\GetConfigRequestErrorListener;
use Alicloud\ConfigMonitor\nacos\listener\config\ListenerConfigRequestListener;
use Alicloud\ConfigMonitor\nacos\Nacos;
use Alicloud\ConfigMonitor\nacos\NacosConfig;
use Alicloud\ConfigMonitor\nacos\util\LogUtil;


/**
 * Class MonitorHandle
 * @package AlicloudMonitor
 */
class MonitorHandle
{
    
    //轮询时间
    protected $pullingSenonds = 30;

    //nacos请求地址，包括端口
    protected $nacosHost = null;
    
    //区分配置文件环境
    protected $env = null;
    
    //配置项名称：即项目名称
    protected $dataId = null;
    
    //项目分组：
    protected $group = 'DEFAULT_GROUP';
    
    //租户信息：即命名空间，如果此项目的配置不同部署节点会不一样，则可以设置为多个命名空间
    protected $nameSpaceId = '';
    
    //是否修改laravel 本地 env文件
    protected $changeToEnvFile = null;
    
    
    /**
     * 初始化配置
     * MonitorHandle constructor.
     * @param string  $snapshotPath 配置文件快照存储地点
     * @param string  $changeToEnvFile  实际要用于变更的 项目env文件
     * @param integer  $pullingSenonds  轮询时间 默认30秒
     * @param string  $env 是本地的环境，不同的环境会生成不同的快照目录
     * @throws
     */
    public function __construct($snapshotPath, $changeToEnvFile='', $pullingSenonds=30, $env='def')
    {
        //这些环境变量不能为空（除了AK,SK外其它项每台服务器都可能不一样的）
        $envVars = [
            'ak'        => getenv('ALI_MSE_AK'),
            'sk'        => getenv('ALI_MSE_SK'),
            'host'      => getenv('ALI_MSE_HOST'),
            'data_id'   => getenv('ALI_MSE_DATA_ID'),
            'group'     => getenv('ALI_MSE_GROUP'),
        ];
        $nameSpaceId = getenv('ALI_MSE_NAME_SPACE_ID') ?: '';    //这个可以为空
        foreach ($envVars as $name=>$val) {
            if (empty($val)){
                throw new \Exception(sprintf("无法获取到环境变量:%s", $name), 1);
            }
        }
        if (!is_dir($snapshotPath)){
            throw new \Exception('snapshotPath 应该是一个有效路径', 1);
        }
    
        if (!empty($changeToEnvFile)){
            $dirinfo = pathinfo($changeToEnvFile);
            if (!is_dir($dirinfo['dirname'])){
                throw new \LogicException(sprintf("env文件路径 %s 不存在,请先创建. %s", $dirinfo['dirname'], PHP_EOL), 1);
            }
            $this->changeToEnvFile = $changeToEnvFile;
        }
    
    
    
        //轮询时间
        $pullingSenonds = intval($pullingSenonds);
        $pullingSenonds = ($pullingSenonds >30  || $pullingSenonds <5) ? 30 : $pullingSenonds;
        $this->pullingSenonds = $pullingSenonds;
        //初始化一些值
        $this->nacosHost = $envVars['host'];
        $this->dataId   = $envVars['data_id'];
        $this->env      = $env;
        $this->nameSpaceId = $nameSpaceId;
        $this->group = $envVars['group'];
        
        //设置值，其它参数在init中传入
        NacosConfig::setAk($envVars['ak']);
        NacosConfig::setSk($envVars['sk']);
        NacosConfig::setSnapshotPath($snapshotPath);
        NacosConfig::setIsDebug(false);
        NacosConfig::setLongPullingTimeout($pullingSenonds * 1000);
        
    }
    
    /**
     * 执行监听
     */
    public function listenNotify()
    {
        //注册一个出错通知
        GetConfigRequestErrorListener::add(function($config) {
            if (!$config->getConfig()) {
                //LogUtil::error("获取配置异常，不做任何变更..." . PHP_EOL);
                $config->setChanged(false);  //出错不修改
            }
        });
        
        //添加获取配置变更通知
        ListenerConfigRequestListener::add(function ($config) {
            if ($config){
                LogUtil::info("监听到配置有变更，已经更新到本地ENV文件... \n");
                if (!empty($this->changeToEnvFile)) {
                    //这里会更新到本地env文件
                    file_put_contents($this->changeToEnvFile, $config);
                }
            }
        });
    
    
        LogUtil::info(sprintf("配置监听中，长轮询时间为%s秒 ... %s", $this->pullingSenonds, PHP_EOL));
        Nacos::init(
            $this->nacosHost,
            $this->env,
            $this->dataId,
            $this->group,
            $this->nameSpaceId
        )->listener();
        
    }
    
    
}