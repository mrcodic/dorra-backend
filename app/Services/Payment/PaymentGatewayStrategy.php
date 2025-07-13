<?php
 namespace App\Services\Payment;

 interface PaymentGatewayStrategy
 {
     public function pay(array $payload, array $data): array;
     public function refund(string $transactionId): bool;
 }
