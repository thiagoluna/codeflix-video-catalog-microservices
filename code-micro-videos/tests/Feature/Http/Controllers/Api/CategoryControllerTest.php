<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
                ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(
                        route(
                            'categories.show',
                            ['category' => $this->category->id]
                        ));
        $response->assertStatus(200)
                ->assertJson($this->category->toArray());
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
        $response = $this->json('PUT', route('categories.update', ['category'=> $this->category->id], []));

        $this->assertWithValidationRequired($response);
    }

    /**
     * Validate Update with Validation Max 255
     */
    public function testUpdateWithValidationMax()
    {

        $response = $this->json('PUT',
            route('categories.update',
                ['category'=> $this->category->id]
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

        $response = $this->json('PUT',
            route('categories.update',
                ['category'=> $this->category->id]
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
        $response = $this->json('PUT',
            route('categories.update',
                ['category'=> $this->category->id]
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
    protected function assertWithValidationRequired(TestResponse $response)
    {
        $response->AssertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    protected function assertWithValidationMax(TestResponse $response)
    {
        $response->AssertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    protected function assertWithValidationNullableMaxFields(TestResponse $response)
    {
        $response->AssertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    protected function assertWithValidationBooleanMaxFields(TestResponse $response)
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

    public function testInvalidationData()
    {
        $data = ['name' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['name'], 'required', []);
        $response->assertJsonMissingValidationErrors(['is_active']);
    }

    public function assertInvalidationMax(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['name'], 'max.string', ['max' => 255]);
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['is_active'], 'boolean');
    }

    /**
     * Test CREATE POST status 201 with is_active Rtue/False
     * And description nullable/Not
     */
    public function testStore()
    {
        $data = ['name' => 'test'];
        $response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name' => 'test',
            'description' => 'desc',
            'is_active' => false,
            'deleted_at' => null
        ];
        $this->assertStore($data, $data);

//        $response = $this->json('POST', route('categories.store'), [
//            'name' => 'test'
//        ]);
//
//        $id = $response->json('id');
//        $category = Category::find($id);
//
//        $response->assertStatus(201)
//                ->assertJson($category->toArray());
//
//        $this->assertTrue($response->json('is_active'));
//        $this->assertNull($response->json('description'));
//
//
//        $response = $this->json('POST', route('categories.store'), [
//            'name' => 'test',
//            'description' => 'desc',
//            'is_active' => false
//        ]);
//
//        $response->assertJsonFragment([
//            'name' => 'test',
//            'description' => 'desc',
//            'is_active' => false
//        ]);
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
        $this->category = factory(Category::class)->create(['description' => 'desc', 'is_active' => false]);
        $data = ['name' => 'test', 'description' => 'desc', 'is_active' => false];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = ['name' => 'test', 'description' => ''];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, array_merge($data, ['description' => 'test']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

//        $category = factory(Category::class)->create(['is_active' => true]);
//        $response = $this->json('PUT', route('categories.update', ['category'=> $category->id]),
//            [
//            'name' => 'test2',
//            'description' => 'desc2',
//            'is_active' => false
//            ]
//        );
//
//        $id = $response->json('id');
//        $category = Category::find($id);
//
//        $response->assertStatus(200)
//                ->assertJson($category->toArray())
//                ->assertJsonFragment([
//            'name' => 'test2',
//            'description' => 'desc2',
//            'is_active' => false
//        ]);
//
//        $response = $this->json('PUT', route('categories.update', ['category'=> $category->id]),
//            [
//                'name' => 'test2',
//                'description' => '',
//            ]
//        );
//
//        $response->assertJsonFragment([
//                'name' => 'test2',
//                'description' => null,
//                'is_active' => false
//            ]);
//
//        $category->description = 'foo';
//        $category->save();
//
//        $response->assertJsonFragment([
//            'name' => 'test2',
//            'description' => null,
//            'is_active' => false
//        ]);
    }

    public function testDelete()
    {

        $response = $this->json('DELETE', route('categories.destroy', ['category'=> $this->category->id]));

        $response->assertStatus(204);
        $this->assertNull(Category::find($this->category->id));

        $this->category->restore();
        $this->assertNotNull(Category::find($this->category->id));
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}
