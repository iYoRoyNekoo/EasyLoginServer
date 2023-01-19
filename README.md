# EasyLogin - Server

## action选项
API通过传入action参数区分操作
action定义如下：
| 传入值 | 操作 | 需要参数 | 返回值 |
| ---- | ---- | ---- | ---- |
| login | 登录 | 用户名[username]或邮箱[email]<sup>[1]</sup>，密码[password] | result、token |
| reg | 注册 | 用户名[username]，邮箱[email]，密码[password] | result |
| verify | 验证邮箱可用性 | 用户名[username]或邮箱[email]<sup>[1]</sup>，验证码[code] | result |
| gencode | 生成邮箱验证码 | 用户名[username]或邮箱[email]<sup>[1]</sup> | result |
| modify | 修改 | 用户名[username]或邮箱[email]<sup>[1]</sup>，密码，要修改的字段[target]<sup>[2]</sup>，内容[content] | result |

* <sup>[1]</sup> 若同时传入，则按照用户名登录  
* <sup>[2]</sup> target包括：用户名[username]，邮箱[email]，密码[password]



## 返回值
返回数据为json格式，包含一个result字段、一个msg字段和可能的数据字段  
result字段代表登录结果，参考原因如下：
| 返回代码 | 参考原因 | msg |
| ---- | ---- | ---- |
| 0 | OK | Success |
| 1 | 登录服务已被禁用 | Service is unavailable |
| 2 | 数据库连接失败 | Failed to connect to Database |
| 3 | 无效操作（未知/未传入action） | Undefined action |
| 4 | 缺少参数（见上表，若传参缺失会报此错误码） | Missing parameter |
