<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

header('Content-Type: application/json');

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Tambahkan validasi awal
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data received']);
        exit();
    }

    $action = $data['action'] ?? '';
    if (!$action) {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
        exit();
    }

    switch ($action) {
        case 'add':
            $productId = intval($data['product_id']);
            $quantity = intval($data['quantity']);

            if (!$productId) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                exit();
            }

            try {
                // Cek dulu apakah produk ada
                $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                    exit();
                }

                // Cek apakah sudah ada di cart
                $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$_SESSION['user_id'], $productId]);
                $existingItem = $stmt->fetch();

                if ($existingItem) {
                    // Update quantity jika sudah ada
                    $newQuantity = $existingItem['quantity'] + $quantity;
                    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                    $stmt->execute([$newQuantity, $existingItem['id']]);
                } else {
                    // Insert baru jika belum ada
                    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $productId, $quantity]);
                }

                // Ambil total items setelah update
                $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $result = $stmt->fetch();

                echo json_encode([
                    'success' => true,
                    'message' => 'Item added to cart',
                    'total' => $result['total'] ?? 0
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        case 'update':
            try {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$data['quantity'], $data['cart_id'], $_SESSION['user_id']]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        case 'remove':
            try {
                $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$data['cart_id'], $_SESSION['user_id']]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        default:
            // Tambahkan fallback untuk aksi tidak valid
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        // Ambil semua item di keranjang
        $stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Hitung total item
        $totalItems = 0;
        foreach ($items as $item) {
            $totalItems += $item['quantity'];
        }

        // Kembalikan data
        echo json_encode([
            'success' => true,
            'total' => $totalItems,
            'items' => $items
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
