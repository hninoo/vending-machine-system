<?php
namespace Api\Tests;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Transaction;
use PHPUnit\Framework\TestCase;
use Web\Controllers\ProductsController;
use PHPUnit\Framework\MockObject\MockObject;

class ProductsControllerTest extends TestCase
{
    private ProductsController $controller;
    private MockObject $productModel;
    private MockObject $transactionModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productModel = $this->createMock(Product::class);
        $this->transactionModel = $this->createMock(Transaction::class);

        $_SESSION['role'] = UserRole::ADMIN->value;

        $this->controller = new ProductsController($this->productModel, $this->transactionModel, false);
    }

    public function testIndex(): void
    {
        $this->productModel->method('all')->willReturn([]);
        $this->productModel->method('count')->willReturn(0);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $this->assertStringContainsString('Products', $output);
    }

    public function testCreatePostSuccess(): void
    {
        $this->productModel->expects($this->once())
            ->method('create')
            ->with('Product A', 100.00, 10, 'none', null);

        $_POST['name'] = 'Product A';
        $_POST['price'] = 100.00;
        $_POST['quantity'] = 10;
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_SESSION = ['role' => UserRole::ADMIN->value];

        $this->controller->create();

        $this->assertEquals('Product created successfully!', $_SESSION['success_message']);
    }

    public function testCreatePostValidationFailure(): void
    {
        $_POST['name'] = '';
        $_POST['price'] = 'invalid';
        $_POST['quantity'] = -1;
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->expectException(\InvalidArgumentException::class);

        $this->controller->create();
    }

    public function testUpdatePostSuccess(): void
    {
        $this->productModel->expects($this->once())
            ->method('update')
            ->with(1, 'Updated Product', 150.00, 15, 'none', null);

        $_POST['name'] = 'Updated Product';
        $_POST['price'] = 150.00;
        $_POST['quantity'] = 15;
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_SESSION = ['role' => UserRole::ADMIN->value];

        $this->controller->update(1);

        $this->assertEquals('Product updated successfully!', $_SESSION['success_message']);
    }

    public function testDeleteSuccess(): void
    {
        $this->productModel->expects($this->once())
            ->method('delete')
            ->with(1);

        $_SESSION = ['role' => UserRole::ADMIN->value];

        $this->controller->delete(1);

        $this->assertEquals('Product deleted successfully!', $_SESSION['success_message']);
    }

    public function testPurchaseSuccess(): void
    {
        $this->productModel->method('get')->willReturn(['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'quantity_available' => 10]);
        $this->productModel->expects($this->once())
            ->method('decrementStock')
            ->with(1, 5)
            ->willReturn(true);
        $this->transactionModel->expects($this->once())
            ->method('create')
            ->with(1, 1, 5, 500.00);

        $_POST['user_id'] = 1;
        $_POST['quantity'] = 5;
        $_SESSION = ['role' => UserRole::ADMIN->value];

        $this->controller->purchase(1);

        $this->assertEquals('Purchase completed successfully!', $_SESSION['success_message']);
    }

    public function testPurchaseFailure(): void
    {
        $this->productModel->method('get')->willReturn(['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'quantity_available' => 0]);

        $this->expectException(\Exception::class);

        $_POST['user_id'] = 1;
        $_POST['quantity'] = 5;
        $this->controller->purchase(1);
    }
}
