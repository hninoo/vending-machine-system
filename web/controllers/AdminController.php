<?php
declare(strict_types=1);

namespace Web\Controllers;

use App\Config\Database;
use App\Enums\HttpMethod;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use PDO;
use Web\Traits\AdminCheckTrait;

class AdminController
{
    use AdminCheckTrait;

    private Admin $adminModel;
    private Product $productModel;
    private Transaction $transactionModel;
    private User $userModel;
    private PDO $connection;

    public function __construct(
        ?Admin $adminModel = null,
        ?Product $productModel = null,
        ?Transaction $transactionModel = null,
        ?User $userModel = null,
        ?PDO $connection = null,
    ) {
        $this->adminModel = $adminModel ?? new Admin();
        $this->productModel = $productModel ?? new Product();
        $this->transactionModel = $transactionModel ?? new Transaction();
        $this->userModel = $userModel ?? new User();
        $this->connection = $connection ?? (new Database())->getConnection();
    }

    public function dashboard(): void
    {
        $this->checkAdmin();

        try {
            $stats = [
                'products' => $this->productModel->count(),
                'users' => $this->userModel->count(),
                'transactions' => $this->transactionModel->count(),
                'revenueToday' => $this->fetchRevenueToday(),
            ];

            $revenueDelta = $this->fetchRevenueDeltaPercent();
            $weeklySales = $this->fetchWeeklySales();
            $recentProducts = $this->fetchRecentProducts(5);
            $recentUsers = $this->fetchRecentUsers(3);
            $recentTransactions = $this->fetchRecentTransactions(5);
            $activity = $this->buildActivityFeed($recentProducts, $recentUsers, $recentTransactions);
            $adminUsername = (string) ($_SESSION['admin_username'] ?? 'Admin');

            require __DIR__ . '/../views/admin/dashboard.php';
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function login(): void
    {
        if (isset($_SESSION['admin_id'])) {
            header('Location: /admin/dashboard');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === HttpMethod::POST->value) {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            $admin = $this->adminModel->authenticate($username, $password);

            if ($admin) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['role'] = UserRole::ADMIN->value;

                header('Location: /admin/dashboard');
                exit();
            }

            $_SESSION['error_message'] = 'Invalid username or password.';
            header('Location: /admin/login');
            exit();
        }

        require __DIR__ . '/../views/admin/login.php';
    }

    public function logout(): void
    {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        session_destroy();
        header('Location: /admin/login');
        exit();
    }

    private function fetchRevenueToday(): float
    {
        $statement = $this->connection->query(
            "SELECT COALESCE(SUM(total_price), 0) AS revenue
             FROM transactions
             WHERE transaction_date >= CURDATE()
               AND transaction_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
        );
        $row = $statement->fetch();
        return (float) ($row['revenue'] ?? 0);
    }

    private function fetchRevenueDeltaPercent(): ?float
    {
        $statement = $this->connection->query(
            "SELECT
                COALESCE(SUM(CASE
                    WHEN transaction_date >= CURDATE()
                     AND transaction_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                    THEN total_price
                END), 0) AS today,
                COALESCE(SUM(CASE
                    WHEN transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                     AND transaction_date < CURDATE()
                    THEN total_price
                END), 0) AS yesterday
             FROM transactions
             WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
               AND transaction_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
        );
        $row = $statement->fetch();
        $today = (float) ($row['today'] ?? 0);
        $yesterday = (float) ($row['yesterday'] ?? 0);
        if ($yesterday <= 0.0) {
            return null;
        }
        return (($today - $yesterday) / $yesterday) * 100.0;
    }

    private function fetchWeeklySales(): array
    {
        $statement = $this->connection->query(
            "SELECT DATE(transaction_date) AS day, COALESCE(SUM(total_price), 0) AS total
             FROM transactions
             WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               AND transaction_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
             GROUP BY DATE(transaction_date)"
        );
        $byDay = [];
        foreach ($statement->fetchAll() as $row) {
            $byDay[(string) $row['day']] = (float) $row['total'];
        }

        $series = [];
        for ($i = 6; $i >= 0; $i--) {
            $ts = strtotime('-' . $i . ' days');
            $key = date('Y-m-d', $ts);
            $series[] = [
                'date' => $key,
                'label' => date('D', $ts),
                'total' => $byDay[$key] ?? 0.0,
            ];
        }
        return $series;
    }

    private function fetchRecentProducts(int $limit): array
    {
        $statement = $this->connection->prepare(
            "SELECT id, name, price, quantity_available, product_badge, old_price
             FROM products
             ORDER BY id DESC
             LIMIT :limit"
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    private function fetchRecentUsers(int $limit): array
    {
        $statement = $this->connection->prepare(
            "SELECT id, username, role FROM users ORDER BY id DESC LIMIT :limit"
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    private function fetchRecentTransactions(int $limit): array
    {
        $statement = $this->connection->prepare(
            "SELECT t.id, t.quantity, t.total_price, t.transaction_date, p.name AS product_name
             FROM transactions t
             JOIN products p ON p.id = t.product_id
             ORDER BY t.id DESC LIMIT :limit"
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    private function buildActivityFeed(array $products, array $users, array $transactions): array
    {
        $feed = [];

        foreach (array_slice($transactions, 0, 3) as $tx) {
            $feed[] = [
                'tone' => 'blue',
                'text' => sprintf(
                    'Transaction <strong>#%d</strong> completed $%s',
                    (int) $tx['id'],
                    number_format((float) $tx['total_price'], 2),
                ),
                'time' => $this->relativeTime((string) ($tx['transaction_date'] ?? 'now')),
            ];
        }

        foreach (array_slice($products, 0, 2) as $product) {
            $tone = (int) $product['quantity_available'] <= 0
                ? 'rose'
                : ((int) $product['quantity_available'] < 10 ? 'amber' : 'teal');
            $message = (int) $product['quantity_available'] <= 0
                ? sprintf('<strong>%s</strong> out of stock', htmlspecialchars((string) $product['name']))
                : sprintf('<strong>%s</strong> stocked with %d units', htmlspecialchars((string) $product['name']), (int) $product['quantity_available']);
            $feed[] = ['tone' => $tone, 'text' => $message, 'time' => 'recent'];
        }

        foreach (array_slice($users, 0, 1) as $user) {
            $feed[] = [
                'tone' => 'amber',
                'text' => sprintf('<strong>%s</strong> account registered', htmlspecialchars((string) $user['username'])),
                'time' => 'recent',
            ];
        }

        return $feed;
    }

    private function relativeTime(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return '';
        }
        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . 'm ago';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . 'h ago';
        }
        if ($diff < 604800) {
            return floor($diff / 86400) . 'd ago';
        }
        return date('M j', $timestamp);
    }
}
