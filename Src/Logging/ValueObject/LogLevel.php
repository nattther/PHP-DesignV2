<?php

declare(strict_types=1);

namespace Design\Logging\ValueObject;

/**
 * Defines the available log severity levels.
 *
 * How to read them (from least to most severe):
 * - Debug:    Very detailed information, mainly useful during development
 * - Info:     Normal application events (startup, user actions, etc.)
 * - Notice:   Something worth noting, but not a problem (optional in your project)
 * - Warning:  Something unexpected happened, but the app can continue
 * - Error:    A real problem occurred and needs attention
 * - Critical: A major failure, usually urgent (service down, data corruption risk, etc.)
 *
 * Using an enum prevents typos like "EROR" or "WARN" and keeps the values consistent.
 */
enum LogLevel: string
{
    case Debug = 'DEBUG';
    case Info = 'INFO';
    case Notice = 'NOTICE';
    case Warning = 'WARNING';
    case Error = 'ERROR';
    case Critical = 'CRITICAL';
}
