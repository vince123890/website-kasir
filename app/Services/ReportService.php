<?php

namespace App\Services;

use App\Repositories\ReportRepository;
use Carbon\Carbon;
use Exception;

class ReportService extends BaseService
{
    public function __construct(
        protected ReportRepository $reportRepository
    ) {
    }

    /**
     * Generate sales report
     */
    public function generateSalesReport(array $filters): array
    {
        return $this->executeTransaction(function () use ($filters) {
            // Set default date range if not provided
            if (empty($filters['start_date'])) {
                $filters['start_date'] = Carbon::now()->startOfMonth()->format('Y-m-d');
            }

            if (empty($filters['end_date'])) {
                $filters['end_date'] = Carbon::now()->endOfMonth()->format('Y-m-d');
            }

            // Validate date range
            $startDate = Carbon::parse($filters['start_date']);
            $endDate = Carbon::parse($filters['end_date']);

            if ($startDate->gt($endDate)) {
                throw new Exception('Start date cannot be after end date');
            }

            // Get report data
            $reportData = $this->reportRepository->getSalesReport($filters);

            return $this->successResponse('Sales report generated successfully', $reportData);
        });
    }

    /**
     * Generate inventory report
     */
    public function generateInventoryReport(array $filters): array
    {
        return $this->executeTransaction(function () use ($filters) {
            $reportData = $this->reportRepository->getInventoryReport($filters);

            return $this->successResponse('Inventory report generated successfully', $reportData);
        });
    }

    /**
     * Generate financial report
     */
    public function generateFinancialReport(array $filters): array
    {
        return $this->executeTransaction(function () use ($filters) {
            // Set default date range if not provided
            if (empty($filters['start_date'])) {
                $filters['start_date'] = Carbon::now()->startOfMonth();
            }

            if (empty($filters['end_date'])) {
                $filters['end_date'] = Carbon::now()->endOfMonth();
            }

            $reportData = $this->reportRepository->getFinancialReport($filters);

            return $this->successResponse('Financial report generated successfully', $reportData);
        });
    }

    /**
     * Generate cashier performance report
     */
    public function generateCashierReport(array $filters): array
    {
        return $this->executeTransaction(function () use ($filters) {
            // Set default date range if not provided
            if (empty($filters['start_date'])) {
                $filters['start_date'] = Carbon::now()->startOfMonth()->format('Y-m-d');
            }

            if (empty($filters['end_date'])) {
                $filters['end_date'] = Carbon::now()->endOfMonth()->format('Y-m-d');
            }

            $reportData = $this->reportRepository->getCashierReport($filters);

            return $this->successResponse('Cashier report generated successfully', $reportData);
        });
    }

    /**
     * Export report to Excel/PDF/CSV
     */
    public function exportReport(string $reportType, string $format, array $filters): string
    {
        // Generate report data based on type
        $reportData = match ($reportType) {
            'sales' => $this->reportRepository->getSalesReport($filters),
            'inventory' => $this->reportRepository->getInventoryReport($filters),
            'financial' => $this->reportRepository->getFinancialReport($filters),
            'cashier' => $this->reportRepository->getCashierReport($filters),
            default => throw new Exception('Invalid report type'),
        };

        // Export based on format
        return match ($format) {
            'excel' => $this->exportToExcel($reportType, $reportData),
            'pdf' => $this->exportToPdf($reportType, $reportData),
            'csv' => $this->exportToCsv($reportType, $reportData),
            default => throw new Exception('Invalid export format'),
        };
    }

