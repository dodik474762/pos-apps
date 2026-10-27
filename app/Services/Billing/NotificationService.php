<?php

namespace App\Services\Billing;

use App\Models\Billing\Subscription;
use App\Models\Billing\SubscriptionInvoice;
use App\Models\Billing\SubscriptionNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(private BillingPeriodService $periods) {}

    public function run(): int
    {
        $sent = 0;
        $today = $this->periods->now()->startOfDay();

        $invoices = SubscriptionInvoice::withoutCompanyScope()
            ->with(['subscription.plan'])
            ->where('status', SubscriptionInvoice::STATUS_PENDING)
            ->get();

        foreach ($invoices as $invoice) {
            $days = $this->periods->daysUntil($invoice->due_date->copy()->startOfDay());

            if (in_array($days, config('billing.notifications.reminder_days', [7, 3, 0]), true)) {
                $type = 'DUE_D'.$days;
                $sent += $this->sendOnce($invoice->subscription, $invoice->company_id, $type, $today, $invoice);
            } elseif ($days < 0 && ! $this->hasSent($invoice->subscription_id, 'OVERDUE')) {
                $sent += $this->sendOnce($invoice->subscription, $invoice->company_id, 'OVERDUE', $today, $invoice);
            }
        }

        $trials = Subscription::withoutCompanyScope()
            ->with(['plan'])
            ->where('status', Subscription::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->get();

        foreach ($trials as $subscription) {
            $days = $this->periods->daysUntil($subscription->trial_ends_at->copy()->startOfDay());

            if (in_array($days, config('billing.notifications.trial_reminder_days', [3, 0]), true)) {
                $sent += $this->sendOnce($subscription, $subscription->company_id, 'TRIAL_D'.$days, $today, null);
            }
        }

        return $sent;
    }

    protected function sendOnce(?Subscription $subscription, int $companyId, string $type, $sentOn, ?SubscriptionInvoice $invoice): int
    {
        if (! $subscription) {
            return 0;
        }

        $channels = config('billing.notifications.channels', ['log']);
        $company = DB::table('company')->where('id', $companyId)->first();
        $recipient = $company->email ?? null;
        $count = 0;

        foreach ($channels as $channel) {
            try {
                $notification = SubscriptionNotification::create([
                    'company_id' => $companyId,
                    'subscription_id' => $subscription->id,
                    'type' => $type,
                    'sent_on' => $sentOn->toDateString(),
                    'channel' => $channel,
                    'recipient' => $channel === 'mail' ? $recipient : null,
                    'status' => 'SENT',
                ]);
            } catch (QueryException $e) {
                continue;
            }

            try {
                $this->deliver($channel, $subscription, $invoice, $company, $type);
                $count++;
            } catch (\Throwable $e) {
                $notification->status = 'FAILED';
                $notification->error = $e->getMessage();
                $notification->save();
                Log::error('billing.notification_failed', [
                    'subscription_id' => $subscription->id,
                    'type' => $type,
                    'channel' => $channel,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    protected function deliver(string $channel, Subscription $subscription, ?SubscriptionInvoice $invoice, $company, string $type): void
    {
        $subject = $this->subject($type, $subscription, $invoice);
        $body = $this->body($type, $subscription, $invoice);

        if ($channel === 'mail' && ! empty($company->email)) {
            Mail::raw($body, function ($message) use ($company, $subject) {
                $message->to($company->email)->subject($subject);
            });

            return;
        }

        if ($channel === 'whatsapp') {
            $this->sendWhatsApp($company->no_hp ?? null, $body);

            return;
        }

        Log::info('billing.notification', [
            'type' => $type,
            'company_id' => $subscription->company_id,
            'subject' => $subject,
            'body' => $body,
        ]);
    }

    protected function sendWhatsApp(?string $phone, string $body): void
    {
        $provider = config('billing.notifications.whatsapp.provider', 'log');

        if ($provider === 'log' || ! $phone) {
            Log::info('billing.whatsapp', ['phone' => $phone, 'body' => $body]);

            return;
        }

        Log::warning('billing.whatsapp_provider_not_configured', ['provider' => $provider]);
    }

    protected function hasSent(int $subscriptionId, string $type): bool
    {
        return SubscriptionNotification::where('subscription_id', $subscriptionId)
            ->where('type', $type)
            ->exists();
    }

    protected function subject(string $type, Subscription $subscription, ?SubscriptionInvoice $invoice): string
    {
        $plan = $subscription->plan->name ?? 'Langganan';

        return match (true) {
            str_starts_with($type, 'TRIAL') => 'Pengingat Trial '.$plan,
            $type === 'OVERDUE' => 'Tagihan Langganan Telah Jatuh Tempo',
            default => 'Pengingat Tagihan Langganan '.$plan,
        };
    }

    protected function body(string $type, Subscription $subscription, ?SubscriptionInvoice $invoice): string
    {
        $plan = $subscription->plan->name ?? '-';

        if (str_starts_with($type, 'TRIAL')) {
            return "Trial paket {$plan} akan berakhir pada {$subscription->trial_ends_at->format('d/m/Y')}. "
                .'Silakan pilih paket dan lakukan pembayaran untuk tetap menggunakan layanan.';
        }

        if ($invoice) {
            return "Tagihan {$invoice->number} sebesar Rp ".number_format((float) $invoice->amount, 0, ',', '.')
                ." untuk paket {$plan} jatuh tempo pada {$invoice->due_date->format('d/m/Y')}. "
                .'Silakan lakukan pembayaran melalui halaman langganan.';
        }

        return "Tagihan langganan paket {$plan} perlu segera diselesaikan.";
    }
}
