<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo data for the CRM resources. Owners are the playground's admin (cms_users). Safe to run
 * again: rows are matched on their natural keys.
 */
class CrmSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ownerId = DB::table('cms_users')->orderBy('id')->value('id');

        $companies = [
            ['Northwind Traders', 'retail', 'https://northwind.example.com', 'Seattle', 420],
            ['Contoso Health', 'health', 'https://contoso-health.example.com', 'Boston', 1800],
            ['Fabrikam Software', 'software', 'https://fabrikam.example.com', 'Austin', 95],
            ['Tailspin Finance', 'finance', 'https://tailspin.example.com', 'New York', 650],
            ['Wingtip Academy', 'education', 'https://wingtip.example.com', 'Denver', 120],
            ['Litware Manufacturing', 'manufacturing', 'https://litware.example.com', 'Detroit', 2300],
        ];
        foreach ($companies as $i => [$name, $industry, $website, $city, $employees]) {
            DB::table('crm_companies')->updateOrInsert(['name' => $name], [
                'industry' => $industry, 'website' => $website, 'city' => $city, 'employees' => $employees,
                'phone' => sprintf('+1 555 02%02d', $i + 1), 'updated_at' => $now, 'created_at' => $now,
            ]);
        }
        $companyIds = DB::table('crm_companies')->pluck('id', 'name');

        foreach (['Decision maker', 'Champion', 'Technical', 'Newsletter', 'Cold'] as $tag) {
            DB::table('crm_tags')->updateOrInsert(['name' => $tag], ['updated_at' => $now, 'created_at' => $now]);
        }
        $tagIds = DB::table('crm_tags')->pluck('id', 'name');

        // [name, company, job title, tags, subscribed]
        $contacts = [
            ['Rachel Green', 'Northwind Traders', 'Head of Purchasing', ['Decision maker'], true],
            ['Tom Becker', 'Northwind Traders', 'IT Manager', ['Technical'], false],
            ['Priya Nair', 'Contoso Health', 'COO', ['Decision maker', 'Champion'], true],
            ['Marcus Lee', 'Fabrikam Software', 'CTO', ['Technical', 'Champion'], true],
            ['Elena Petrova', 'Tailspin Finance', 'Procurement Lead', ['Decision maker'], false],
            ['David Osei', 'Tailspin Finance', 'Analyst', ['Newsletter'], true],
            ['Hannah Schmidt', 'Wingtip Academy', 'Principal', ['Decision maker'], false],
            ['Carlos Ruiz', 'Litware Manufacturing', 'Plant Manager', ['Cold'], false],
            ['Yuki Tanaka', null, 'Consultant', ['Newsletter'], true],
        ];
        foreach ($contacts as [$name, $company, $title, $tags, $subscribed]) {
            $email = strtolower(str_replace(' ', '.', $name)).'@example.com';
            DB::table('crm_contacts')->updateOrInsert(['email' => $email], [
                'crm_company_id' => $company ? $companyIds[$company] : null, 'owner_id' => $ownerId, 'name' => $name,
                'job_title' => $title, 'phone' => null, 'is_subscribed' => $subscribed, 'updated_at' => $now, 'created_at' => $now,
            ]);
            $contactId = DB::table('crm_contacts')->where('email', $email)->value('id');
            foreach ($tags as $tag) {
                DB::table('crm_contact_tag')->updateOrInsert(['crm_contact_id' => $contactId, 'crm_tag_id' => $tagIds[$tag]], []);
            }
        }
        $contactIds = DB::table('crm_contacts')->pluck('id', 'name');

        // [title, company, contact, stage, amount, probability, closes in N days]
        $deals = [
            ['Store POS rollout', 'Northwind Traders', 'Rachel Green', 'negotiation', 48000, 70, 14],
            ['Inventory sync add-on', 'Northwind Traders', 'Tom Becker', 'qualified', 9500, 30, 45],
            ['Patient portal', 'Contoso Health', 'Priya Nair', 'proposal', 120000, 50, 30],
            ['Developer licences', 'Fabrikam Software', 'Marcus Lee', 'won', 18000, 100, -10],
            ['Risk dashboard', 'Tailspin Finance', 'Elena Petrova', 'lead', 65000, 10, 90],
            ['Analyst training', 'Tailspin Finance', 'David Osei', 'lost', 7000, 0, -25],
            ['Learning platform', 'Wingtip Academy', 'Hannah Schmidt', 'qualified', 22000, 30, 60],
            ['Factory floor tablets', 'Litware Manufacturing', 'Carlos Ruiz', 'lead', 85000, 10, 120],
            ['Support renewal', 'Fabrikam Software', 'Marcus Lee', 'won', 12000, 100, -40],
        ];
        foreach ($deals as [$title, $company, $contact, $stage, $amount, $probability, $closesIn]) {
            $closed = in_array($stage, ['won', 'lost'], true);
            DB::table('crm_deals')->updateOrInsert(['title' => $title], [
                'crm_company_id' => $companyIds[$company], 'crm_contact_id' => $contactIds[$contact], 'owner_id' => $ownerId,
                'stage' => $stage, 'amount' => $amount, 'probability' => $probability,
                'expected_close_on' => $now->copy()->addDays($closesIn)->toDateString(),
                'closed_at' => $closed ? $now->copy()->addDays($closesIn) : null,
                'notes' => null, 'updated_at' => $now, 'created_at' => $now,
            ]);
        }
        $dealIds = DB::table('crm_deals')->pluck('id', 'title');

        // [subject, type, deal, due in N hours, done]
        $activities = [
            ['Send revised quote', 'email', 'Store POS rollout', -20, false],      // overdue
            ['Pricing call with Rachel', 'call', 'Store POS rollout', 26, false],
            ['Portal demo', 'meeting', 'Patient portal', 50, false],
            ['Write the proposal', 'task', 'Patient portal', -3, false],           // overdue
            ['Intro call', 'call', 'Risk dashboard', 72, false],
            ['Kick-off meeting', 'meeting', 'Developer licences', -240, true],
            ['Ask for feedback', 'email', 'Analyst training', -500, true],
            ['Campus visit', 'meeting', 'Learning platform', 120, false],
        ];
        foreach ($activities as [$subject, $type, $deal, $dueIn, $done]) {
            DB::table('crm_activities')->updateOrInsert(['subject' => $subject], [
                'crm_deal_id' => $dealIds[$deal],
                'crm_contact_id' => DB::table('crm_deals')->where('id', $dealIds[$deal])->value('crm_contact_id'),
                'type' => $type, 'due_at' => $now->copy()->addHours($dueIn), 'is_done' => $done,
                'notes' => null, 'updated_at' => $now, 'created_at' => $now,
            ]);
        }
    }
}
