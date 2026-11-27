<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class LaporanAccessTest extends TestCase
{
    public function test_guest_cannot_open_business_reports(): void
    {
        $this->get('/admin/laporan')->assertRedirect('/admin/login');
    }

    public function test_export_route_is_not_public(): void
    {
        $this->get('/admin/laporan/export/penjualan/csv')->assertRedirect('/admin/login');
    }
}