    /**
     * Export to Excel
     */
    protected function exportToExcel(string $reportType, array $reportData): string
    {
        // Implementation will use Laravel Excel
        $filename = $reportType . '_report_' . date('Y-m-d_His') . '.xlsx';
        $filepath = storage_path('app/exports/' . $filename);

        // Create directory if not exists
        if (!file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        // For now, return filename (actual implementation would use Laravel Excel)
        return $filename;
    }

    /**
     * Export to PDF
     */
    protected function exportToPdf(string $reportType, array $reportData): string
    {
        // Implementation will use DomPDF or Snappy
        $filename = $reportType . '_report_' . date('Y-m-d_His') . '.pdf';
        $filepath = storage_path('app/exports/' . $filename);

        // Create directory if not exists
        if (!file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        // For now, return filename (actual implementation would use DomPDF)
        return $filename;
    }

    /**
     * Export to CSV
     */
    protected function exportToCsv(string $reportType, array $reportData): string
    {
        $filename = $reportType . '_report_' . date('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        // Create directory if not exists
        if (!file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        // Create CSV file
        $file = fopen($filepath, 'w');

        // Add headers and data based on report type
        if ($reportType === 'sales') {
            fputcsv($file, ['Date', 'Store', 'Cashier', 'Transaction Number', 'Amount', 'Payment Method']);

            if (isset($reportData['transactions'])) {
                foreach ($reportData['transactions'] as $transaction) {
                    fputcsv($file, [
                        $transaction->transaction_date->format('Y-m-d'),
                        $transaction->store->name ?? 'N/A',
                        $transaction->cashier->name ?? 'N/A',
                        $transaction->transaction_number,
                        $transaction->total_amount,
                        $transaction->payment_method,
                    ]);
                }
            }
        } elseif ($reportType === 'inventory') {
            fputcsv($file, ['Product', 'SKU', 'Category', 'Store', 'Quantity', 'Min Stock', 'Max Stock', 'Value']);

            if (isset($reportData['stocks'])) {
                foreach ($reportData['stocks'] as $stock) {
                    fputcsv($file, [
                        $stock->product->name,
                        $stock->product->sku,
                        $stock->product->category->name ?? 'N/A',
                        $stock->store->name ?? 'N/A',
                        $stock->quantity,
                        $stock->min_stock,
                        $stock->max_stock,
                        $stock->quantity * $stock->product->selling_price,
                    ]);
                }
            }
        } elseif ($reportType === 'cashier') {
            fputcsv($file, ['Cashier', 'Total Sales', 'Transactions', 'Avg Transaction', 'Discount Given']);

            if (isset($reportData['cashier_performance'])) {
                foreach ($reportData['cashier_performance'] as $performance) {
                    fputcsv($file, [
                        $performance['cashier_name'],
                        $performance['total_sales'],
                        $performance['transaction_count'],
                        $performance['avg_transaction_value'],
                        $performance['total_discount_given'],
                    ]);
                }
            }
        }

        fclose($file);

        return $filename;
    }

    /**
     * Get admin dashboard stats
     */
    public function getAdminDashboardStats(): array
    {
        return $this->executeTransaction(function () {
            $stats = $this->reportRepository->getAdminDashboardStats();
            return $this->successResponse('Admin dashboard stats retrieved', $stats);
        });
    }

    /**
     * Get tenant dashboard stats
     */
    public function getTenantDashboardStats(int $tenantId): array
    {
        return $this->executeTransaction(function () use ($tenantId) {
            $stats = $this->reportRepository->getTenantDashboardStats($tenantId);
            return $this->successResponse('Tenant dashboard stats retrieved', $stats);
        });
    }

    /**
     * Get store dashboard stats
     */
    public function getStoreDashboardStats(int $storeId): array
    {
        return $this->executeTransaction(function () use ($storeId) {
            $stats = $this->reportRepository->getStoreDashboardStats($storeId);
            return $this->successResponse('Store dashboard stats retrieved', $stats);
        });
    }

    /**
     * Get cashier dashboard stats
     */
    public function getCashierDashboardStats(int $cashierId): array
    {
        return $this->executeTransaction(function () use ($cashierId) {
            $stats = $this->reportRepository->getCashierDashboardStats($cashierId);
            return $this->successResponse('Cashier dashboard stats retrieved', $stats);
        });
    }
}
