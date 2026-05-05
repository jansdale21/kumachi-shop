<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private function fakePngUpload(string $filename): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'kumachi_product_image_');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/nXQAAAAASUVORK5CYII='));

        return new UploadedFile($path, $filename, 'image/png', null, true);
    }

    public function test_admin_can_create_product_with_image(): void
    {
        Storage::fake('public');

        $adminRole = Role::query()->create([
            'role_name' => 'admin',
        ]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $category = Category::query()->create([
            'name' => 'Drinks',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Iced Coffee',
            'category_id' => $category->id,
            'base_price' => 120,
            'availability' => 'available',
            'image' => $this->fakePngUpload('coffee.png'),
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::query()->first();

        $this->assertNotNull($product);
        $this->assertNotEmpty($product->image_path);

        $this->assertTrue(Storage::disk('public')->exists($product->image_path));
    }
}
