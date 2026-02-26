<?php

namespace Look\Workflows\Core\Contracts;

interface StepSchema
{
    public function name(): ?string;

    public function label(): ?string;
    public function icon(): ?string;
    public function description(): ?string;
    public function helpText(): ?string;
    public function categories(): array;

    /*
        public function inputs(): array;
        public function outputs(): array;
        public function errors(): array;
    */

    public function fields(): array;

    public function step(array $data = []): Step;

    public function fromArray(array $definition): StepSchema;
    public function toArray(): array;

    /*
        public function import(array $fields): Step;
        public function export(Step $fields): array;
        public function validate(array $fields): array;
    */
}
