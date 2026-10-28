<?php

namespace Tests\Feature;

use App\Admin\Pages\HrDashboard;
use App\Admin\Pages\SalesPipeline;
use App\Admin\Pages\StoreOverview;
use App\Models\CrmDeal;
use App\Models\HrLeaveRequest;
use App\Models\ShopOrder;
use App\Models\ShopProduct;
use crocodicstudio\crudbooster\helpers\CBRouter;
use CrudBooster\Panel\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Opens every page of the example resources (e-commerce, HR, CRM) on the seeded data, and runs
 * a few of their actions and hooks.
 */
class AdminExamplesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * CRUDBooster registers the module routes from cms_moduls at boot, before the test database
     * exists, so register them again once it is migrated and seeded.
     */
    protected function afterRefreshingDatabase(): void
    {
        CBRouter::route();
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->app['router']->getRoutes()->refreshActionLookups();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->post('/admin/login', ['email' => 'admin@crudbooster.com', 'password' => '123456'])->assertRedirect();
        $this->assertNotEmpty(session('admin_id'));
    }

    public function test_every_resource_page_opens(): void
    {
        foreach (app(Panel::class)->getResources() as $resource) {
            $record = $resource::newModel()->newQuery()->first();
            $this->assertNotNull($record, "$resource has no seeded record");

            foreach ($resource::getPages() as $page) {
                $url = in_array($page, ['view', 'edit'], true) ? $resource::url($page, $record) : $resource::url($page);
                $this->get($url)->assertOk();
            }
        }
    }

    public function test_the_dashboard_pages_open(): void
    {
        foreach ([StoreOverview::class, HrDashboard::class, SalesPipeline::class] as $page) {
            $this->get($page::url())->assertOk();
        }

        $this->get(HrDashboard::url())->assertSee('Away today')->assertSee('Engineering');
        $this->get(SalesPipeline::url())->assertSee('Pipeline by stage')->assertSee('Negotiation');
    }

    public function test_the_filtered_child_lists_open(): void
    {
        $this->get('/admin/hr-employees?filters[department]=1')->assertOk();
        $this->get('/admin/crm-contacts?filters[company]=1')->assertOk();
        $this->get('/admin/shop-order-items?filters[order]='.ShopOrder::query()->value('id'))->assertOk();
    }

    public function test_leave_requests_are_approved_with_an_action(): void
    {
        $leave = HrLeaveRequest::query()->where('status', 'pending')->firstOrFail();

        $this->post("/admin/hr-leave-requests/{$leave->id}/actions/approve")->assertRedirect();

        $this->assertSame('approved', $leave->fresh()->status);
        $this->assertNotNull($leave->fresh()->decided_at);
    }

    public function test_new_leave_requests_start_as_pending_and_check_their_dates(): void
    {
        $this->post('/admin/hr-leave-requests', [
            'hr_employee_id' => 1, 'type' => 'annual', 'starts_on' => '2030-01-10', 'ends_on' => '2030-01-05',
        ])->assertSessionHasErrors('ends_on');

        $this->post('/admin/hr-leave-requests', [
            'hr_employee_id' => 1, 'type' => 'annual', 'starts_on' => '2030-01-10', 'ends_on' => '2030-01-12',
        ])->assertRedirect();

        $this->assertSame('pending', HrLeaveRequest::query()->latest('id')->first()->status);
    }

    public function test_winning_a_deal_closes_it(): void
    {
        $deal = CrmDeal::query()->where('stage', 'negotiation')->firstOrFail();

        $this->post("/admin/crm-deals/{$deal->id}/actions/won")->assertRedirect();

        $deal->refresh();
        $this->assertSame('won', $deal->stage);
        $this->assertSame(100, $deal->probability);
        $this->assertNotNull($deal->closed_at);
    }

    public function test_order_items_keep_the_order_total_in_sync(): void
    {
        $order = (new ShopOrder)->forceFill(['shop_customer_id' => 1, 'order_number' => 'TEST-1', 'status' => 'pending', 'total' => 0]);
        $order->save();
        $product = ShopProduct::query()->firstOrFail();

        $this->post('/admin/shop-order-items', [
            'shop_order_id' => $order->id, 'shop_product_id' => $product->id, 'quantity' => 2, 'unit_price' => '',
        ])->assertRedirect();

        $this->assertEquals($product->price * 2, $order->fresh()->total);
    }

    public function test_an_order_moves_through_its_statuses(): void
    {
        $order = ShopOrder::query()->where('status', 'paid')->firstOrFail();

        $this->post("/admin/shop-orders/{$order->id}/actions/paid")->assertForbidden();   // only for pending orders
        $this->post("/admin/shop-orders/{$order->id}/actions/shipped")->assertRedirect();

        $this->assertSame('shipped', $order->fresh()->status);
    }

    public function test_the_employee_export_downloads_a_csv(): void
    {
        $response = $this->post('/admin/hr-employees/bulk/export', ['records' => [1, 2]]);

        $response->assertOk();
        $this->assertStringContainsString('Amelia Brooks', $response->streamedContent());
    }
}
