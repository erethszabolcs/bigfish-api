<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;

class UserTest extends TestCase
{
    private $headers = ['Accept' => 'application/json'];

    private $payload = [
        'name' => 'Test Name',
        'email' => 'test@email.hu',
        'phone_number' => '(835) 375-7734',
        'date_of_birth' => '1995-11-13',
        'is_active' => 1,
    ];

    public function testUsersAreListedCorrectly()
    {
        User::factory()->count(User::$per_page * 2)->create();

        $response = $this->json('GET', '/api/v1/users', [], $this->headers)
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'phone_number',
                            'date_of_birth',
                            'is_active',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'links' => [
                        'first',
                        'last',
                        'prev',
                        'next',
                    ],
                    'meta' => [
                        'current_page',
                        'from',
                        'last_page',
                        'links' => [
                            '*' => [
                                'url',
                                'label',
                                'active',
                            ]
                            ],
                        'path',
                        'per_page',
                        'to',
                        'total',
                    ]
                ]
            );
    }

    public function testPaginationWorks()
    {
        User::factory()->count(User::$per_page * 2)->create();

        $response = $this->json('GET', '/api/v1/users?page=2', [], $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(
            [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone_number',
                        'date_of_birth',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active',
                        ]
                        ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ]
            ]
        )
        ->assertJson(
            [
                'meta' => [
                    'current_page' => 2
                ],
            ]);
    }

    public function testOrderingWorks()
    {
        User::factory()->count(User::$per_page * 2)->create();

        foreach(User::$required as $orderby) {
            foreach(['asc', 'desc'] as $direction) {
                $order = User::orderBy($orderby, $direction)->paginate(User::$per_page)->pluck('id')->toArray();

                $response = $this->json('GET', '/api/v1/users?order_by=' . $orderby . '&direction=' . $direction, [], $this->headers);

                $resp_ids = array_column(json_decode($response->getContent())->data, 'id');

                $this->assertTrue($order == $resp_ids);
            }
        }
    }

    public function testUsersAreShownCorrectly()
    {
        $user = User::factory()->create();

        $response = $this->json('GET', '/api/v1/users/' . $user->id, [], $this->headers)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'date_of_birth' => $user->date_of_birth,
                    'is_active' =>  $user->is_active,
                ]
            ]);
    }

    public function testUsersAreCreatedCorrectly()
    {
        $response = $this->json('POST', '/api/v1/users', $this->payload, $this->headers)
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => $this->payload['name'],
                    'email' => $this->payload['email'],
                    'phone_number' => $this->payload['phone_number'],
                    'date_of_birth' => $this->payload['date_of_birth'],
                    'is_active' => $this->payload['is_active'],
                ],
                'message' => 'User created successfully',
            ]);
    }

    public function testUsersAreUpdatedCorrectly()
    {
        $user = User::factory()->create();

        $response = $this->json('PUT', '/api/v1/users/' . $user->id, $this->payload, $this->headers)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $this->payload['name'],
                    'email' => $this->payload['email'],
                    'phone_number' => $this->payload['phone_number'],
                    'date_of_birth' => $this->payload['date_of_birth'],
                    'is_active' => $this->payload['is_active'],
                ],
                'message' => 'User updated successfully',
            ]);
    }

    public function testUsersAreDeletedCorrectly()
    {
        $user = User::factory()->create();

        $response = $this->json('DELETE', '/api/v1/users/' . $user->id, [], $this->headers)
            ->assertStatus(204);
    }

    public function testUserNotFoundWorks()
    {
        foreach(['GET', 'PUT', 'DELETE'] as $method) {
            do{
                $id = rand();
            }
            while(in_array($id, User::all()->pluck('id')->toArray()));
    
            $response = $this->json($method, '/api/v1/users/' . $id, [], $this->headers)
                ->assertStatus(404)
                ->assertExactJson(
                    [
                        'error' => 'Entry for User not found',
                    ]);
        }
    }

    public function testRequiredValidationOnCreateWorks()
    {
        foreach(User::$required as $field) {
            
            $payload = $this->payload;

            unset($payload[$field]);
            
            $response = $this->json('POST', '/api/v1/users', $payload, $this->headers)
                ->assertStatus(422)
                ->assertExactJson(
                    [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            $field => [
                                'The ' . str_replace('_', ' ', $field) . ' field is required.'
                            ]
                        ]
                    ]);
        }
    }

    public function testFilledValidationOnUpdateWorks()
    {
        $user = User::factory()->create();

        foreach(User::$required as $field) {
            
            $payload = $this->payload;

            $payload[$field] = '';
            
            $response = $this->json('PUT', '/api/v1/users/' . $user->id, $payload, $this->headers)
                ->assertStatus(422)
                ->assertExactJson(
                    [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            $field => [
                                'The ' . str_replace('_', ' ', $field) . ' field must have a value.'
                            ]
                        ]
                    ]);
        }
    }

    public function testEmailValidationWorks()
    {
        $user = User::factory()->create();

        $payload = $this->payload;

        $payload['email'] = 'invalidemail';

        foreach(['PUT', 'POST'] as $method) {
            $response = $this->json($method, '/api/v1/users' . (($method == 'PUT') ? '/' . $user->id : ''), $payload, $this->headers)
            ->assertStatus(422)
            ->assertExactJson(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'email' => [
                            'The email must be a valid email address.'
                        ]
                    ]
                ]);
        }
    }

    public function testDateOfBirthValidationWorks()
    {
        $user = User::factory()->create();

        $payload = $this->payload;

        $payload['date_of_birth'] = 'invaliddateofbirth';

        foreach(['POST', 'PUT'] as $method) {
            $response = $this->json($method, '/api/v1/users' . (($method == 'PUT') ? '/' . $user->id : ''), $payload, $this->headers)
                ->assertStatus(422)
                ->assertExactJson(
                    [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'date_of_birth' => [
                                'The date of birth is not a valid date.'
                            ]
                        ]
                    ]);
        }
    }

    public function testIsActiveValidationWorks()
    {
        $user = User::factory()->create();

        $payload = $this->payload;

        $payload['is_active'] = 'invalidisactive';

        foreach(['POST', 'PUT'] as $method) {
            $response = $this->json($method, '/api/v1/users' . (($method == 'PUT') ? '/' . $user->id : ''), $payload, $this->headers)
                ->assertStatus(422)
                ->assertExactJson(
                    [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'is_active' => [
                                'The is active field must be true or false.'
                            ]
                        ]
                    ]);
        }
    }

    public function testFallbackRouteWorks()
    {
        do {
            $route = Str::random(10);
        }
        while(str_starts_with($route, 'users'));

        $response = $this->json('GET', '/api/v1/' . $route, [], $this->headers)
            ->assertStatus(404)
            ->assertExactJson(
                [
                    'message' => 'Page Not Found',
                ]);
    }
}
