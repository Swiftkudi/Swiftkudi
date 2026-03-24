<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export class for Financial Reports
 */
class FinancialReportExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @var array
     */
    private $data;

    /**
     * Create a new export instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the array data to export.
     *
     * @return array
     */
    public function array(): array
    {
        $rows = [];

        // Add header section
        $rows[] = ['EARNDESK FINANCIAL REPORT'];
        $rows[] = ['Period:', $this->data['start_date']->format('Y-m-d') . ' to ' . $this->data['end_date']->format('Y-m-d')];
        $rows[] = [];

        // Revenue Section
        $rows[] = ['REVENUE BREAKDOWN'];
        $rows[] = ['Source', 'Amount'];
        $rows[] = ['Activation Revenue', $this->data['revenue_by_source']['activation']];
        $rows[] = ['Task Creation', $this->data['revenue_by_source']['task_creation']];
        $rows[] = ['Affiliate Commission', $this->data['revenue_by_source']['affiliate_commission']];
        $rows[] = ['Withdrawal Fee', $this->data['revenue_by_source']['withdrawal_fee']];
        $rows[] = ['Advertising', $this->data['revenue_by_source']['advertising']];
        $rows[] = ['Marketplace', $this->data['revenue_by_source']['marketplace']];
        $rows[] = ['Other Revenue', $this->data['revenue_by_source']['other']];
        $rows[] = ['TOTAL REVENUE', $this->data['total_revenue']];
        $rows[] = [];

        // Expense Section
        $rows[] = ['EXPENSE BREAKDOWN'];
        $rows[] = ['Category', 'Amount'];
        $rows[] = ['Gateway Fees', $this->data['expenses_by_category']['gateway_fees']];
        $rows[] = ['Server Costs', $this->data['expenses_by_category']['server_cost']];
        $rows[] = ['Email Costs', $this->data['expenses_by_category']['email_cost']];
        $rows[] = ['SMS Costs', $this->data['expenses_by_category']['sms_cost']];
        $rows[] = ['Staff Costs', $this->data['expenses_by_category']['staff_cost']];
        $rows[] = ['Marketing', $this->data['expenses_by_category']['marketing']];
        $rows[] = ['Operations', $this->data['expenses_by_category']['operations']];
        $rows[] = ['Referral Bonuses', $this->data['expenses_by_category']['referral_bonus']];
        $rows[] = ['Custom Expenses', $this->data['expenses_by_category']['custom']];
        $rows[] = ['TOTAL EXPENSES', $this->data['total_expenses']];
        $rows[] = [];

        // Activation Stats
        $rows[] = ['ACTIVATION STATISTICS'];
        $rows[] = ['Metric', 'Value'];
        $rows[] = ['Total Activations', $this->data['activation_stats']['total']];
        $rows[] = ['Normal Activations', $this->data['activation_stats']['normal']];
        $rows[] = ['Referral Activations', $this->data['activation_stats']['referral']];
        $rows[] = ['Activation Revenue', $this->data['activation_stats']['revenue']];
        $rows[] = ['Referral Bonuses Paid', $this->data['activation_stats']['referral_bonus']];
        $rows[] = [];

        // Summary
        $rows[] = ['FINANCIAL SUMMARY'];
        $rows[] = ['Total Revenue', $this->data['total_revenue']];
        $rows[] = ['Total Expenses', $this->data['total_expenses']];
        $rows[] = ['NET PROFIT', $this->data['net_profit']];
        $rows[] = [];

        // Daily Breakdown
        $rows[] = ['DAILY BREAKDOWN'];
        $rows[] = ['Date', 'Revenue', 'Expenses', 'Profit'];

        foreach ($this->data['daily_breakdown'] as $day) {
            $rows[] = [
                $day['date'],
                $day['revenue'],
                $day['expense'],
                $day['profit'],
            ];
        }

        return $rows;
    }

    /**
     * Get the headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * Get the worksheet title.
     *
     * @return string
     */
    public function title(): string
    {
        return 'Financial Report';
    }
}
