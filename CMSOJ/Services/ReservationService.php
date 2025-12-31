<?php

namespace CMSOJ\Services;

class ReservationService
{
    public function validate(array $input): array
    {
        $errors = [];

        if (!isset($input['first_name']) || !preg_match('/^[\p{L}\s\'\-]+$/u', $input['first_name'])) {
            $errors['first_name'] = 'First name must contain only letters.';
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (!preg_match('/^[\d\(\)\-\+]+$/', $input['phone'] ?? '')) {
            $errors['phone'] = 'Please enter a valid phone number.';
        }

        $persons = (int) ($input['persons'] ?? 0);
        if ($persons < 1 || $persons > 20) {
            $errors['persons'] = 'Number of persons must be between 1 and 20.';
        }

        if (!strtotime($input['date'] ?? '') || strtotime($input['date']) < strtotime('today')) {
            $errors['date'] = 'Please enter a valid future date.';
        }

        if (empty($input['time'])) {
            $errors['time'] = 'Please select a time.';
        }

        return $errors;
    }

    public function buildHtml(array $input): string
    {
        $dateFormatted = date('l j F Y', strtotime($input['date']));

        $msg = "Name: {$input['first_name']}<br>";
        $msg .= "Email: {$input['email']}<br>";

        if (!empty($input['phone'])) {
            $msg .= "Phone: {$input['phone']}<br>";
        }

        $msg .= "Persons: {$input['persons']}<br>";
        $msg .= "Date: {$dateFormatted}<br>";
        $msg .= "Time: {$input['time']}<br>";

        if (!empty($input['message'])) {
            $msg .= "Message: " . nl2br(htmlspecialchars($input['message'])) . "<br>";
        }

        return $msg;
    }
}
