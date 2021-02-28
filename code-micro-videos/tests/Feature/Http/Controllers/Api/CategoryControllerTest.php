<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
                ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(
                                route(
                                    'categories.show',
                                    ['category' => $category->id]
                                ));
        $response->assertStatus(200)
                ->assertJson($category->toArray());
    }

    /**
     * Validate Create with fields required e msg error
     */
    public function testCreateWithValidationRequired()
    {
        $response = $this->json('POST', route('categories.store', []));
        //dd($response->content());

        $this->assertWithValidationRequired($response);
    }

    /**
     * Validate Create with Validation Max 255
     */
    public function testCreateWithValidationMax()
    {
        $response = $this->json('POST', route('categories.store',[
            'name' => str_repeat('a', 256)
        ]));

        $this->assertWithValidationMax($response);
    }

    /**
     * Validate Create with fields Nullable and Max rule
     */
    public function testCreateWithValidationNullableMaxFields()
    {
        $response = $this->json('POST', route('categories.store',[
            'name' => str_repeat('a', 256),
        ]));

        $this->assertWithValidationNullableMaxFields($response);
    }

    /**
     * Validate Create with fields Boolean and Max rule
     */
    public function testBooleanMaxFieldsValidation()
    {
        $response = $this->json('POST', route('categories.store',[
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]));

        $this->assertWithValidationBooleanMaxFields($response);
    }

    /**
     * Validate UPDATE with fields Required
     */
    public function testUpdateWithValidationRequired()
    {
        $category = factory(Category::class)->create();
        $response = $this->json('PUT', route('categories.update', ['category'=> $category->id], []));

        $this->assertWithValidationRequired($response);
    }

    /**
     * Validate Update with Validation Max 255
     */
    public function testUpdateWithValidationMax()
    {
        $category = factory(Category::class)->create();

        $response = $this->json('PUT',
            route('categories.update',
                ['category'=> $category->id]
            ),
            ['name' => str_repeat('a', 256)]
        );

        $this->assertWithValidationMax($response);
    }

    /**
     * Validate UPDATE with fields Nullable and Max rule
     */
    public function testUpdateWithValidationNullableMaxFields()
    {
        $category = factory(Category::class)->create();

        $response = $this->json('PUT',
            route('categories.update',
                ['category'=> $category->id]
            ),
            ['name' => str_repeat('a', 256)]
        );

        $this->assertWithValidationNullableMaxFields($response);
    }

    /**
     * Validate UPDATE with fields Boolean and Max rule
     */
    public function testUpdateWithValidationBooleanMaxFields()
    {
        $category = factory(Category::class)->create();

        $response = $this->json('PUT',
            route('categories.update',
                ['category'=> $category->id]
            ),
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        );

        $this->assertWithValidationBooleanMaxFields($response);
    }

    /**
     * @param TestResponse $response
     * Methods for Create and Update
     */
    public function assertWithValidationRequired(TestResponse $response)
    {
        $response->AssertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    public function assertWithValidationMax(TestResponse $response)
    {
        $response->AssertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    public function assertWithValidationNullableMaxFields(TestResponse $response)
    {
        $response->AssertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    public function assertWithValidationBooleanMaxFields(TestResponse $response)
    {
        $response->AssertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_active'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ])
            ->assertJsonFragment([
                Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    /**
     * Test CREATE POST status 201 with is_active Rtue/False
     * And description nullable/Not
     */
    public function testStore()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test'
        ]);

        $id = $response->json('id');
        $category = Category::find($id);

        $response->assertStatus(201)
                ->assertJson($category->toArray());

        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));


        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test',
            'description' => 'desc',
            'is_active' => false
        ]);

        $response->assertJsonFragment([
            'name' => 'test',
            'description' => 'desc',
            'is_active' => false
        ]);
                //OU
//        $this->assertFalse($response->json('is_active'));
//        $this->assertEquals('test', $response->json('name'));
//        $this->assertEquals('desc', $response->json('description'));


    }

    /**
     * Test UPDATE PUT status 200 with is_active True/False
     * And description nullable/Not
     */
    public function testUpdate()
    {
        $category = factory(Category::class)->create(['is_active' => true]);

        $response = $this->json('PUT', route('categories.update', ['category'=> $category->id]),
            [
            'name' => 'test2',
            'description' => 'desc2',
            'is_active' => false
            ]
        );

        $id = $response->json('id');
        $category = Category::find($id);

        $response->assertStatus(200)
                ->assertJson($category->toArray())
                ->assertJsonFragment([
            'name' => 'test2',
            'description' => 'desc2',
            'is_active' => false
        ]);

        $response = $this->json('PUT', route('categories.update', ['category'=> $category->id]),
            [
                'name' => 'test2',
                'description' => '',
            ]
        );

        $response->assertJsonFragment([
                'name' => 'test2',
                'description' => null,
                'is_active' => false
            ]);

        $category->description = 'foo';
        $category->save();

        $response->assertJsonFragment([
            'name' => 'test2',
            'description' => null,
            'is_active' => false
        ]);
    }

    public function testDelete()
    {
        $category = factory(Category::class)->create();

        $response = $this->json('DELETE', route('categories.destroy', ['category'=> $category->id]));

        $response->assertStatus(204);
        $this->assertNull(Category::find($category->id));

        $category->restore();
        $this->assertNotNull(Category::find($category->id));
    }
}
