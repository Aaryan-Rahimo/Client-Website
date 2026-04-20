<?php

/*
 * Author: Aaryan, Kissan, Inderbir, Angad
 * Date Created: 2026-04-04
 * Description: Helper functions for the clinic web application, including CSRF token generation and verification, string sanitization, email sending, date/time formatting, appointment check-in logic, and CSS class mapping for
 */

declare(strict_types=1);

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}


function verify_csrf(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = (string) (filter_input(INPUT_POST, 'csrf', FILTER_UNSAFE_RAW) ?? '');
    $token = trim($token);
    if ($token === '' || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

function request_post_string(string $key): string
{
    $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);
    return trim((string) ($value ?? ''));
}

function request_get_string(string $key): string
{
    $value = filter_input(INPUT_GET, $key, FILTER_UNSAFE_RAW);
    return trim((string) ($value ?? ''));
}

function request_post_int(string $key): int
{
    $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
    return ($value === false || $value === null) ? 0 : (int) $value;
}


function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}


function clean_str(?string $value): string
{
    return trim(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
}

function send_email(string $to, string $subject, string $body, string $replyTo = ''): bool
{
    $from    = 'noreply@ephesiansdental.com';
    $headers = "From: Ephesians Dental <{$from}>\r\n";
    $headers .= 'Reply-To: ' . ($replyTo !== '' ? $replyTo : $from) . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();

    return mail($to, $subject, wordwrap($body, 70), $headers);
}

function format_time_ampm(string $time): string
{
    $ts = strtotime($time);
    if ($ts === false) {
        return $time;
    }
    return date('g:i A', $ts);
}

function format_date_long(string $date): string
{
    $ts = strtotime($date);
    if ($ts === false) {
        return $date;
    }
    return date('M j, Y', $ts);
}

function appointment_can_check_in(string $date, string $timeStart, ?DateTimeImmutable $now = null): bool
{
    $appointmentAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date . ' ' . $timeStart);
    if (!$appointmentAt) {
        return false;
    }

    $current = $now ?? new DateTimeImmutable('now');
    $openAt  = $appointmentAt->modify('-30 minutes');

    return $current >= $openAt;
}

function appointment_status_tag_class(string $status): string
{
    return match ($status) {
        'Pending'     => 'tag admin-tag--pending',
        'Confirmed'   => 'tag admin-tag--confirmed',
        'Rescheduled' => 'tag admin-tag--rescheduled',
        'Checked In'  => 'tag admin-tag--checked-in',
        'Completed'   => 'tag admin-tag--approved',
        'Declined'    => 'tag admin-tag--declined',
        default       => 'tag admin-tag--priority-low',
    };
}

function message_priority_tag_class(string $priority): string
{
    return match ($priority) {
        'High'   => 'tag admin-tag--priority-high',
        'Medium' => 'tag admin-tag--priority-medium',
        'Low'    => 'tag admin-tag--priority-low',
        default  => 'tag admin-tag--priority-low',
    };
}

function appointment_time_slots(): array
{
    return [
        '10:00:00' => '10:00 AM',
        '10:30:00' => '10:30 AM',
        '11:00:00' => '11:00 AM',
        '11:30:00' => '11:30 AM',
        '12:00:00' => '12:00 PM',
        '12:30:00' => '12:30 PM',
        '13:00:00' => '1:00 PM',
        '13:30:00' => '1:30 PM',
        '14:00:00' => '2:00 PM',
        '14:30:00' => '2:30 PM',
        '15:00:00' => '3:00 PM',
        '15:30:00' => '3:30 PM',
        '16:00:00' => '4:00 PM',
        '16:30:00' => '4:30 PM',
        '17:00:00' => '5:00 PM',
        '17:30:00' => '5:30 PM',
    ];
}

function detect_message_priority(string $subject, string $body): string
{
    $text = strtolower($subject . ' ' . $body);
    $high = ['pain', 'emergency', 'urgent', 'broken', 'bleeding', 'swelling'];
    foreach ($high as $word) {
        if (str_contains($text, $word)) {
            return 'High';
        }
    }
    $med = ['billing', 'invoice', 'payment', 'charge', 'cost'];
    foreach ($med as $word) {
        if (str_contains($text, $word)) {
            return 'Medium';
        }
    }
    return 'Low';
}
