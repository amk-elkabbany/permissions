<?php

use App\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelEnso\PermissionManager\app\Models\Permission;
use LaravelEnso\PermissionManager\app\Models\PermissionGroup;
use Tests\TestCase;

class PermissionGroupTest extends TestCase
{
    use DatabaseMigrations;

    private $faker;

    protected function setUp()
    {
        parent::setUp();

        // $this->disableExceptionHandling();
        $this->faker = Factory::create();
        $this->actingAs(User::first());
    }

    /** @test */
    public function index()
    {
        $response = $this->get('/system/permissionGroups');

        $response->assertStatus(200);
    }

    /** @test */
    public function create()
    {
        $response = $this->get('/system/permissionGroups/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function store()
    {
        $postParams = $this->postParams();
        $response = $this->post('/system/permissionGroups', $postParams);

        $permissionGroup = PermissionGroup::whereName($postParams['name'])->first();

        $response->assertStatus(200)
            ->assertJsonFragment([
            'message' => 'The permission group was created!',
            'redirect'=> '/system/permissionGroups/'.$permissionGroup->id.'/edit',
            ]);
    }

    /** @test */
    public function edit()
    {
        $permissionGroup = PermissionGroup::create($this->postParams());
        $permissionGroup = $permissionGroup->fresh();

        $response = $this->get('/system/permissionGroups/'.$permissionGroup->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewHas('form');
    }

    /** @test */
    public function update()
    {
        $permissionGroup = PermissionGroup::create($this->postParams());
        $permissionGroup->description = 'edited';
        $permissionGroup->_method = 'PATCH';

        $this->patch('/system/permissionGroups/'.$permissionGroup->id, $permissionGroup->toArray())
            ->assertStatus(200)
            ->assertJson(['message' => __(config('labels.savedChanges'))]);

        $this->assertTrue($permissionGroup->fresh()->description === 'edited');
    }

    /** @test */
    public function destroy()
    {
        $permissionGroup = PermissionGroup::create($this->postParams());

        $response = $this->delete('/system/permissionGroups/'.$permissionGroup->id);

        $response->assertJsonFragment(['message']);
        $this->assertNull($permissionGroup->fresh());
        $response->assertStatus(200);
    }

    /** @test */
    public function cant_destroy_if_has_permission()
    {
        $permissionGroup = PermissionGroup::create($this->postParams());
        $this->addPermission($permissionGroup);

        $response = $this->delete('/system/permissionGroups/'.$permissionGroup->id);

        $response->assertStatus(302);
        $this->assertTrue(session('flash_notification')[0]->level === 'warning');
        $this->assertNotNull($permissionGroup->fresh());
    }

    private function addPermission($permissionGroup)
    {
        $permission = new Permission([
            'permission_group_id'   => $permissionGroup->id,
            'name'                  => $this->faker->word,
            'description'           => $this->faker->sentence,
            'type'                  => 0,
            'default'               => 0,
        ]);
        $permission->save();
    }

    private function postParams()
    {
        return [
            'name'                  => $this->faker->word,
            'description'           => $this->faker->sentence,
            '_method'               => 'POST',
        ];
    }
}
