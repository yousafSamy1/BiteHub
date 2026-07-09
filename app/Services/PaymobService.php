<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected string $apiKey;
    protected string $integrationId;
    protected string $iframeId;
    protected string $hmacSecret;
    protected string $baseUrl = 'https://accept.paymob.com/api';

    public function __construct()
    {
        $this->apiKey        = env('PAYMOB_API_KEY');
        $this->integrationId = env('PAYMOB_INTEGRATION_ID');
        $this->iframeId      = env('PAYMOB_IFRAME_ID');
        $this->hmacSecret    = env('PAYMOB_HMAC_SECRET');
    }

    /**
     * Step 1: Authentication Request
     */
    public function authenticate()
    {
        try {
            $response = Http::post("{$this->baseUrl}/auth/tokens", [
                'api_key' => $this->apiKey
            ]);

            if ($response->successful()) {
                return $response->json('token');
            }

            Log::error('Paymob Auth Failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob Auth Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Step 2: Order Registration Request
     */
    public function createOrder(string $token, float $amount, array $items = [], string $merchantOrderId = null)
    {
        try {
            $data = [
                'auth_token'      => $token,
                'delivery_needed' => 'false',
                'amount_cents'    => (int) round($amount * 100),
                'currency'        => 'EGP',
                'items'           => $items,
            ];

            if ($merchantOrderId) {
                $data['merchant_order_id'] = $merchantOrderId;
            }

            $response = Http::post("{$this->baseUrl}/ecommerce/orders", $data);

            if ($response->successful()) {
                return $response->json('id');
            }

            Log::error('Paymob Order Registration Failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob Order Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Step 3: Payment Key Request
     */
    public function getPaymentKey(string $token, int $paymobOrderId, float $amount, array $billingData)
    {
        try {
            $response = Http::post("{$this->baseUrl}/acceptance/payment_keys", [
                'auth_token'     => $token,
                'amount_cents'   => (int) round($amount * 100),
                'expiration'     => 3600, // 1 hour
                'order_id'       => $paymobOrderId,
                'billing_data'   => [
                    'apartment'     => $billingData['apartment'] ?? 'NA',
                    'email'         => $billingData['email'] ?? 'test@test.com',
                    'floor'         => $billingData['floor'] ?? 'NA',
                    'first_name'    => $billingData['first_name'] ?? 'Guest',
                    'street'        => $billingData['street'] ?? 'NA',
                    'building'      => $billingData['building'] ?? 'NA',
                    'phone_number'  => $billingData['phone_number'] ?? '01000000000',
                    'shipping_method' => 'PKG',
                    'postal_code'   => $billingData['postal_code'] ?? 'NA',
                    'city'          => $billingData['city'] ?? 'NA',
                    'country'       => $billingData['country'] ?? 'EG',
                    'last_name'     => $billingData['last_name'] ?? 'User',
                    'state'         => $billingData['state'] ?? 'NA',
                ],
                'currency'       => 'EGP',
                'integration_id' => $this->integrationId,
                'lock_order_when_paid' => 'false'
            ]);

            if ($response->successful()) {
                return $response->json('token');
            }

            Log::error('Paymob Payment Key Failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob Payment Key Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get the final Iframe URL for redirection
     */
    public function getIframeUrl(string $paymentToken): string
    {
        return "https://accept.paymob.com/api/acceptance/iframes/{$this->iframeId}?payment_token={$paymentToken}";
    }

    /**
     * Verify HMAC Signature
     */
    public function verifyHmac(array $data, string $hmac): bool
    {
        // Helper to get string representation of boolean-like values
        $boolStr = function($val) {
            if (is_bool($val)) return $val ? 'true' : 'false';
            if (is_string($val)) return strtolower($val) === 'true' ? 'true' : 'false';
            return $val ? 'true' : 'false';
        };

        // Ensure we retrieve the order ID correctly if it's an object/array in the source but flat in the HMAC
        $orderId = '';
        if (isset($data['order'])) {
            $orderId = is_array($data['order']) ? ($data['order']['id'] ?? '') : $data['order'];
        }

        $string = ($data['amount_cents'] ?? '') .
                  ($data['created_at'] ?? '') .
                  ($data['currency'] ?? '') .
                  $boolStr($data['error_occured'] ?? false) .
                  $boolStr($data['has_parent_transaction'] ?? false) .
                  ($data['id'] ?? '') .
                  ($data['integration_id'] ?? '') .
                  $boolStr($data['is_3d_secure'] ?? false) .
                  $boolStr($data['is_auth'] ?? false) .
                  $boolStr($data['is_capture'] ?? false) .
                  $boolStr($data['is_refunded'] ?? false) .
                  $boolStr($data['is_standalone_payment'] ?? false) .
                  $boolStr($data['is_voided'] ?? false) .
                  $orderId .
                  ($data['owner'] ?? '') .
                  $boolStr($data['pending'] ?? false) .
                  ($data['source_data_pan'] ?? '') .
                  ($data['source_data_sub_type'] ?? '') .
                  ($data['source_data_type'] ?? '') .
                  $boolStr($data['success'] ?? false);

        $calculatedHmac = hash_hmac('sha512', $string, $this->hmacSecret);

        return hash_equals($calculatedHmac, $hmac);
    }
}
