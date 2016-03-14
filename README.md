# **TestLink管理系统**

>基于T&W公司需求搭建的web测试用例管理系统

###**1. 实现功能**

* 测试项目管理
* 产品需求管理
* 测试计划管理
* 测试用例创建、管理和执行
* 简单的测试报告输出和统计功能

###**2. 环境搭建**

* Win64下使用**wamp**的集成环境搭建，具体参数如下：

<table class="table table-bordered table-striped table-condensed">
    <tr>
        <td>IP</td>
        <td>172.28.4.3:8080/testlink</td>
    </tr>
    <tr>
        <td>Product Version</td>
        <td>1.9.14</td>
    </tr>
    <tr>
        <td>Database</td>
        <td>MySQL-5.6.17</td>
    </tr>
    <tr>
        <td>PHP Version</td>
        <td>PHP/5.5.12</td>
    </tr>
    <tr>
        <td>Apache Version</td>
        <td>Apache/2.4.9 (Win64)</td>
    </tr>
</table>

###**3. 需求目标**

* 完成数据库的备份并定时备份至相关服务器
* 管理人员能够使用TestLink维护更新测试用例
* 测试人员能够使用TestLink测试并记录测试结果
* 通过TestLink能够输出符合需求的测试报告以及相关文档
* 与QIMS系统相关联进行BUG的追踪管理
* 测试用例与测试需求相关联，通过唯一ID进行检索