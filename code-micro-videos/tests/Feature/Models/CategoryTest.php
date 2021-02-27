<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    private  $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->catgory = new Category();
    }


    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Category::class, 1)->create();
        $catgories = Category::all();
        $categoryKey = array_keys($catgories->first()->getAttributes());
        $this->assertCount(1, $catgories);
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $categoryKey
        );
    }

    public function testCreate()
    {
        $category = Category::create(['name' => 'Test']);
        $category->refresh(); //Para atualizar o Model com as novas informações

        $categoryUuid = strlen($category->id);
        $this->assertEquals(36, $categoryUuid);

        $this->assertEquals('Test', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create(['name' => 'Test', 'description' => null]);
        $this->assertNull($category->description);

        $category = Category::create(['name' => 'Test', 'description' => 'desc']);
        $this->assertEquals('desc', $category->description);

        $category = Category::create(['name' => 'Test', 'is_active' => false]);
        $this->assertFalse($category->is_active);

        $category = Category::create(['name' => 'Test', 'is_active' => true]);
        $this->assertTrue($category->is_active);
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description' => 'test_description'
        ])->first();

        $data = [
                    'name' => 'test_updated',
                    'description' => 'desc_updated',
                    'is_active' => false
                ];
        $category->update($data);

//        $this->assertEquals('test_updated', $category->name);
//        $this->assertEquals('desc_updated', $category->description);
//        $this->assertFalse($category->is_active);

        //O bloco comentado acima pode ser testado como o foreach
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDelete()
    {
        factory(Category::class, 1)->create();
        $category = Category::first();
        $item = $category->find($category->id);

        $result = $item->delete();

        $this->assertEquals('1', $result);
    }
}
