<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


class GenrerTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();
        $genreKeys = array_keys($genres->first()->getAttributes());

        $this->assertCount(1, $genres);
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $genreKeys
        );
    }

    public function testCreate()
    {
        $genre = Genre::create(['name' => 'Test']);
        $genre->refresh(); //Para atualizar o Model com as novas informações

        $genreUuid = strlen($genre->id);
        $this->assertEquals(36, $genreUuid);

        $this->assertEquals('Test', $genre->name);
        $this->assertTrue($genre->is_active);

        $genre = Genre::create(['name' => 'Test', 'is_active' => false]);
        $this->assertFalse($genre->is_active);

        $genre = Genre::create(['name' => 'Test', 'is_active' => true]);
        $this->assertTrue($genre->is_active);
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create()->first();

        $data = [
            'name' => 'test_updated',
            'is_active' => false
        ];
        $genre->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete()
    {
        $genre = factory(Genre::class, 1)->create()->first();

        $result = $genre->delete();

        $this->assertEquals('1', $result);
    }
}
