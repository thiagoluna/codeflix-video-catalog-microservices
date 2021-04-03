<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(
            route(
                'genres.show',
                ['genre' => $genre->id]
            ));
        $response->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    /**
     * Validate Create with fields required e msg error
     */
    public function testCreateWithValidationRequired()
    {
        $response = $this->json('POST', route('genres.store', []));

        $this->assertWithValidationRequired($response);
    }

    /**
     * Validate Create with Validation Max 255
     */
    public function testCreateWithValidationMax()
    {
        $response = $this->json('POST', route('genres.store',[
            'name' => str_repeat('a', 256)
        ]));

        $this->assertWithValidationMax($response);
    }

    /**
     * Validate Create with fields Nullable and Max rule
     */
    public function testCreateWithValidationNullableMaxFields()
    {
        $response = $this->json('POST', route('genres.store',[
            'name' => str_repeat('a', 256),
        ]));

        $this->assertWithValidationNullableMaxFields($response);
    }

    /**
     * Validate Create with fields Boolean and Max rule
     */
    public function testBooleanMaxFieldsValidation()
    {
        $response = $this->json('POST', route('genres.store',[
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]));

        $this->assertWithValidationNullableMaxFields($response);
    }

    /**
     * Validate UPDATE with fields Required
     */
    public function testUpdateWithValidationRequired()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json('PUT', route('genres.update', ['genre'=> $genre->id], []));

        $this->assertWithValidationRequired($response);
    }

    /**
     * Validate Update with Validation Max 255
     */
    public function testUpdateWithValidationMax()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->json('PUT',
            route('genres.update',
                ['genre'=> $genre->id]
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
        $genre = factory(Genre::class)->create();

        $response = $this->json('PUT',
            route('genres.update',
                ['genre'=> $genre->id]
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
        $genre = factory(Genre::class)->create();

        $response = $this->json('PUT',
            route('genres.update',
                ['genre'=> $genre->id]
            ),
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        );

        $this->assertWithValidationNullableMaxFields($response);
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
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test'
        ]);

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response->assertStatus(201)
            ->assertJson($genre->toArray());

        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'is_active' => false
        ]);

        $response->assertJsonFragment([
            'name' => 'test',
            'is_active' => false
        ]);
    }

    /**
     * Test UPDATE PUT status 200 with is_active True/False
     * And description nullable/Not
     */
    public function testUpdate()
    {
        $genre = factory(Genre::class)->create(['is_active' => true]);
        $response = $this->json('PUT', route('genres.update', ['genre'=> $genre->id]),
            [
                'name' => 'test2',
                'is_active' => false
            ]
        );
        $id = $response->json('id');
        $genre = Genre::find($id);
        $response->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'test2',
                'is_active' => false
            ]);

        $response = $this->json('PUT', route('genres.update', ['genre'=> $genre->id]),
            [
                'name' => 'test2',
            ]
        );

        $response->assertJsonFragment([
            'name' => 'test2',
            'is_active' => false
        ]);

        $genre->save();

        $response->assertJsonFragment([
            'name' => 'test2',
            'is_active' => false
        ]);
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->json('DELETE', route('genres.destroy', ['genre'=> $genre->id]));

        $response->assertStatus(204);
        $this->assertNull(Genre::find($genre->id));

        $genre->restore();
        $this->assertNotNull(Genre::find($genre->id));
    }
}
