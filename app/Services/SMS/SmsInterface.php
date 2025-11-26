<?php

namespace App\Services\SMS;

interface SmsInterface
{
    /**
     * Send SMS message to one or more numbers.
     *
     * @param string|array $numbers
     * @param string $message
     * @param array $options optional params like language, sender
     * @return mixed
     */
    public function send(string|array $numbers, string $message, array $options = []);
}
