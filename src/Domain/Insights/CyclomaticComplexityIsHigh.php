<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Domain\Insights;

use NunoMaduro\PhpInsights\Domain\Contracts\HasDetails;

final class CyclomaticComplexityIsHigh extends Insight implements HasDetails
{
    public function hasIssue(): bool
    {
        $maxComplexity = $this->getMaxComplexity();
        foreach ($this->collector->getClassComplexity() as $complexity) {
            if ($complexity > $maxComplexity) {
                return true;
            }
        }

        return false;
    }

    public function getTitle(): string
    {
        return sprintf('Having `classes` with more than ' .
            $this->getMaxComplexity() .
            ' cyclomatic complexity is prohibited - Consider refactoring');
    }

    /**
     * {@inheritdoc}
     */
    public function getDetails(): array
    {
        $complexityLimit = $this->getMaxComplexity();
        $classesComplexity = array_filter(
            $this->collector->getClassComplexity(),
            static function ($complexity) use ($complexityLimit) {
                return $complexity > $complexityLimit;
            }
        );

        uasort($classesComplexity, static function ($left, $right) {
            return $right - $left;
        });

        $classesComplexity = array_reverse($classesComplexity);

        return array_map(static function ($class, $complexity) {
            return "$class: $complexity cyclomatic complexity";
        }, array_keys($classesComplexity), $classesComplexity);
    }

    private function getMaxComplexity(): int
    {
        return (int) ($this->config['maxComplexity'] ?? 5);
    }
}
