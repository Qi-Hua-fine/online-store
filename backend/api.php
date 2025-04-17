<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // 处理预检请求
}

$host = 'localhost';
$dbname = 'onlinestore_db';
$username = 'root'; 
$password = 'fangkeshen123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    die(json_encode(['error' => $e->getMessage()]));
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'getProducts':
        getProducts($pdo);
        break;
    case 'addToCart':
        addToCart($pdo);
        break;
    case 'getCart':
    if (isset($_GET['user_id'])) {
        getCart($pdo, $_GET['user_id']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    }
    break;
    default:
        echo json_encode(['status' => 'Invalid action']);
        break;
}

function getProducts(PDO $pdo) {
    try {
        $stmt = $pdo->query('SELECT * FROM products');
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $products]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function addToCart(PDO $pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'];
        $productId = $data['product_id'];
        $quantity = $data['quantity'];

        try {
            $stmt = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $productId, $quantity]);
            echo json_encode(['status' => 'success', 'message' => 'Item added to cart']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
}

function getCart(PDO $pdo, $userId) {
    try {
        $stmt = $pdo->prepare('SELECT products.name as product_name, cart_items.quantity, products.price FROM cart_items INNER JOIN products ON cart_items.product_id = products.id WHERE cart_items.user_id = ?');
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $cartItems]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>



