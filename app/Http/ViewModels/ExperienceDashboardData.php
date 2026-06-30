<?php

namespace App\Http\ViewModels;

use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ExperienceDashboardData
{
    public function platform(): array
    {
        return [
            'stats' => [
                ['label' => 'Revenue', 'value' => $this->money($this->sum('payment_transactions', 'amount', ['status' => 'successful'])), 'icon' => 'fa-chart-line', 'tone' => 'success'],
                ['label' => 'Wallet Balance', 'value' => $this->money($this->sum('wallets', 'available_balance')), 'icon' => 'fa-wallet', 'tone' => 'primary'],
                ['label' => 'Verifications', 'value' => $this->count('verification_requests'), 'icon' => 'fa-id-card', 'tone' => 'info'],
                ['label' => 'Tenants', 'value' => $this->count('tenants'), 'icon' => 'fa-building', 'tone' => 'warning'],
            ],
            'sections' => [
                ['title' => 'Payments', 'metric' => $this->count('payment_transactions'), 'caption' => 'Total payment records', 'icon' => 'fa-credit-card'],
                ['title' => 'Products', 'metric' => $this->count('products'), 'caption' => 'Catalog products', 'icon' => 'fa-boxes-stacked'],
                ['title' => 'Notifications', 'metric' => $this->count('notifications'), 'caption' => 'Notification records', 'icon' => 'fa-bell'],
                ['title' => 'Support', 'metric' => $this->count('support_tickets'), 'caption' => 'Support tickets', 'icon' => 'fa-life-ring'],
            ],
            'activity' => $this->activity(),
        ];
    }

    public function tenant(Tenant $tenant): array
    {
        return [
            'stats' => [
                ['label' => 'Wallet Balance', 'value' => $this->money($this->sum('wallets', 'available_balance', ['tenant_id' => $tenant->id])), 'icon' => 'fa-wallet', 'tone' => 'primary'],
                ['label' => 'Today Verifications', 'value' => $this->countToday('verification_requests', $tenant->id), 'icon' => 'fa-id-card', 'tone' => 'info'],
                ['label' => 'Monthly Spend', 'value' => $this->money($this->sumMonth('wallet_transactions', 'amount', $tenant->id, ['type' => 'debit'])), 'icon' => 'fa-receipt', 'tone' => 'warning'],
                ['label' => 'Available Products', 'value' => $this->count('tenant_products', ['tenant_id' => $tenant->id, 'is_enabled' => 1]), 'icon' => 'fa-box', 'tone' => 'success'],
            ],
            'transactions' => $this->recentWalletTransactions($tenant->id),
            'notifications' => $this->recentRows('notifications', $tenant->id, ['event_type', 'status', 'created_at'], 5),
            'support' => $this->recentRows('support_tickets', $tenant->id, ['reference', 'subject', 'status', 'created_at'], 5),
        ];
    }

    public function wallet(?Tenant $tenant = null): array
    {
        $tenantId = $tenant?->id;

        return [
            'wallets' => $this->rows('wallets', $tenantId, ['currency', 'available_balance', 'reserved_balance', 'frozen_balance', 'status']),
            'transactions' => $this->recentWalletTransactions($tenantId),
            'funding' => $this->recentRows('payment_transactions', $tenantId, ['reference', 'amount', 'currency', 'status', 'created_at'], 10),
            'reservations' => $this->recentRows('wallet_reservations', $tenantId, ['reference', 'amount', 'captured_amount', 'released_amount', 'currency', 'status', 'created_at'], 10),
        ];
    }

    public function verification(?Tenant $tenant = null): array
    {
        $tenantId = $tenant?->id;

        return [
            'services' => $this->rows('verification_services', null, ['name', 'service_code', 'default_price', 'currency', 'status']),
            'requests' => $this->recentRows('verification_requests', $tenantId, ['reference', 'provider', 'price_charged', 'currency', 'status', 'created_at'], 10),
        ];
    }

    public function products(?Tenant $tenant = null): array
    {
        return [
            'categories' => $this->rows('product_categories', null, ['code', 'name', 'status']),
            'products' => $this->rows('products', null, ['code', 'name', 'default_price', 'currency', 'status', 'visibility']),
            'mappings' => $this->rows('product_provider_mappings', null, ['provider', 'provider_product_code', 'status', 'priority']),
            'overrides' => $this->rows('tenant_products', $tenant?->id, ['is_enabled', 'selling_price_override', 'status_override']),
        ];
    }

    private function activity(): array
    {
        return collect([
            ['label' => 'Latest tenant', 'value' => DB::table('tenants')->latest()->value('name') ?? 'No tenants yet'],
            ['label' => 'Latest payment', 'value' => DB::table('payment_transactions')->latest()->value('reference') ?? 'No payments yet'],
            ['label' => 'Latest verification', 'value' => DB::table('verification_requests')->latest()->value('reference') ?? 'No verifications yet'],
        ])->all();
    }

    private function recentWalletTransactions(?string $tenantId): array
    {
        return $this->recentRows('wallet_transactions', $tenantId, ['reference', 'type', 'amount', 'currency', 'balance_after', 'status', 'created_at'], 10);
    }

    private function rows(string $table, ?string $tenantId, array $columns, int $limit = 20): array
    {
        if (! $this->tableExists($table)) {
            return [];
        }

        $query = DB::table($table)->select($columns);

        if ($tenantId !== null && $this->hasColumn($table, 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->limit($limit)->get()->map(fn ($row): array => (array) $row)->all();
    }

    private function recentRows(string $table, ?string $tenantId, array $columns, int $limit): array
    {
        if (! $this->tableExists($table)) {
            return [];
        }

        $query = DB::table($table)->select($columns);

        if ($tenantId !== null && $this->hasColumn($table, 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->latest()->limit($limit)->get()->map(fn ($row): array => (array) $row)->all();
    }

    private function count(string $table, array $where = []): int
    {
        if (! $this->tableExists($table)) {
            return 0;
        }

        return (int) DB::table($table)->where($where)->count();
    }

    private function countToday(string $table, string $tenantId): int
    {
        if (! $this->tableExists($table)) {
            return 0;
        }

        return (int) DB::table($table)
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }

    private function sum(string $table, string $column, array $where = []): float
    {
        if (! $this->tableExists($table)) {
            return 0;
        }

        return (float) DB::table($table)->where($where)->sum($column);
    }

    private function sumMonth(string $table, string $column, string $tenantId, array $where = []): float
    {
        if (! $this->tableExists($table)) {
            return 0;
        }

        return (float) DB::table($table)
            ->where('tenant_id', $tenantId)
            ->where($where)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum($column);
    }

    private function money(float $amount): string
    {
        return 'NGN '.number_format($amount, 2);
    }

    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }

    private function hasColumn(string $table, string $column): bool
    {
        return DB::getSchemaBuilder()->hasColumn($table, $column);
    }
}
