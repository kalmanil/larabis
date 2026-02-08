<?php

namespace App\Features\Pages\Contracts;

/**
 * Contract for page data and admin panel data per tenant/view.
 * Implementations are resolved by PageDataServiceFactory from tenant id + view code.
 */
interface PageDataServiceInterface
{
    /**
     * Page data for the current view (default or admin).
     *
     * @return array<string, mixed>
     */
    public function getPageData(): array;

    /**
     * Admin dashboard data (used when view is admin). Return empty array if not admin.
     *
     * @return array<string, mixed>
     */
    public function getAdminDashboardData(): array;

    /**
     * Admin theme configuration (used when view is admin). Return empty array if not admin.
     *
     * @return array<string, mixed>
     */
    public function getAdminTheme(): array;
}
