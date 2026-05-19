<?php
declare(strict_types=1);

namespace Web\Controllers;

use App\Enums\HttpMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use InvalidArgumentException;
use Web\Traits\AdminCheckTrait;

class TransactionsController
{
    use AdminCheckTrait;

    private Transaction $transactionModel;
    private Product $productModel;
    private User $userModel;

    public function __construct(
        ?Transaction $transactionModel = null,
        ?Product $productModel = null,
        ?User $userModel = null,
    ) {
        $this->transactionModel = $transactionModel ?? new Transaction();
        $this->productModel = $productModel ?? new Product();
        $this->userModel = $userModel ?? new User();
    }

    public function index(): void
    {
        $this->checkAdmin();

        try {
            $limit = 5; 
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $offset = ($page - 1) * $limit;
    
            $transactions = $this->transactionModel->all($limit, $offset);
            $totalTransactions = $this->transactionModel->count();
            $totalPages = ceil($totalTransactions / $limit);
            require __DIR__ . '/../views/transaction/list.php';
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function create(): void
    {
        $this->checkAdmin();

        try {
            if ($_SERVER['REQUEST_METHOD'] === HttpMethod::POST->value) {
                $userId = $this->positiveInt($_POST['user_id'] ?? null, 'User ID');
                $productId = $this->positiveInt($_POST['product_id'] ?? null, 'Product ID');
                $quantity = $this->positiveInt($_POST['quantity'] ?? null, 'Quantity');

                if ($userId <= 0 || $productId <= 0 || $quantity <= 0) {
                    throw new InvalidArgumentException("Invalid input data.");
                }
                if (!$this->userModel->get($userId)) {
                    throw new InvalidArgumentException("Invalid user.");
                }

                $product = $this->productModel->get($productId);
                if (!$product || $product['quantity_available'] < $quantity) {
                    throw new InvalidArgumentException("Insufficient stock or invalid product.");
                }

                $totalPrice = (float) $product['price'] * $quantity;
                $this->transactionModel->create($userId, $productId, $quantity, $totalPrice);

                $this->ensureSession();
                $_SESSION['success_message'] = "Transaction created successfully!";
                
                header('Location: /transactions');
                exit();
            }

            $users = $this->userModel->all();
            $products = $this->productModel->all();
            require __DIR__ . '/../views/transaction/add.php';
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function edit(int $id): void
    {
        $this->checkAdmin();

        try {
            $transaction = $this->transactionModel->get($id);
            $users = $this->userModel->all();
            $products = $this->productModel->all();
            require __DIR__ . '/../views/transaction/edit.php';
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function update(int $id): void
    {
        $this->checkAdmin();

        try {
            if ($_SERVER['REQUEST_METHOD'] === HttpMethod::POST->value) {
                $userId = $this->positiveInt($_POST['user_id'] ?? null, 'User ID');
                $productId = $this->positiveInt($_POST['product_id'] ?? null, 'Product ID');
                $quantity = $this->positiveInt($_POST['quantity'] ?? null, 'Quantity');

                if ($userId <= 0 || $productId <= 0 || $quantity <= 0) {
                    throw new InvalidArgumentException("Invalid input data.");
                }
                if (!$this->userModel->get($userId)) {
                    throw new InvalidArgumentException("Invalid user.");
                }

                $product = $this->productModel->get($productId);
                if (!$product || $product['quantity_available'] < $quantity) {
                    throw new InvalidArgumentException("Insufficient stock or invalid product.");
                }

                $totalPrice = (float) $product['price'] * $quantity;
                $this->transactionModel->update($id, $userId, $productId, $quantity, $totalPrice);

                $this->ensureSession();
                $_SESSION['success_message'] = "Transaction updated successfully!";
                
                header('Location: /transactions');
                exit();
            }
        } catch (\Exception $e) {
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

            $this->transactionModel->delete($id);

            $this->ensureSession();
            $_SESSION['success_message'] = "Transaction deleted successfully!";
            
            header('Location: /transactions');
            exit();
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function positiveInt(mixed $value, string $field): int
    {
        if (is_bool($value)) {
            throw new InvalidArgumentException(sprintf('%s must be an integer between 1 and 2147483647.', $field));
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);

        if ($integer === false) {
            throw new InvalidArgumentException(sprintf('%s must be an integer between 1 and 2147483647.', $field));
        }

        return $integer;
    }
}
