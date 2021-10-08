<?php

namespace Tegic\HyperfValidationExtra\Annotation;
use Hyperf\Di\Annotation\AbstractAnnotation;
/**
 * @Annotation
 * @Target("METHOD")
 */
class Validation extends AbstractAnnotation
{
    /**
     * 验证器.
     * @var string
     */
    public $validate = '';

    /**
     * 验证参数.
     * @var string
     */
    public $field = '';

    /**
     * 验证场景.
     * @var string
     */
    public $scene = '';

    public function __construct($value = null)
    {
        parent::__construct($value);
    }
}