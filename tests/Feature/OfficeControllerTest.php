<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\Notification;
use Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;


class OfficeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */



    /** @test */
    public function itListsAllOfficesInPaginatedWay(){


        Office::factory(30)->create();

        $response = $this->get('/api/offices');

        // $response->dump();

        // $response->assertOk();
        // $response->assertJsonCount(20, 'data');


        $this->assertNotNull($response->json('data')[0]['id']) ;
        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonCount(20, 'data')
            ->assertJsonStructure(['data' => ['*' => ['id', 'title']]])
            ;

    }




     /** @test */
    public function itOnlyListsOfficesThatAreNotHiddenAndApproved(){

        Office::factory(3)->create();

        // Office::factory()->hidden()->create();
        // Office::factory()->pending()->create();


        $response = $this->get('/api/offices');

        $response->assertOk()
            ->assertJsonCount(3, 'data');

        $this->assertNotNull($response->json('data')[0]['id']);
        $this->assertNotNull($response->json('meta'));
        $this->assertNotNull($response->json('links'));

    }

    /**
     * @test
     */
    public function itListsOfficesIncludingHiddenAndUnApprovedIfFilteringForTheCurrentLoggedInUser()
    {
        $user = User::factory()->create();

        Office::factory(3)->for($user)->create();

        Office::factory()->hidden()->for($user)->create();
        Office::factory()->pending()->for($user)->create();

        $this->actingAs($user);

        $response = $this->get('/api/offices?user_id='.$user->id);

        $response->assertOk()
            ->assertJsonCount(5, 'data');

    }



    /** @test */
    public function itFiltersByUserId()
    {
        Office::factory(3)->create();

        $host = User::factory()->create();
        $office = Office::factory()->for($host)->create();

        $response = $this->get(
            '/api/offices?user_id='.$host->id
        );

        $response->assertOk() ;
        $response->assertJsonCount(1, 'data');

        $this->assertEquals($office->id , $response->json('data')[0]['id']);


    }




    /** @test */
    public function itFiltersByVisitorId()
    {
        Office::factory(3)->create();

        $user = User::factory()->create();
        $office = Office::factory()->create();


        Reservation::factory()->for(Office::factory())->create() ;
        Reservation::factory()->for($office)->for($user)->create() ;

        $response = $this->get(
            '/api/offices?visitor_id='.$user->id
        );

        $response->assertOk() ;
        $response->assertJsonCount(1, 'data');

        $this->assertEquals($office->id , $response->json('data')[0]['id']);
    }





    /**
     * @test
     */
    public function itFiltersByTags()
    {
        $tags = Tag::factory(2)->create();

        $office = Office::factory()->hasAttached($tags)->create();
        Office::factory()->hasAttached($tags->first())->create();
        Office::factory()->create();

        $response = $this->get(
            'api/offices?'.http_build_query([
                'tags' => $tags->pluck('id')->toArray()
            ])
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $office->id);
    }






    /** @test */
    public function itIncludesImagesTagsAndUser()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create() ;
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag) ;
        $office->images()->create(['path' => 'image.jpg']) ;




        $response = $this->get('/api/offices');

        $response->assertOk() ;

        $this->assertIsArray($response->json('data')[0]['tags']) ;
        $this->assertCount(1 , $response->json('data')[0]['tags']) ;

        $this->assertIsArray($response->json('data')[0]['images']) ;
        $this->assertCount(1 , $response->json('data')[0]['images']) ;
        $this->assertEquals($user->id , $response->json('data')[0]['user']['id']);


    }



    /** @test */
    public function itReturnsTheNumberOfActiveReservations(){

        $office = Office::factory()->create();
        Reservation::factory()->for($office)->create(['status'=>Reservation::STATUS_ACTIVE]) ;
        Reservation::factory()->for($office)->create(['status'=>Reservation::STATUS_CANCELLED]) ;

        $response = $this->get('/api/offices');
        $response->assertOk();
        $this->assertEquals(1, $response->json('data')[0]['reservations_count']);


    }

    /** @test */
    public function itOrdersByDistanceWhenCoordinatesAreProvided()
    {

        $office1 = Office::factory()->create([
            'lat' => '39.751812666457816' ,
            'lng' => '-8.808362496919695' ,
            'title' => 'leiria'
        ]);

        $office2 = Office::factory()->create([
            'lat' => '39.09342550230428' ,
            'lng' => '-9.260238110562733' ,
            'title' => 'Torres Vedres'
        ]);

        // $this->withoutExceptionHandling();

        $response = $this->get('/api/offices?lat=38.71869818739813&lng=-9.14235264990831');

        $response->assertOk();
        $this->assertEquals( 'Torres Vedres' , $response->json('data')[0]['title']);
        $this->assertEquals( 'leiria' , $response->json('data')[1]['title']);


    }


    /** @test */
    public function itShowsTheOffice()
    {

        $user = User::factory()->create();
        $tag = Tag::factory()->create() ;
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag) ;
        $office->images()->create(['path' => 'image.jpg']) ;


        Reservation::factory()->for($office)->create(['status'=>Reservation::STATUS_ACTIVE]) ;
        Reservation::factory()->for($office)->create(['status'=>Reservation::STATUS_CANCELLED]) ;

        $response = $this->get('api/offices/'.$office->id);

        $response->assertOk()
            ->assertJsonPath('data.reservations_count', 1)
            ->assertJsonCount(1, 'data.tags')
            ->assertJsonCount(1, 'data.images')
            ->assertJsonPath('data.user.id', $user->id);





    }


    /** @test */
    public function itCreatesAnOffice()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $user = User::factory()->createQuietly() ;
        $tag = Tag::factory() -> create() ;
        $tag2 = Tag::factory() -> create() ;

        $this->actingAs($user);

        $response = $this->postJson('/api/offices' , [
            'title' => 'Office in arksane' ,
            'description' => 'Description' ,
            'lat' => '39.751812666457816' ,
            'lng' => '-8.808362496919695' ,
            'address_line1' => 'address' ,
            'price_per_day' => 10_000 ,
            'monthly_discount' => 5 ,
            'tags' => [
                $tag->id , $tag2->id
            ]

        ]);

        $response->assertCreated()
        ->assertJsonPath('data.title' , 'Office in arksane')
        ->assertJsonPath('data.approval_status' , Office::APPROVAL_PENDING)
        ->assertJsonPath('data.user.id' , $user->id)
        ->assertJsonCount(2, 'data.tags');

        $this->assertDatabaseHas('offices' , [
            'title' => 'office in arksane'
        ]);
        // FacadesNotification::assertSentTo($admin, OfficePendingApproval::class);

    }



    /** @test */
    public function itDosntAllowCreatingIfScopeIsNotProvided()
    {
        // $user = User::factory()->createQuietly() ;

        // $token = $this->createToken('test' , ['office.create']) ;


        // $response = $this->postJson('/api/offices' , [] , [
        //     'Authorization' => 'Bearer ' . $token->plainTextToken
        // ]);

        // // dd( $response->json() );




        // $response->assertStatus(403) ;
        $user = User::factory()->create();

        Sanctum::actingAs($user, []);

        $response = $this->postJson('/api/offices');

        $response->assertForbidden();

    }

    /** @test */
    public function itUpdatesAnOffice()
    {

        $user = User::factory()->createQuietly() ;
        $tags = Tag::factory(2) -> create() ;
        $anotherTag = Tag::factory() -> create() ;

        $office = Office::factory() -> for($user)->create() ;

        $office->tags()->attach($tags) ;

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id , [
            'title' => 'Office updates' ,
            'tags' => [$tags[0]->id , $anotherTag->id]

        ]);

        $response->assertOk()
        ->assertJsonCount(2 , 'data.tags')
        ->assertJsonPath('data.tags.0.id' , $tags[0]->id)
        ->assertJsonPath('data.tags.1.id' , $anotherTag->id)
        ->assertJsonPath('data.title' , 'Office updates')


        ->assertJsonPath('data.user.id' , $user->id);

           $this->assertDatabaseHas('offices' , [
               'id' => $response->json('data.id')
           ]);

    }


    /** @test */
    public function itDosntUpdateOfficeThatDoesntBelongToUser(){
        $user = User::factory()->createQuietly() ;
        $anotherUser = User::factory()->createQuietly() ;
        $office = Office::factory() -> for($anotherUser)->create() ;


        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id , [
            'title' => 'Office updates'

        ]);


        $response->assertStatus(403) ;


    }



    /**
     * @test
    */
    // public function itMarksTheOfficeAsPendingIfDirty()
    // {
    //     $admin = User::factory()->create(['is_admin' => true]);

    //     FacadesNotification::fake();

    //     $user = User::factory()->create();
    //     $office = Office::factory()->for($user)->create();

    //     $this->actingAs($user);

    //     $response = $this->putJson('/api/offices/'.$office->id, [
    //         'lat' => 40.74051727562952
    //     ]);

    //     $response->assertOk();

    //     $this->assertDatabaseHas('offices', [
    //         'id' => $office->id,
    //         'approval_status' => Office::APPROVAL_PENDING,
    //     ]);

    //     FacadesNotification::assertSentTo([$admin], OfficePendingApproval::class);
    // }

    /**
     * @test
    */
    public function itCanDeleteOffices()
    {

        Storage::put('/office_image.jpg', 'empty');

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path' => 'office_image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson('/api/offices/'.$office->id);

        $response->assertOk();

        $this->assertSoftDeleted($office);

        $this->assertModelMissing($image);

        Storage::assertMissing('office_image.jpg');
    }



    /**
     * @test
      */
    public function itCannotDeleteAnOfficeThatHasReservations()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        Reservation::factory(3)->for($office)->create();

        $this->actingAs($user);

        $response = $this->deleteJson("/api/offices/{$office->id}");


        // dd($response->json());
        $response->assertStatus(422);
        $response->assertUnprocessable();

        $this->assertNotSoftDeleted($office);
    }





    /**
     * @test
     */
    public function itUpdatedTheFeaturedImageOfAnOffice()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path' => 'image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id, [
            'featured_image_id' => $image->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.featured_image_id', $image->id);
    }

    /**
     * @test
     */
    public function itDoesntUpdateFeaturedImageThatBelongsToAnotherOffice()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        $office2 = Office::factory()->for($user)->create();

        $image = $office2->images()->create([
            'path' => 'image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id, [
            'featured_image_id' => $image->id,
        ]);

        $response->assertUnprocessable()->assertInvalid('featured_image_id');
    }



}
