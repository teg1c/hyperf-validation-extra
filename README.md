# hyperf/validation 扩展组件

场景规则覆盖、注解

## 安装
```
composer require tegic/hyperf-validation-extra
```

## 使用

验证器  `app/Validate/TestValidate.php`
```php
<?php

declare(strict_types=1);
/**
 * This file is part of Backend Skeleton.
 *
 * @Auther     tegic
 * @Contact    teg1c@foxmail.com
 */
namespace App\Validate;

class TestValidate
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => '登录信息不存在',
        ];
    }

    public function scenes()
    {
        return [
            'test' => [
                'user_id',
                'password' => 'required',//这里新增了一个验证规则，底层会自动新增
            ],
        ];
    }
}

```

注解调用 `app/Controller/IndexController.php`

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Backend Skeleton.
 *
 * @Auther     tegic
 * @Contact    teg1c@foxmail.com
 */
namespace App\Controller;

use App\Validate\TestValidate;
use Tegic\HyperfValidationExtra\Annotation\Validation;

class IndexController extends AbstractController
{
    /**
     * 不指定参数 默认验证数据从 `$this->request->all()` 获取，场景值为 `test`
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Validation(validate: TestValidate::class,field: "",scene: "test")]
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();
        $this->test($this->request->all());
        return $this->success($this->request->all());
    }

    /**
     * 指定参数为 `params` 参数为数组形式 
     * @param $params
     */
     #[Validation(validate: TestValidate::class,field: "params",scene: "test")]
    protected function test($params)
    {
    }
}

```

## 注意

需要自行捕获异常 `Hyperf\Validation\ValidationException`

```
if ($throwable instanceof ValidationException) {
    $message = $throwable->validator->errors()->first();
}
```