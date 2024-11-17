<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Fungsi untuk mendapatkan items dari cart
function getCartItems() {
    $database = new Database();
    $conn = $database->getConnection();
    
    try {
        $stmt = $conn->prepare("
            SELECT c.id as cart_id, p.id as product_id, p.name, p.price, c.quantity, p.image_url
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

$cartItems = getCartItems();
$totalAmount = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart - Coca Cola</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .cart-page {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .cart-items {
            margin-top: 2rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-right: 1rem;
        }

        .item-details {
            flex: 1;
        }

        .item-total {
            font-weight: bold;
            font-size: 1.2rem;
            color: #b50009;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1rem 0;
        }

        .quantity-controls button {
            padding: 0.5rem 1rem;
            background: #b50009;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .cart-total {
            margin-top: 2rem;
            text-align: right;
            font-size: 1.5rem;
        }

        .checkout-btn {
            padding: 1rem 2rem;
            background: #b50009;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2rem;
            margin-top: 1rem;
        }

        .empty-cart {
            text-align: center;
            margin-top: 2rem;
            font-size: 1.2rem;
            color: #666;
        }

        .back-to-shop {
            display: inline-block;
            margin-top: 1rem;
            color: #b50009;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="cart-page">
        <h2>Your Shopping Cart</h2>
        
        <?php if(empty($cartItems)): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="product.php" class="back-to-shop">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach($cartItems as $item): 
                    $itemTotal = $item['price'] * $item['quantity'];
                    $totalAmount += $itemTotal;
                ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>Price: Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                            <div class="quantity-controls">
                                <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                                <span><?php echo $item['quantity']; ?></span>
                                <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                                <button onclick="removeItem(<?php echo $item['cart_id']; ?>)">Remove</button>
                            </div>
                        </div>
                        <div class="item-total">
                            Rp <?php echo number_format($itemTotal, 0, ',', '.'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-total">
                <h3>Total: Rp <?php echo number_format($totalAmount, 0, ',', '.'); ?></h3>
                <button class="checkout-btn" onclick="checkout()">Processed to Checkout</button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateQuantity(cartId, newQuantity) {
            if(newQuantity < 1) {
                removeItem(cartId);
                return;
            }

            fetch('cart_operations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    cart_id: cartId,
                    quantity: newQuantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        }

        function removeItem(cartId) {
            if(confirm('Are you sure you want to remove this item?')) {
                fetch('cart_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        cart_id: cartId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    }
                });
            }
        }

        function checkout() {
            alert('Checkout functionality will be implemented here');
            // Implement checkout logic
        }
    </script>
</body>
</html>