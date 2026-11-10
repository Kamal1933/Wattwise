<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_profile_page_shows_initials_and_photo_form_when_no_photo(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);

        $this->actingAs($user)->get(route('profile.index'))
            ->assertOk()
            ->assertSee('Foto profil')
            ->assertSee(route('profile.photo.update'), false)
            ->assertSee('>BS</span>', false)
            ->assertDontSee('Hapus foto');
    }

    public function test_user_can_upload_and_replace_profile_photo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.photo.update'), ['photo' => UploadedFile::fake()->image('saya.jpg', 300, 300)])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('status');

        $first = $user->fresh()->photo_path;
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);

        $this->post(route('profile.photo.update'), ['photo' => UploadedFile::fake()->image('baru.png')]);
        $second = $user->fresh()->photo_path;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        // Photo appears in the header on every page.
        $this->get(route('dashboard'))->assertSee('storage/'.$second, false);
    }

    public function test_user_can_remove_profile_photo(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('profile.photo.update'), ['photo' => UploadedFile::fake()->image('saya.jpg')]);
        $path = $user->fresh()->photo_path;

        $this->get(route('profile.index'))->assertSee('Hapus foto');

        $this->delete(route('profile.photo.destroy'))->assertRedirect(route('profile.index'));

        $this->assertNull($user->fresh()->photo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_photo_must_be_a_small_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.photo.update'), ['photo' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrorsIn('photo', 'photo');

        $this->post(route('profile.photo.update'), ['photo' => UploadedFile::fake()->image('besar.jpg')->size(3000)])
            ->assertSessionHasErrorsIn('photo', 'photo');

        $this->assertNull($user->fresh()->photo_path);
    }

    public function test_guest_cannot_change_photo(): void
    {
        $this->post(route('profile.photo.update'))->assertRedirect(route('login'));
        $this->delete(route('profile.photo.destroy'))->assertRedirect(route('login'));
    }
}
