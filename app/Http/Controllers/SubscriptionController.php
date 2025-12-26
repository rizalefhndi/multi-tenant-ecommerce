<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\TenantInvoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    /**
     * Display subscription status and usage
     */
    public function index(): Response
    {
        $tenant = tenant();
        $plan = $tenant->getCurrentPlan();

        return Inertia::render('Subscription/Index', [
            'subscription' => [
                'status' => $tenant->subscription_status,
                'status_label' => $tenant->subscription_status_label,
                'status_color' => $tenant->subscription_status_color,
                'billing_cycle' => $tenant->billing_cycle,
                'billing_cycle_label' => $tenant->billing_cycle_label,
                'is_on_trial' => $tenant->is_on_trial,
                'trial_days_remaining' => $tenant->trial_days_remaining,
                'subscription_days_remaining' => $tenant->subscription_days_remaining,
                'subscription_ends_at' => $tenant->subscription_ends_at?->format('d M Y'),
                'trial_ends_at' => $tenant->trial_ends_at?->format('d M Y'),
            ],
            'plan' => $plan ? [
                'id' => $plan->id,
                'slug' => $plan->slug,
                'name' => $plan->name,
                'description' => $plan->description,
                'formatted_price_monthly' => $plan->formatted_price_monthly,
                'formatted_price_yearly' => $plan->formatted_price_yearly,
                'features' => $plan->features,
                'is_free' => $plan->isFree(),
            ] : null,
            'usage' => $tenant->getUsageSummary(),
        ]);
    }

    /**
     * Display available plans for upgrade
     */
    public function plans(): Response
    {
        $tenant = tenant();
        $currentPlan = $tenant->getCurrentPlan();

        $plans = Plan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) use ($currentPlan) {
                return [
                    'id' => $plan->id,
                    'slug' => $plan->slug,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'price_monthly' => $plan->price_monthly,
                    'price_yearly' => $plan->price_yearly,
                    'formatted_price_monthly' => $plan->formatted_price_monthly,
                    'formatted_price_yearly' => $plan->formatted_price_yearly,
                    'yearly_savings_percent' => $plan->yearly_savings_percent,
                    'max_products_display' => $plan->max_products_display,
                    'max_orders_display' => $plan->max_orders_display,
                    'max_storage_display' => $plan->max_storage_display,
                    'features' => $plan->features,
                    'is_featured' => $plan->is_featured,
                    'is_custom' => $plan->is_custom,
                    'is_free' => $plan->isFree(),
                    'is_current' => $currentPlan && $plan->id === $currentPlan->id,
                    'is_upgrade' => $currentPlan && $plan->isHigherThan($currentPlan),
                ];
            });

        return Inertia::render('Subscription/Plans', [
            'plans' => $plans,
            'currentPlan' => $currentPlan ? [
                'id' => $currentPlan->id,
                'name' => $currentPlan->name,
            ] : null,
        ]);
    }

    /**
     * Process plan change (upgrade/downgrade)
     */
    public function changePlan(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $tenant = tenant();
        $plan = Plan::findOrFail($request->plan_id);

        // Create invoice for new plan
        $invoice = TenantInvoice::createForSubscription(
            $tenant, 
            $plan, 
            $request->billing_cycle
        );

        // If free plan, activate immediately
        if ($plan->isFree()) {
            $tenant->subscribeToPlan($plan, $request->billing_cycle);
            return redirect()->route('subscription.index')
                ->with('success', 'Paket berhasil diubah ke ' . $plan->name);
        }

        // Redirect to payment for paid plans
        return redirect()->route('subscription.invoice', $invoice->id);
    }

    /**
     * Display billing history
     */
    public function invoices(): Response
    {
        $tenant = tenant();

        $invoices = TenantInvoice::forTenant($tenant->id)
            ->latest()
            ->paginate(10)
            ->through(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'period_display' => $invoice->period_display,
                    'total' => $invoice->total,
                    'formatted_total' => $invoice->formatted_total,
                    'status' => $invoice->status,
                    'status_label' => $invoice->status_label,
                    'status_color' => $invoice->status_color,
                    'is_overdue' => $invoice->is_overdue,
                    'due_at' => $invoice->due_at?->format('d M Y'),
                    'paid_at' => $invoice->paid_at?->format('d M Y'),
                    'plan_name' => $invoice->plan?->name,
                    'created_at' => $invoice->created_at->format('d M Y'),
                ];
            });

        return Inertia::render('Subscription/Invoices', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Display single invoice
     */
    public function showInvoice(TenantInvoice $invoice): Response
    {
        // Ensure invoice belongs to current tenant
        if ($invoice->tenant_id !== tenant()->id) {
            abort(403);
        }

        return Inertia::render('Subscription/InvoiceDetail', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'period_start' => $invoice->period_start->format('d M Y'),
                'period_end' => $invoice->period_end->format('d M Y'),
                'period_display' => $invoice->period_display,
                'billing_cycle' => $invoice->billing_cycle,
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount,
                'tax' => $invoice->tax,
                'total' => $invoice->total,
                'formatted_subtotal' => $invoice->formatted_subtotal,
                'formatted_total' => $invoice->formatted_total,
                'currency' => $invoice->currency,
                'status' => $invoice->status,
                'status_label' => $invoice->status_label,
                'status_color' => $invoice->status_color,
                'is_overdue' => $invoice->is_overdue,
                'due_at' => $invoice->due_at?->format('d M Y H:i'),
                'paid_at' => $invoice->paid_at?->format('d M Y H:i'),
                'line_items' => $invoice->line_items,
                'payment_method' => $invoice->payment_method,
                'created_at' => $invoice->created_at->format('d M Y H:i'),
            ],
            'plan' => $invoice->plan ? [
                'name' => $invoice->plan->name,
                'slug' => $invoice->plan->slug,
            ] : null,
        ]);
    }

    /**
     * Display subscription expired page
     */
    public function expired(): Response
    {
        $tenant = tenant();

        return Inertia::render('Subscription/Expired', [
            'status' => $tenant->subscription_status,
            'status_label' => $tenant->subscription_status_label,
            'subscription_ends_at' => $tenant->subscription_ends_at?->format('d M Y'),
        ]);
    }
}
