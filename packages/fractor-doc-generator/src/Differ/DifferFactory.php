<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Differ;

use SebastianBergmann\Diff\Differ;

final class DifferFactory
{
    public static function create(): Differ
    {
        return new Differ(new FullContextDiffOutputBuilder());
    }
}
