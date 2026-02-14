<?php
require_once __DIR__ . '/BaseController.php';

/**
 * ShippingController handles shipping and courier integrations.
 * Supports RajaOngkir API for shipping cost calculations.
 */
class ShippingController extends BaseController {
    private $rajaongkir_config = [
        'api_key' => 'your-rajaongkir-api-key', // Replace with actual API key
        'base_url' => 'https://api.rajaongkir.com/starter/',
        'account_type' => 'starter' // starter, basic, pro
    ];

    /**
     * Calculate shipping cost.
     */
    public function calculateCost() {
        $origin = intval($_GET['origin'] ?? 0); // Origin city/district ID
        $destination = intval($_GET['destination'] ?? 0); // Destination city/district ID
        $weight = intval($_GET['weight'] ?? 1000); // Weight in grams
        $courier = sanitize($_GET['courier'] ?? 'jne'); // jne, pos, tiki, etc.

        if (!$origin || !$destination) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Origin and destination are required']);
            exit;
        }

        $result = $this->getShippingCost($origin, $destination, $weight, $courier);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Get list of provinces.
     */
    public function getProvinces() {
        $result = $this->rajaongkirRequest('province');

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Get list of cities/districts for a province.
     */
    public function getCities($province_id) {
        $result = $this->rajaongkirRequest('city', ['province' => $province_id]);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Get shipping cost from RajaOngkir API.
     */
    private function getShippingCost($origin, $destination, $weight, $courier) {
        $params = [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier
        ];

        return $this->rajaongkirRequest('cost', $params);
    }

    /**
     * Make request to RajaOngkir API.
     */
    private function rajaongkirRequest($endpoint, $params = []) {
        $url = $this->rajaongkir_config['base_url'] . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'key: ' . $this->rajaongkir_config['api_key']
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return ['error' => 'API request failed: ' . $err];
        } else {
            return json_decode($response, true);
        }
    }

    /**
     * Get shipping options for frontend.
     */
    public function getShippingOptions() {
        $origin = intval($_GET['origin'] ?? 501); // Default: Yogyakarta
        $destination = intval($_GET['destination'] ?? 0);
        $weight = intval($_GET['weight'] ?? 1000);
        $couriers = ['jne', 'pos', 'tiki', 'sicepat', 'wahana'];

        if (!$destination) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Destination is required']);
            exit;
        }

        $options = [];
        foreach ($couriers as $courier) {
            $result = $this->getShippingCost($origin, $destination, $weight, $courier);
            if (isset($result['rajaongkir']['results']) && !empty($result['rajaongkir']['results'])) {
                $courier_data = $result['rajaongkir']['results'][0];
                $options[] = [
                    'courier' => $courier_data['code'],
                    'name' => $courier_data['name'],
                    'costs' => $courier_data['costs']
                ];
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['shipping_options' => $options]);
        exit;
    }

    /**
     * Save shipping preference for order.
     */
    public function saveShipping($order_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('penjualan');
        }

        $courier = sanitize($_POST['courier'] ?? '');
        $service = sanitize($_POST['service'] ?? '');
        $shipping_cost = floatval($_POST['shipping_cost'] ?? 0);
        $estimated_days = sanitize($_POST['estimated_days'] ?? '');

        if (!$courier || !$service || $shipping_cost <= 0) {
            flashMessage('error', 'Data pengiriman tidak lengkap');
            redirect('penjualan/detail/' . $order_id);
        }

        // Update order with shipping info
        runInTransaction(function($conn) use ($order_id, $courier, $service, $shipping_cost, $estimated_days) {
            // Update penjualan with shipping cost
            $stmt = $conn->prepare("UPDATE penjualan SET total_bayar = total_bayar + ? WHERE id = ?");
            $stmt->bind_param('di', $shipping_cost, $order_id);
            $stmt->execute();
            $stmt->close();

            // Save shipping details (you might want to create a shipping_details table)
            $stmt2 = $conn->prepare("INSERT INTO shipping_details (order_id, courier, service, cost, estimated_days, created_at) VALUES (?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE courier = VALUES(courier), service = VALUES(service), cost = VALUES(cost), estimated_days = VALUES(estimated_days)");
            $stmt2->bind_param('issds', $order_id, $courier, $service, $shipping_cost, $estimated_days);
            $stmt2->execute();
            $stmt2->close();
        });

        flashMessage('success', 'Informasi pengiriman berhasil disimpan');
        
        // Send shipping notification
        require_once __DIR__ . '/NotificationController.php';
        $notification = new NotificationController();
        $notification->sendShippingNotification($order_id);
        
        redirect('penjualan/detail/' . $order_id);
    }
}
