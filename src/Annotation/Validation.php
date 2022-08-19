<?php

namespace Tegic\HyperfValidationExtra\Annotation;
use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
#[Attribute(Attribute::TARGET_METHOD)]
class Validation extends AbstractAnnotation
{

    public function __construct(public ?string $validate = null, public ?string $field = null, public ?string $scene = null)
    {

    }

}