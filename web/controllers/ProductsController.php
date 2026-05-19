<?php
declare(strict_types=1);

namespace Web\Controllers;

use App\Attributes\Route;
use App\Config\Database;
use App\Enums\HttpMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Support\ProductPayload;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Web\Traits\AdminCheckTrait;

class ProductsController
{
    use AdminCheckTrait;

    private Product $productModel;
    private Transaction $transactionModel;
    private bool $terminate;
    private ?PDO $connection;
    private ?User $userModel;

    public function __construct(
        ?Product $productModel = null,
        ?Transaction $transactionModel = null,
        bool $terminate = true,
        ?PDO $connection = null,
        ?User $userModel = null,
    ) {
        $this->connection = ($productModel === null && $transactionModel === null)
            ? ($connection ?? (new Database())->getConnection())
            : $connection;
        $this->productModel = $productModel ?? new Product($this->connection);
        $this->transactionModel = $transactionModel ?? new Transaction($this->connection);
        $this->userModel = $userModel ?? (($productModel === null && $transactionModel === null) ? new User($this->connection) : null);
        $this->terminate = $terminate;
    }

    public function index(): void
    {
        $this->checkAdmin();

        try {
            $limit = 5; 
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $sortBy = (string) ($_GET['sort'] ?? 'id');
            $direction = (string) ($_GET['direction'] ?? 'ASC');
            $offset = ($page - 1) * $limit;
    
            $products = $this->productModel->all($limit, $offset, $sortBy, $direction);
            $totalProducts = $this->productModel->count();
            $totalPages = ceil($totalProducts / $limit);
            require __DIR__ . '/../views/product/list.php';
        } catch (InvalidArgumentException $e) {
            if (!$this->terminate) {
                throw $e;
            }
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function create(): void
    {
        $this->checkAdmin();

        try {
            if ($_SERVER['REQUEST_METHOD'] === HttpMethod::POST->value) {
                $product = ProductPayload::fromArray($_POST);

                $this->productModel->create(
                    $product->name,
                    $product->price,
                    $product->quantity,
                    $product->badge->value,
                    $product->oldPrice,
                );

                $this->ensureSession();
                $_SESSION['success_message'] = "Product created successfully!";

                $this->redirect('/products');
                return;
            }

            require __DIR__ . '/../views/product/add.php';
        } catch (InvalidArgumentException $e) {
            if (!$this->terminate) {
                throw $e;
            }
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function edit(int $id): void
    {
        $this->checkAdmin();
        $product = $this->productModel->get($id);
        require __DIR__ . '/../views/product/edit.php';
    }

    public function update(int $id): void
    {
        $this->checkAdmin();

        try {
            if ($_SERVER['REQUEST_METHOD'] === HttpMethod::POST->value) {
                $product = ProductPayload::fromArray($_POST);

                $this->productModel->update(
                    $id,
                    $product->name,
                    $product->price,
                    $product->quantity,
                    $product->badge->value,
                    $product->oldPrice,
                );
                $this->ensureSession();
                $_SESSION['success_message'] = "Product updated successfully!";

                $this->redirect('/products');
                return;
            }
        } catch (InvalidArgumentException $e) {
            if (!$this->terminate) {
                throw $e;
            }
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function delete(int $id): void
    {
        $this->checkAdmin();

        try {
            if ($id <= 0) {
                throw new InvalidArgumentException("Invalid ID provided");
            }

            $this->productModel->delete($id);

            $this->ensureSession();
            $_SESSION['success_message'] = "Product deleted successfully!";
            
            $this->redirect('/products');
            return;
        } catch (InvalidArgumentException $e) {
            if (!$this->terminate) {
                throw $e;
            }
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function showPurchase(int $id): void
    {
        $this->checkAdmin();
        $product = $this->productModel->get($id);

        if (!$product) {
            echo "Error: Product not found.";
            exit();
        }

        require __DIR__ . '/../views/product/purchase.php';
    }

    #[Route(HttpMethod::POST, '/products/{id}/purchase')]
    public function purchase(int $id): void
    {
        $this->checkAdmin();

        $userId = $this->positiveInt($_POST['user_id'] ?? null, 'User ID');
        $quantity = $this->positiveInt($_POST['quantity'] ?? null, 'Quantity');
        $product = $this->productModel->get($id);

        if (!$product) {
            throw new RuntimeException('Product not found.');
        }

        $availableQuantity = (int) $product['quantity_available'];
        if ($userId <= 0) {
            throw new RuntimeException('Valid user ID is required.');
        }
        if ($this->userModel !== null && !$this->userModel->get($userId)) {
            throw new RuntimeException('Valid user ID is required.');
        }
        if ($quantity <= 0 || $availableQuantity < $quantity) {
            throw new RuntimeException('Insufficient product quantity.');
        }

        $totalPrice = (float) $product['price'] * $quantity;

        try {
            if ($this->connection) {
                $this->connection->beginTransaction();
            }
            if (!$this->productModel->decrementStock($id, $quantity)) {
                throw new RuntimeException('Insufficient product quantity.');
            }
            $this->transactionModel->create($userId, $id, $quantity, $totalPrice);
            if ($this->connection) {
                $this->connection->commit();
            }
        } catch (\Throwable $e) {
            if ($this->connection && $this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $e;
        }

        $this->ensureSession();
        $_SESSION['success_message'] = "Purchase completed successfully!";

        $this->redirect('/products');
    }

    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function redirect(string $location): void
    {
        if ($this->terminate) {
            header('Location: ' . $location);
            exit();
        }
    }

    private function positiveInt(mixed $value, string $field): int
    {
        if (is_bool($value)) {
            throw new RuntimeException(sprintf('%s must be an integer between 1 and 2147483647.', $field));
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);

        if ($integer === false) {
            throw new RuntimeException(sprintf('%s must be an integer between 1 and 2147483647.', $field));
        }

        return $integer;
    }
}
