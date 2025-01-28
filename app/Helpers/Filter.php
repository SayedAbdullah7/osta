<?php

namespace App\Helpers;

use Carbon\Carbon;

class Filter
{
    /**
     * Generate filter data for a select input.
     *
     * @param string $label
     * @param array $options
     * @return array
     */
    public static function select($label, $options)
    {
        return [
            'type' => 'select',
            'label' => $label,
            'options' => $options,
//            'options' => array_map(function ($key, $value) {
//                return ['key' => $key, 'value' => $value];
//            }, array_keys($options), $options),
        ];
    }

    /**
     * Generate filter data for a date input.
     *
     * @param $label
     * @return array
     */
    public static function date($label,$max = null, $min = null)
    {
        if ($min == 'today') {
            $min = Carbon::today()->toDateString();
        }
        if ($max == 'today') {
            $max = Carbon::today()->toDateString();
        }
        return [
            'type' => 'date',
            'label' => $label,
            'min'=> $min,
            'max' => $max
        ];
    }
}
