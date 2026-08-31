<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

/**
 * Message templates for every alert the lifecycle modules raise. Placeholders
 * are :name tokens, substituted by NotificationService::render().
 */
class NotificationTemplateSeeder extends Seeder
{
    private const TEMPLATES = [
        [
            'name' => 'maintenance_due',
            'channel' => 'in_app',
            'subject' => 'Maintenance due: :asset_code',
            'body' => ':asset_code is scheduled for :maintenance_type maintenance on :scheduled_at.',
        ],
        [
            'name' => 'maintenance_overdue',
            'channel' => 'mail',
            'subject' => 'Maintenance overdue: :asset_code',
            'body' => ':asset_code was scheduled for maintenance on :scheduled_at and the job is still open.',
        ],
        [
            'name' => 'warranty_expiring',
            'channel' => 'mail',
            'subject' => 'Warranty expiring: :asset_code',
            'body' => 'The warranty from :vendor_name for :asset_code expires on :end_date (:days_remaining days left).',
        ],
        [
            'name' => 'warranty_expired',
            'channel' => 'mail',
            'subject' => 'Warranty expired: :asset_code',
            'body' => 'The warranty from :vendor_name for :asset_code expired on :end_date. The asset is no longer covered.',
        ],
        [
            'name' => 'transfer_approved',
            'channel' => 'in_app',
            'subject' => 'Transfer approved: :asset_code',
            'body' => 'Your transfer request for :asset_code was approved by :approved_by.',
        ],
        [
            'name' => 'transfer_rejected',
            'channel' => 'in_app',
            'subject' => 'Transfer rejected: :asset_code',
            'body' => 'Your transfer request for :asset_code was rejected by :approved_by.',
        ],
        [
            'name' => 'disposal_approved',
            'channel' => 'in_app',
            'subject' => 'Disposal approved: :asset_code',
            'body' => 'Your disposal request for :asset_code was approved by :approved_by.',
        ],
        [
            'name' => 'disposal_rejected',
            'channel' => 'in_app',
            'subject' => 'Disposal rejected: :asset_code',
            'body' => 'Your disposal request for :asset_code was rejected by :approved_by.',
        ],
    ];

    public function run(): void
    {
        foreach (self::TEMPLATES as $template) {
            NotificationTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template + ['is_active' => true],
            );
        }
    }
}
