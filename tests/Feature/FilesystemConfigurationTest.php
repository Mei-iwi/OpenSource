<?php

namespace Tests\Feature;

use Tests\TestCase;

class FilesystemConfigurationTest extends TestCase
{
    public function test_persistent_uploads_falls_back_when_upload_path_is_blank(): void
    {
        $this->assertSame(
            storage_path('app/private/uploads'),
            config('filesystems.disks.persistent_uploads.root'),
        );
    }

    public function test_private_media_disks_are_preserved(): void
    {
        $this->assertSame('persistent_uploads', config('filesystems.avatar_disk'));
        $this->assertSame('persistent_uploads', config('filesystems.attendance_proof_disk'));
        $this->assertArrayHasKey('persistent_uploads', config('filesystems.disks'));
    }
}
