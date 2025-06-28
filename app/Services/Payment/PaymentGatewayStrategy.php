<?php
 namespace App\Services\Payment;

 interface PaymentGatewayStrategy
 {
     public function pay(array $data): array;
     public function refund(string $transactionId): bool;
 }
