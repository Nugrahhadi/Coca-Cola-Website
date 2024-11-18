<?php
session_start();
require_once 'config.php';

// Buat koneksi database
$database = new Database();
$conn = $database->getConnection();

// Query untuk mengambil data produk
$query = "SELECT * FROM products";
$stmt = $conn->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Coca-Cola</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: #b50009;
            padding: 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo img {
            height: 50px;
            /* Sesuaikan dengan ukuran yang diinginkan */
            margin-left: 30px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.2rem;
        }

        nav a {
            color: #fefefe;
            text-decoration: none;
            font-weight: bold;
            margin-right: 30px;
            font-size: 16px;
            padding: 5px 10px;
            transition: all 0.3s ease;
        }

        .login-btn {
            padding: 8px 16px;
            border: 2px solid white;
            border-radius: 20px;
            background-color: transparent;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background-color: white;
            color: #b50009;
            border-color: #b50009;
        }

        .home {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 2rem;
            background: linear-gradient(45deg, #b50009, #ff0008);
            color: white;
        }

        .home-content {
            max-width: 550px;
        }

        .logo {
            transform: rotate(0deg);
            margin-bottom: 0;
        }

        .home-content img {
            font-size: 4rem;
            margin-top: -100px;
            margin-bottom: -150px;
            margin-left: 40px;
            transform: rotate(0deg);
        }

        .home-content p {
            font-size: 1.3rem;
            line-height: 1.5;
            margin-left: 97px;
            margin-right: -70px;
        }

        .home img {
            max-width: 500px;
            transform: rotate(0deg);
            transition: transform 0.3s;
        }

        .home img:hover {
            transform: rotate(0deg) scale(1.1);
        }

        .rotated-icon img {
            max-width: 500px;
            transform: rotate(-15deg);
            transition: transform 0.3s;
            margin-left: -60px;
        }

        .rotated-icon img:hover {
            transform: rotate(0deg) scale(1.1);
        }

        .products {
            padding: 4rem 2rem;
            background: #b50009;
            padding-top: 100px;
        }

        .products h1 {
            text-align: center;
            color: white;
            font-size: 2.2rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        }

        .products p {
            text-align: center;
            color: white;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            font-style: italic;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }

        .card {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transform: translateY(50px) scale(1);
            opacity: 0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px) scale(2);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
        }

        .card img {
            width: 200px;
            height: 200px;
            object-fit: contain;
            margin-bottom: 1rem;
        }

        .card h4 {
            color: #e61e27;
            margin-bottom: 0.5rem;
        }

        .interactive-section {
            min-height: 100vh;
            background: linear-gradient(180deg, #b50009, #ff0008);
            padding: 4rem 2rem;
            text-align: center;
        }

        .bubble-container {
            position: relative;
            height: 500px;
            overflow: hidden;
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .bubble-container h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-align: center;
            margin-top: 1rem;
        }

        .bubble-container p {
            color: white;
            font-size: 1.2rem;
            text-align: center;
            max-width: 600px;
        }

        .bubble {
            position: absolute;
            background: rgba(255, 253, 254, 0.31);
            border-radius: 50%;
            animation: float 8s infinite;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 500px;
            position: relative;
            text-align: center;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #b50009;
        }

        .modal-content img {
            max-width: 200px;
            margin-bottom: 1rem;
        }

        .modal-content h3 {
            color: #b50009;
            margin-bottom: 1rem;
        }

        .modal-content p {
            margin-bottom: 1rem;
            line-height: 1.5;
            color: #b50009;
        }

        .shop-section {
            background: #fff;
            padding: 4rem 2rem;
        }

        .shop-section h2 {
            text-align: center;
            color: #b50009;
            margin-bottom: 2rem;
        }

        .shop-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }

        .shop-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .shop-card img {
            width: 150px;
            height: 150px;
            object-fit: contain;
            margin-bottom: 1rem;
        }

        .shop-card h4 {
            color: #b50009;
            margin-bottom: 0.5rem;
        }

        .shop-card .price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
        }

        .cart-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .quantity {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .cart-btn {
            padding: 0.5rem 1rem;
            background: #b50009;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .cart-btn:hover {
            background: #8b0007;
        }

        .add-to-cart-btn {
            background: #b50009;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 1rem;
            transition: background 0.3s;
        }

        .add-to-cart-btn:hover {
            background: #8b0007;
        }

        .total-items {
            text-align: center;
            font-size: 1.2rem;
            margin-top: 2rem;
            color: #b50009;
        }

        .custom-confirm-button {
            background-color: #b50009 !important;
            color: white !important;
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .custom-cancel-button {
            background-color: #ddd !important;
            color: #333 !important;
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: bold;
            font-size: 14px;
        }

        footer {
            background: #b50009;
            color: white;
            text-align: center;
            padding: 1rem;
        }

        footer p {
            margin: 0;
        }

        @keyframes float {
            0% {
                transform: translateY(400px);
                opacity: 0;
            }

            20% {
                opacity: 0.45;
            }

            80% {
                opacity: 0.10;
            }

            100% {
                transform: translateY(-100px);
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <!--Navbar-->
    <nav>
        <div class="nav-logo">
            <img src="nav-logo.png" alt="Coca-Cola Logo" />
        </div>
        <div class="nav-links">
            <a href="#home">Home</a>
            <a href="#products">Products</a>
            <a href="#interactive">Experience</a>
            <a href="#shop">Shop</a>
            <?php if ($isLoggedIn): ?>
                <a href="cart.php">Cart (<span id="cart-count">0</span>)</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Home Section -->
    <section id="home" class="home">
        <div class="home-content">
            <img src="white logo.png" alt="Coca-Cola Logo" class="logo" />
            <p>
                Refresh your world with Coca-Cola, the iconic beverage that has
                brought happiness and refreshment to people around the globe since
                1886. Experience the perfect blend of unique taste and sparkling
                bubbles that make every moment special.
            </p>
        </div>
        <div class="rotated-icon">
            <img src="3d coke.png" alt="Coca-Cola Can" />
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="products">
        <h1>Our Product</h1>
        <p>Hey, what's your go-to product? Got any favorites?</p>
        <div class="card-container">
            <div class="card" onclick="openModal('modal-zero')">
                <img src="zero big.png" alt="Coca-Cola Zero" />
                <h4>Coca-Cola Zero</h4>
            </div>

            <div class="card" onclick="openModal('modal-classic')">
                <img src="classic.png" alt="Coca-Cola Classic" />
                <h4>Coca-Cola Classic</h4>
            </div>

            <div class="card" onclick="openModal('modal-y3000')">
                <img src="y3000.png" alt="Coca-Cola Y3000" />
                <h4>Coca-Cola Y3000</h4>
            </div>

            <div class="card" onclick="openModal('modal-light')">
                <img src="light.png" alt="Coca-Cola Light" />
                <h4>Coca-Cola Light</h4>
            </div>
        </div>

        <!-- Modal product -->
        <div class="modal" id="modal-zero">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <img src="zero big.png" alt="Coca-Cola Zero" />
                <h3>Coca-Cola Zero</h3>
                <p>
                    Great Coke taste with zero sugar and zero calories. Coca-Cola Zero
                    Sugar is the perfect drink for those who want the classic taste of
                    Coca-Cola without the sugar.
                </p>
                <div class="price">Rp 10.000</div>
                <div class="cart-controls">
                    <button class="cart-btn" onclick="decreaseQuantity(1, 'shop')">-</button>
                    <span class="quantity" id="shop-qty-1">0</span>
                    <button class="cart-btn" onclick="increaseQuantity(1, 'shop')">+</button>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(1, 'shop')">Add to Cart</button>
            </div>
        </div>

        <div class="modal" id="modal-classic">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <img src="classic.png" alt="Coca-Cola Classic" />
                <h3>Coca-Cola Classic</h3>
                <p>
                    The original and classic taste that has refreshed generations. A
                    perfect balance of sweet and refreshing that has become a global
                    icon since 1886.
                </p>
                <div class="price">Rp 9.000</div>
                <div class="cart-controls">
                    <button class="cart-btn" onclick="decreaseQuantity(2, 'shop')">-</button>
                    <span class="quantity" id="shop-qty-2">0</span>
                    <button class="cart-btn" onclick="increaseQuantity(2, 'shop')">+</button>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(2, 'shop')">Add to Cart</button>
            </div>
        </div>

        <div class="modal" id="modal-y3000">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <img src="y3000.png" alt="Coca-Cola Y3000" />
                <h3>Coca-Cola Y3000</h3>
                <p>
                    Experience the future of refreshment with Coca-Cola Y3000. A limited
                    edition flavor created with AI to bring you a taste of tomorrow,
                    today.
                </p>
                <div class="price">Rp 13.000</div>
                <div class="cart-controls">
                    <button class="cart-btn" onclick="decreaseQuantity(3, 'shop')">-</button>
                    <span class="quantity" id="shop-qty-3">0</span>
                    <button class="cart-btn" onclick="increaseQuantity(3, 'shop')">+</button>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(3, 'shop')">Add to Cart</button>
            </div>
        </div>

        <div class="modal" id="modal-light">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <img src="light.png" alt="Coca-Cola Light" />
                <h3>Coca-Cola Light</h3>
                <p>
                    A lighter way to enjoy your favorite beverage. Coca-Cola Light
                    offers the same refreshing taste with fewer calories, perfect for
                    those watching their calorie intake.
                </p>
                <div class="price">Rp 10.000</div>
                <div class="cart-controls">
                    <button class="cart-btn" onclick="decreaseQuantity(4, 'shop')">-</button>
                    <span class="quantity" id="shop-qty-4">0</span>
                    <button class="cart-btn" onclick="increaseQuantity(4, 'shop')">+</button>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(4, 'shop')">Add to Cart</button>
            </div>
        </div>
    </section>

    <!-- Interactive Section -->
    <section id="interactive" class="interactive-section">
        <div class="bubble-container" id="bubbleContainer">
            <h1>Explore Your Spirit with Coca-Cola</h1>
            <p>
                Don't forget to drink
                <span style="font-weight: bold">Coca-Cola</span> to charge your energy
                and mood
            </p>
        </div>
    </section>

    <!-- Shopping Section -->
    <section id="shop" class="shop-section">
        <h2>Buy Your Favorite Coca-Cola</h2>
        <div class="shop-container">
            <div class="shop-card">
                <img src="zero big.png" alt="Coca-Cola Zero" />
                <h4>Coca-Cola Zero</h4>
                <div class="price">Rp 10.000</div>
                <div class="cart-controls">
                    <button class="cart-btn" onclick="decreaseQuantity(1)">-</button>
                    <span class="quantity" id="qty-1">0</span>
                    <button class="cart-btn" onclick="increaseQuantity(1)">+</button>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(1)">Add to Cart</button>
            </div>

            <div class="shop-card">
                <img src="classic.png" alt="Coca-Cola Classic" />
                <h4>Coca-Cola Classic</h4>
                <div class="price">Rp 9.000</div>
                <div class="cart-controls">
                    <button class="cart-btn" onclick="decreaseQuantity(2)">-</button>
                    <span class="quantity" id="qty-2">0</span>
                    <button class="cart-btn" onclick="increaseQuantity(2)">+</button>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(2)">Add to Cart</button>
            </div>

            <div class="shop-card">
                <img src="y3000.png" alt="Coca-Cola Y3000" />
                <h4>Coca-Cola Y3000</h4>
                <div class="price">Rp 13.000</div>
                <div class="cart-controls">
                    <button class="cart-btn" onclick="decreaseQuantity(3)">-</button>
                    <span class="quantity" id="qty-3">0</span>
                    <button class="cart-btn" onclick="increaseQuantity(3)">+</button>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(3)">Add to Cart</button>
            </div>

            <div class="shop-card">
                <img src="light.png" alt="Coca-Cola Light" />
                <h4>Coca-Cola Light</h4>
                <div class="price">Rp 10.000</div>
                <div class="cart-controls">
                    <button class="cart-btn" onclick="decreaseQuantity(4)">-</button>
                    <span class="quantity" id="qty-4">0</span>
                    <button class="cart-btn" onclick="increaseQuantity(4)">+</button>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(4)">Add to Cart</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>© 2024 Coca-Cola | @hadinugross_</p>
    </footer>

    <script>
        let cartQuantities = {};
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener("click", function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute("href")).scrollIntoView({
                    behavior: "smooth",
                });
            });
        });

        // Animate cards on scroll
        gsap.registerPlugin(ScrollTrigger);

        gsap.utils.toArray(".card").forEach((card) => {
            gsap.to(card, {
                scrollTrigger: {
                    trigger: card,
                    start: "top bottom-=100",
                    toggleActions: "play none none reverse",
                },
                y: 0,
                opacity: 1,
                duration: 0.8,
            });
        });

        // Create bubbles
        function createBubble() {
            const bubble = document.createElement("div");
            bubble.className = "bubble";

            const size = Math.random() * 30 + 10;
            bubble.style.width = `${size}px`;
            bubble.style.height = `${size}px`;
            bubble.style.left = `${Math.random() * 100}%`;

            bubble.style.animationDuration = `${Math.random() * 4 + 6}s`;

            document.getElementById("bubbleContainer").appendChild(bubble);

            bubble.addEventListener("animationend", () => {
                bubble.remove();
            });
        }

        setInterval(createBubble, 1000);

        // Update fungsi yang sudah ada dan tambahkan fungsi baru
        function updateCart(productId, quantity) {
            fetch('cart_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update',
                        product_id: productId,
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartDisplay();
                    } else {
                        alert('Failed to update cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function removeFromCart(productId) {
            fetch('cart_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartDisplay();
                        updateQuantityDisplays(productId, 0);
                    } else {
                        alert('Failed to remove item');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function updateQuantityDisplays(productId, quantity) {
            cartQuantities[productId] = parseInt(quantity);

            // Update displays in both shop and modal
            const shopQtyElement = document.getElementById(`shop-qty-${productId}`);
            if (shopQtyElement) {
                shopQtyElement.textContent = quantity;
            }

            const modalQtyElement = document.getElementById(`qty-${productId}`);
            if (modalQtyElement) {
                modalQtyElement.textContent = quantity;
            }
        }

        function increaseQuantity(productId, section) {
            const currentQty = cartQuantities[productId] || 0;
            const newQty = currentQty + 1;
            updateQuantityDisplays(productId, newQty);
        }

        function decreaseQuantity(productId, section) {
            const currentQty = cartQuantities[productId] || 0;
            if (currentQty > 0) {
                const newQty = currentQty - 1;
                updateQuantityDisplays(productId, newQty);
            }
        }

        function addToCart(productId) {
            // Check if user is logged in
            <?php if (!$isLoggedIn): ?>
                Swal.fire({
                    title: 'Not Logged In',
                    text: 'You need to login first to add items to the cart.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Go to Login',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'custom-confirm-button',
                        cancelButton: 'custom-cancel-button'
                    },
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
                return;
            <?php endif; ?>

            const quantity = cartQuantities[productId] || 1;

            fetch('cart_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add',
                        product_id: productId,
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Item added to cart successfully',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Update cart count
                        const cartCountElement = document.getElementById('cart-count');
                        if (cartCountElement) {
                            cartCountElement.textContent = data.total;
                        }

                        // Reset quantity
                        updateQuantityDisplays(productId, 0);
                    } else {
                        throw new Error(data.message || 'Failed to add item to cart');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: error.message,
                        icon: 'error'
                    });
                });
        }

        function updateCartDisplay() {
            fetch('cart_operations.php?action=get')
                .then(response => response.json())
                .then(data => {
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement && data.success) {
                        cartCountElement.textContent = data.total || 0; // Perbarui jumlah total item
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        document.addEventListener("DOMContentLoaded", () => {
            updateCartDisplay(); // Memperbarui jumlah item saat halaman dimuat
        });


        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close modal when clicking X button
        document.querySelectorAll('.close-btn').forEach(btn => {
            btn.onclick = function() {
                this.closest('.modal').style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        };
    </script>
</body>

</html>