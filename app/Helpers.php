<?php

use App\Helpers\ChartHelper;

if (!function_exists('svgLineChart')) {
    function svgLineChart($data, $attribute, $minY, $maxY, $color = '#ef4444')
    {
        return ChartHelper::svgLineChart($data, $attribute, $minY, $maxY, $color);
    }
}
