<?php

namespace Tests\Unit\Models;

use App\Models\Organization1;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    public function test_organization1からDS一覧を取得できるか()
    {
        $org1_id = 1;
        $org1 = Organization1::find($org1_id);
        $org2 = $org1->organization2;
        
        $ast = [
            'ジョリーパスタ',
            'ジョリーオックス'
        ];
        $this->assertSame('ジョリーパスタ', $org2->name);
    }
}
