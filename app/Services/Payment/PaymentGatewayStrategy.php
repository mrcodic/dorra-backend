<?php
 namespace App\Services\Payment;

 interface PaymentGatewayStrategy
 {
     public function pay(array $payload, ?array $data): false|array;
     public function refund(string $transactionId): bool;
 }
