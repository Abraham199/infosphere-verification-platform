<?php

namespace App\Domain\Reporting\Enums;

enum MetricKey: string
{
    case TOTAL_REVENUE = 'total_revenue';
    case DAILY_REVENUE = 'daily_revenue';
    case MONTHLY_REVENUE = 'monthly_revenue';
    case WALLET_BALANCE = 'wallet_balance';
    case WALLET_CREDITS = 'wallet_credits';
    case WALLET_DEBITS = 'wallet_debits';
    case SUCCESSFUL_VERIFICATIONS = 'successful_verifications';
    case FAILED_VERIFICATIONS = 'failed_verifications';
    case PROVIDER_SUCCESS_RATE = 'provider_success_rate';
    case AVERAGE_PROCESSING_TIME = 'average_processing_time';
    case PAYMENT_SUCCESS_RATE = 'payment_success_rate';
    case FAILED_PAYMENTS = 'failed_payments';
    case PENDING_PAYMENTS = 'pending_payments';
    case REFUND_VOLUME = 'refund_volume';
    case TOP_SELLING_PRODUCTS = 'top_selling_products';
    case PRODUCT_REVENUE = 'product_revenue';
    case PRODUCT_USAGE = 'product_usage';
    case ACTIVE_TENANTS = 'active_tenants';
    case TENANT_REVENUE = 'tenant_revenue';
    case TENANT_GROWTH = 'tenant_growth';
    case NOTIFICATION_VOLUME = 'notification_volume';
    case QUEUE_STATISTICS = 'queue_statistics';
    case API_USAGE = 'api_usage';
}
