<?php
class UnixTimeHelper
{
    // Get current timestamp
    public static function now(): int
    {
        return time();
    }

    // Add minutes to a timestamp
    public static function addMinutes(int $timestamp, int $minutes): int
    {
        return $timestamp + ($minutes * 60);
    }

    // Add hours to a timestamp
    public static function addHours(int $timestamp, int $hours): int
    {
        return $timestamp + ($hours * 3600);
    }

    // Add days to a timestamp
    public static function addDays(int $timestamp, int $days): int
    {
        return $timestamp + ($days * 86400);
    }

    // Convert timestamp to MySQL TIME format (HH:MM:SS)
    public static function toMySQLTime(int $timestamp): string
    {
        return date('H:i:s', $timestamp);
    }

    // Convert MySQL TIME (HH:MM:SS) to timestamp for today
    public static function fromMySQLTime(string $mysqlTime): int
    {
        // strtotime will assume today’s date
        return strtotime($mysqlTime);
    }

    // Format timestamp with custom PHP format
    public static function format(int $timestamp, string $format): string
    {
        return date($format, $timestamp);
    }
}
