## 安装
~~~
composer require yunwuxin/think-throttle
~~~

> 下面示例均为限制每分钟允许10次访问
>
## 使用方式

### 门面方式
```php
//...
use yunwuxin\throttle\facade\RateLimiter;

//...

$key = 'xxxx';

if(RateLimiter::tooManyAttempts($key, 10))
{
    //超出频率限制了
    throw new \Exception('....');
}

RateLimiter::hit($key, 60);

//....其他操作
```

### 依赖注入方式
```php
use yunwuxin\throttle\RateLimiter;

class SomeClass
{

    public function index(Ratelimiter $limiter)
    {
        $key = 'xxxx';
        
        if($limiter->tooManyAttempts($key, 10))
        {
            //超出频率限制了
            throw new \Exception('....');
        }
        
        $limiter->hit($key, 60);

        //....其他操作
    }
}

```


### 中间件方式
```php
use yunwuxin\throttle\middleware\ThrottleRequests;
use think\facade\Route;

Route::group(function(){
    //路由注册

})->middleware(ThrottleRequests::class, 10);

```

中间件支持3个参数 `$maxAttempts`, `$decayMinutes`, `$prefix`

~~~
...->middleware(ThrottleRequests::class, $maxAttempts, $decayMinutes, $prefix);
~~~
`$maxAttempts`: 可访问次数，默认值60  
`$decayMinutes`: 单位时间，默认值1分钟  
`$prefix`: $key的前缀，默认值为空,可以传入一个闭包，返回一个字符串作为$key，该闭包支持依赖注入
