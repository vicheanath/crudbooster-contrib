<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\helpers\CBImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UploadTest extends FeatureTestCase
{
    public static function diskRoots()
    {
        return [
            'existing installs (storage/app)' => ['app'],
            'Laravel 11+ default (storage/app/private)' => ['app/private'],
        ];
    }

    /**
     * @dataProvider diskRoots
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('diskRoots')]
    public function test_user_photo_upload_is_resized_and_served($root)
    {
        config(['filesystems.disks.local.root' => storage_path($root)]);
        Storage::forgetDisk('local');

        $this->loginAsAdmin();

        $photo = UploadedFile::fake()->image('avatar.png', 400, 300);
        $this->post($this->admin('users/add-save'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'photo' => $photo,
            'id_cms_privileges' => DB::table('cms_privileges')->value('id'),
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect();

        $user = DB::table('cms_users')->where('email', 'jane@example.com')->first();
        $this->assertNotNull($user, 'User was not created');
        $this->assertTrue(Storage::exists($user->photo), "Photo not stored on the local disk ($root)");

        $stored = CBImage::read(Storage::path($user->photo));
        $this->assertSame([90, 90], [$stored->width(), $stored->height()]);

        $this->get($user->photo.'?w=30')->assertOk();
    }
}
