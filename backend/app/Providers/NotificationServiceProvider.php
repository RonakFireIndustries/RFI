<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Wires the notification observers to their models.
 *
 * Observers are used rather than calls sprinkled through controllers so that
 * a status change is caught wherever it happens - a controller, a form
 * request, a service class, a seeder or an artisan command - not only on the
 * happy path through the HTTP layer.
 *
 * Registration is guarded: an install missing a model must not 500 on boot.
 */
class NotificationServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected array $observers = [
        // HR
        \App\Models\Leave::class => \App\Observers\LeaveObserver::class,
        \App\Models\DailyReport::class => \App\Observers\DailyReportObserver::class,
        \App\Models\PayrollPeriod::class => \App\Observers\PayrollPeriodObserver::class,
        \App\Models\Payslip::class => \App\Observers\PayslipObserver::class,

        // Finance
        \App\Models\CashVoucher::class => \App\Observers\CashVoucherObserver::class,
        \App\Models\Payment::class => \App\Observers\PaymentObserver::class,

        // Party masters — each customer/supplier gets a ledger to post against
        \App\Models\Customer::class => \App\Observers\PartyLedgerObserver::class,
        \App\Models\Supplier::class => \App\Observers\PartyLedgerObserver::class,

        // Sales & survey
        \App\Models\Opportunity::class => \App\Observers\OpportunityObserver::class,
        \App\Models\SiteVisit::class => \App\Observers\SiteVisitObserver::class,
        \App\Models\FollowUp::class => \App\Observers\FollowUpObserver::class,
        \App\Models\Quotation::class => \App\Observers\QuotationObserver::class,

        // Sales documents
        \App\Models\SalesOrder::class => \App\Observers\SalesOrderObserver::class,
        \App\Models\Invoice::class => \App\Observers\InvoiceObserver::class,

        // Purchase
        \App\Models\PurchaseOrder::class => \App\Observers\PurchaseOrderObserver::class,

        // Inventory
        \App\Models\StockRequest::class => \App\Observers\StockRequestObserver::class,
        \App\Models\InventoryTransfer::class => \App\Observers\InventoryTransferObserver::class,

        // Compliance
        \App\Models\FireSystem::class => \App\Observers\FireSystemObserver::class,

        // Support
        \App\Models\IssueReport::class => \App\Observers\IssueReportObserver::class,
    ];

    public function boot(): void
    {
        foreach ($this->observers as $model => $observer) {
            if (class_exists($model) && class_exists($observer)) {
                $model::observe($observer);
            }
        }
    }
}
