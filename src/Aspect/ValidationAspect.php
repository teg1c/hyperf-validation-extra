<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Tegic\HyperfValidationExtra\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tegic\HyperfValidationExtra\Annotation\Validation;

class ValidationAspect extends AbstractAspect
{
    public $annotations = [
        Validation::class,
    ];
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    public function __construct(ContainerInterface $container, ServerRequestInterface $request)
    {
        $this->container = $container;
        $this->request = $request;
    }

    /**
     * @throws Exception
     * @return mixed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        foreach ($proceedingJoinPoint->getAnnotationMetadata()->method as $validation) {
            /**
             * @var Validation $validation
             */
            if ($validation instanceof Validation) {
                if (! class_exists($validation->validate)) {
                    throw ValidationException::withMessages([
                        '' => sprintf('%s Does not exist', $validation->validate),
                    ]);
                }
                if (empty($this->request) && empty($validation->field)) {
                    throw ValidationException::withMessages([
                        '' => 'Validation Parameter exception',
                    ]);
                }
                $data = [];
                if ($validation->field && array_key_exists($validation->field, $proceedingJoinPoint->arguments['keys'])) {
                    $data = $proceedingJoinPoint->arguments['keys'][$validation->field];
                }
                if ($this->request) {
                    $data = $this->container->get(RequestInterface::class)->all();
                }

                $this->validationData($data, $validation->validate, $validation->scene);
                break;
            }
        }

        return $proceedingJoinPoint->process();
    }

    /**
     * @param $validation
     * @param $verData
     * @param $class
     * @param $proceedingJoinPoint
     * @param mixed $scene
     * @throws ValidationException
     */
    protected function validationData($verData, $class, $scene)
    {
        $class = new $class();
        //场景 验证
        $rules = $this->getRules($class, $scene);
        $message = call_user_func_array([$class, 'messages'], []);
        /** @var ValidatorFactoryInterface $validationFactory */
        $validationFactory = $this->container->get(ValidatorFactoryInterface::class);
        $validator = $validationFactory->make($verData, $rules, $message);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                '' => $validator->errors()->first(),
            ]);
        }
    }

    /**
     * Get scene rules.
     * @param mixed $class
     * @param mixed $scene
     */
    private function getRules($class, $scene)
    {
        $rules = call_user_func_array([$class, 'rules'], []);
        $scenes = call_user_func_array([$class, 'scenes'], []);
        if ($scene && isset($scenes) && is_array($scenes[$scene])) {
            $newRules = [];
            foreach ($scenes[$scene] as $key => $field) {
                if (is_numeric($key)) {
                    array_key_exists($field, $rules) && $newRules[$field] = $rules[$field];
                } else {
                    $newRules[$key] = $field;
                }
            }
            return $newRules;
        }
        return $rules;
    }
}
