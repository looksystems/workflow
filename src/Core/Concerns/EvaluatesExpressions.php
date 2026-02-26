<?php

namespace Look\Workflows\Core\Concerns;

use Look\Workflows\Core\Support\Expression\ExpressionFunction;
use Look\Workflows\Core\Support\FluentData;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

trait EvaluatesExpressions
{
    protected ExpressionLanguage $expressionLanguage;

    // EXPRESSION

    public function evaluate(string $expression, array $context = [])
    {
        if ($expression === '') {
            return null;
        }

        $language = $this->makeExpressionLanguage();
        $context = $this->prepareExpressionContext($context);

        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $context[$key] = FluentData::make($value);
            }
        }

        return $language->evaluate($expression, $context);
    }

    // LANGUAGE

    public function makeExpressionLanguage(): ExpressionLanguage
    {
        if (!isset($this->expressionLanguage)) {
            $this->expressionLanguage = $this->prepareExpressionLanguage(new ExpressionLanguage);
        }

        return $this->expressionLanguage;
    }

    protected function prepareExpressionLanguage(ExpressionLanguage $language): ExpressionLanguage
    {
        $this->registerExpressionFunctions($language);

        return $language;
    }

    // FUNCTIONS

    public function registerExpressionFunctions(ExpressionLanguage $language): self
    {
        $functions = $this->getExpressionFunctions();
        foreach ($functions as $name => $function) {
            $language->register(
                $name,
                function () {},
                $function
            );
        }

        return $this;
    }

    protected function getExpressionFunctions(): array
    {
        return [
            'value' => ExpressionFunction::value(),
            'count' => ExpressionFunction::count(),
            'min' => ExpressionFunction::min(),
            'max' => ExpressionFunction::max(),
            'avg' => ExpressionFunction::average(),
            'average' => ExpressionFunction::average(),
            'median' => ExpressionFunction::median(),
            'sum' => ExpressionFunction::median(),
            'query' => ExpressionFunction::query(),
            'filter' => ExpressionFunction::filter(),
        ];
    }

    // CONTEXT

    protected function prepareExpressionContext(array $context): array
    {
        return $context;
    }
}
