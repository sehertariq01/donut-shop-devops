<?php
/**
 * Donut Shop Management System - Main Application
 * Features: view products, place orders, view orders, delete orders
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

$message = '';
$messageType = '';

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    $customerName = trim($_POST['customer_name'] ?? '');

    if ($productId <= 0 || $customerName === '') {
        $message = 'Please select a donut and enter your name.';
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare('SELECT name, price FROM products WHERE id = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product) {
            $total = (float) $product['price'] * $quantity;
            $productName = $product['name'];

            $insert = $conn->prepare(
                'INSERT INTO orders (product_id, product_name, customer_name, quantity, total_price, status)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $status = 'pending';
            $insert->bind_param('issids', $productId, $productName, $customerName, $quantity, $total, $status);

            if ($insert->execute()) {
                $message = 'Order placed successfully! Thank you, ' . htmlspecialchars($customerName) . '.';
                $messageType = 'success';
            } else {
                $message = 'Failed to place order. Please try again.';
                $messageType = 'error';
            }
            $insert->close();
        } else {
            $message = 'Selected product not found.';
            $messageType = 'error';
        }
    }
}

// Handle order deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_order') {
    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($orderId > 0) {
        $delete = $conn->prepare('DELETE FROM orders WHERE id = ?');
        $delete->bind_param('i', $orderId);

        if ($delete->execute() && $delete->affected_rows > 0) {
            $message = 'Order deleted successfully.';
            $messageType = 'success';
        } else {
            $message = 'Order not found or could not be deleted.';
            $messageType = 'error';
        }
        $delete->close();
    }
}

// Fetch products for menu and order form
$products = [];
$productResult = $conn->query('SELECT id, name, description, price, emoji FROM products ORDER BY name');
if ($productResult) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch all orders
$orders = [];
$orderResult = $conn->query(
    'SELECT id, product_name, customer_name, quantity, total_price, status, created_at
     FROM orders ORDER BY created_at DESC'
);
if ($orderResult) {
    while ($row = $orderResult->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donut Shop Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <span>🍩</span> Donut Shop
        </div>
        <ul class="navbar-links">
            <li><a href="#menu">Menu</a></li>
            <li><a href="#order">Order</a></li>
            <li><a href="#orders">Orders</a></li>
        </ul>
    </nav>

    <main class="container">
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Donut Menu -->
        <section id="menu" class="section">
            <h2 class="section-title">Our Donuts</h2>
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <p class="empty-state">No products available. Please ensure the database is initialized.</p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <article class="card">
                            <div class="card-image"><?= htmlspecialchars($product['emoji'] ?? '🍩') ?></div>
                            <div class="card-body">
                                <h3 class="card-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="card-description"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="card-price">$<?= number_format((float) $product['price'], 2) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Place Order Form -->
        <section id="order" class="section">
            <h2 class="section-title">Place an Order</h2>
            <div class="form-card">
                <form method="POST" action="#order">
                    <input type="hidden" name="action" value="place_order">

                    <div class="form-group">
                        <label for="customer_name">Your Name</label>
                        <input type="text" id="customer_name" name="customer_name" required
                               placeholder="Enter your name" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="product_id">Select Donut</label>
                        <select id="product_id" name="product_id" required>
                            <option value="">-- Choose a donut --</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= (int) $product['id'] ?>">
                                    <?= htmlspecialchars($product['name']) ?> -
                                    $<?= number_format((float) $product['price'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="1" max="99" value="1" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Place Order</button>
                </form>
            </div>
        </section>

        <!-- Orders Table -->
        <section id="orders" class="section">
            <h2 class="section-title">All Orders</h2>
            <div class="table-wrapper">
                <?php if (empty($orders)): ?>
                    <p class="empty-state">No orders yet. Be the first to order a donut!</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Donut</th>
                                <th>Customer</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= (int) $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td><?= (int) $order['quantity'] ?></td>
                                    <td>$<?= number_format((float) $order['total_price'], 2) ?></td>
                                    <td><?= htmlspecialchars($order['status']) ?></td>
                                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;"
                                              onsubmit="return confirm('Delete this order?');">
                                            <input type="hidden" name="action" value="delete_order">
                                            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Donut Shop Management System | DevOps CI/CD Project</p>
    </footer>
</body>
</html>
