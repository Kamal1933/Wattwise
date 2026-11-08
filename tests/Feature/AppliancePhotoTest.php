<?php

namespace Tests\Feature;

use App\Models\Household;
use App\View\Components\ApplianceArt;
use Database\Seeders\ApplianceCategorySeeder;
use Database\Seeders\ExpertRuleSeeder;
use Database\Seeders\HouseholdSeeder;
use Database\Seeders\TariffRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AppliancePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected Household $household;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        ApplianceArt::flushDefaultPhotoCache();

        $this->seed(TariffRateSeeder::class);
        $this->seed(ApplianceCategorySeeder::class);
        $this->seed(ExpertRuleSeeder::class);
        $this->seed(HouseholdSeeder::class);

        $this->household = Household::first();
        $this->actingAs($this->household->user);
    }

    protected function tearDown(): void
    {
        ApplianceArt::flushDefaultPhotoCache();

        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    protected function applianceFields(array $overrides = []): array
    {
        return array_merge([
            'category_id' => $this->household->appliances->first()->category_id,
            'name' => 'Kulkas Dapur',
            'power_watt' => 120,
            'quantity' => 1,
            'daily_hours' => 24,
            'days_per_month' => 30,
            'usage_factor' => 0.5,
        ], $overrides);
    }

    public function test_uploaded_photo_is_stored_and_shown_on_the_dashboard(): void
    {
        $this->post(route('households.appliances.store', $this->household), $this->applianceFields([
            'photo' => UploadedFile::fake()->image('kulkas.jpg', 600, 600),
        ]))->assertRedirect();

        $appliance = $this->household->appliances()->where('name', 'Kulkas Dapur')->sole();
        $this->assertNotNull($appliance->photo_path);
        Storage::disk('public')->assertExists($appliance->photo_path);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee($appliance->photoUrl(), false);
    }

    public function test_replacing_and_removing_a_photo_cleans_up_old_files(): void
    {
        $appliance = $this->household->appliances->first();

        $this->put(route('appliances.update', $appliance), $this->applianceFields([
            'name' => $appliance->name,
            'photo' => UploadedFile::fake()->image('first.png'),
        ]));
        $firstPath = $appliance->fresh()->photo_path;
        Storage::disk('public')->assertExists($firstPath);

        $this->put(route('appliances.update', $appliance), $this->applianceFields([
            'name' => $appliance->name,
            'photo' => UploadedFile::fake()->image('second.webp'),
        ]));
        $secondPath = $appliance->fresh()->photo_path;
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        $this->put(route('appliances.update', $appliance), $this->applianceFields([
            'name' => $appliance->name,
            'remove_photo' => '1',
        ]));
        $this->assertNull($appliance->fresh()->photo_path);
        Storage::disk('public')->assertMissing($secondPath);
    }

    public function test_deleting_an_appliance_deletes_its_photo(): void
    {
        $appliance = $this->household->appliances->first();
        $path = UploadedFile::fake()->image('ac.jpg')->store('appliances', 'public');
        $appliance->update(['photo_path' => $path]);

        $this->delete(route('appliances.destroy', $appliance))->assertRedirect();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_non_image_files_are_rejected(): void
    {
        $this->post(route('households.appliances.store', $this->household), $this->applianceFields([
            'photo' => UploadedFile::fake()->create('manual.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasErrors('photo');

        $this->assertDatabaseMissing('appliances', ['name' => 'Kulkas Dapur']);
    }

    public function test_default_photo_in_public_folder_replaces_the_illustration(): void
    {
        $path = public_path('images/appliances/kulkas.jpg');
        $photo = UploadedFile::fake()->image('kulkas.jpg');
        copy($photo->getPathname(), $path);

        try {
            $this->get(route('dashboard'))
                ->assertOk()
                ->assertSee(asset('images/appliances/kulkas.jpg'), false);
        } finally {
            unlink($path);
        }
    }
}
