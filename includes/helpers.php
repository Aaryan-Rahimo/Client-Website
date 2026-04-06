<?php

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
    $token = $_POST['csrf'] ?? '';
    if ($token === '' || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

/**
 * Escape output for HTML.
 */
function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize plain text input (trim + HTML entities for storage display safety).
 */
function clean_str(?string $value): string
{
    return trim(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
}

function send_email(string $to, string $subject, string $body, string $replyTo = ''): bool
{
    $from    = 'noreply@rubydental.com';
    $headers = "From: Ruby's Dental Clinic <{$from}>\r\n";
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

function appointment_status_tag_class(string $status): string
{
    return match ($status) {
        'Pending'     => 'tag admin-tag--pending',
        'Confirmed'   => 'tag admin-tag--confirmed',
        'Rescheduled' => 'tag admin-tag--rescheduled',
        'Checked In'  => 'tag admin-tag--checked-in',
        'Declined'    => 'tag admin-tag--priority-low',
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

/**
 * @return array<string, string> value => label
 */
function appointment_time_slots(): array
{
    return [
        '09:00:00' => '9:00 AM',
        '09:30:00' => '9:30 AM',
        '10:00:00' => '10:00 AM',
        '10:30:00' => '10:30 AM',
        '11:00:00' => '11:00 AM',
        '13:00:00' => '1:00 PM',
        '13:30:00' => '1:30 PM',
        '14:00:00' => '2:00 PM',
        '15:00:00' => '3:00 PM',
        '16:00:00' => '4:00 PM',
        '16:30:00' => '4:30 PM',
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
