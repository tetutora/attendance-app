<?php

if (!function_exists('formatMinutesToTimeString')) {
    function formatMinutesToTimeString($minutes)
    {
        if ($minutes === null || $minutes <= 0) {
            return '00:00';
        }
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }
}

if (!function_exists('convertToHoursMinutes')) {
    function convertToHoursMinutes($minutes) {
        if (!$minutes) return '';
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        return sprintf('%d:%02d', $hours, $minutes);
    }
}
