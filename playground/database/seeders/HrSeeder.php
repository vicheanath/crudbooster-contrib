<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo data for the HR resources. Safe to run again: rows are matched on their unique keys.
 */
class HrSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        mt_srand(7);

        $departments = [
            'ENG' => ['Engineering', 'Builds and runs the product.'],
            'SAL' => ['Sales', 'Finds and closes new customers.'],
            'MKT' => ['Marketing', 'Brand, campaigns and content.'],
            'FIN' => ['Finance', 'Accounting, payroll and budgets.'],
            'OPS' => ['Operations', 'Office, IT and logistics.'],
        ];
        foreach ($departments as $code => [$name, $description]) {
            DB::table('hr_departments')->updateOrInsert(['code' => $code], ['name' => $name, 'description' => $description, 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]);
        }
        $departmentIds = DB::table('hr_departments')->pluck('id', 'code');

        foreach (['PHP', 'Laravel', 'JavaScript', 'SQL', 'Negotiation', 'Copywriting', 'Excel', 'Project management', 'Customer support'] as $skill) {
            DB::table('hr_skills')->updateOrInsert(['name' => $skill], ['updated_at' => $now, 'created_at' => $now]);
        }
        $skillIds = DB::table('hr_skills')->pluck('id', 'name');

        // [name, department, job title, type, manager (index in this list), skills]
        $people = [
            ['Amelia Brooks', 'ENG', 'Head of Engineering', 'full_time', null, ['PHP', 'Laravel', 'Project management']],
            ['Noah Chen', 'ENG', 'Senior Developer', 'full_time', 0, ['PHP', 'Laravel', 'SQL']],
            ['Sofia Martins', 'ENG', 'Frontend Developer', 'full_time', 0, ['JavaScript']],
            ['Liam Okafor', 'ENG', 'Developer', 'contract', 1, ['PHP', 'JavaScript']],
            ['Mia Novak', 'ENG', 'Engineering Intern', 'intern', 1, ['JavaScript']],
            ['Ethan Wright', 'SAL', 'Sales Director', 'full_time', null, ['Negotiation']],
            ['Chloe Dubois', 'SAL', 'Account Executive', 'full_time', 5, ['Negotiation', 'Customer support']],
            ['Lucas Silva', 'SAL', 'Account Executive', 'full_time', 5, ['Negotiation']],
            ['Ava Kowalski', 'SAL', 'Sales Assistant', 'part_time', 5, ['Excel', 'Customer support']],
            ['Oliver Hughes', 'MKT', 'Marketing Manager', 'full_time', null, ['Copywriting', 'Project management']],
            ['Isla Romero', 'MKT', 'Content Writer', 'part_time', 9, ['Copywriting']],
            ['James Patel', 'FIN', 'Finance Manager', 'full_time', null, ['Excel']],
            ['Emma Johansson', 'FIN', 'Accountant', 'full_time', 11, ['Excel', 'SQL']],
            ['Henry Adeyemi', 'OPS', 'Operations Lead', 'full_time', null, ['Project management']],
            ['Grace Kim', 'OPS', 'IT Support', 'contract', 13, ['Customer support', 'SQL']],
            ['Jack Moreau', 'OPS', 'Office Coordinator', 'full_time', 13, ['Excel'], 'terminated'],
        ];
        $salaries = ['full_time' => [52000, 140000], 'part_time' => [24000, 40000], 'contract' => [60000, 95000], 'intern' => [18000, 24000]];

        $ids = [];
        foreach ($people as $i => $person) {
            [$name, $department, $title, $type, $manager, $skills] = $person;
            $number = sprintf('EMP-%04d', $i + 1);
            DB::table('hr_employees')->updateOrInsert(['employee_number' => $number], [
                'hr_department_id' => $departmentIds[$department],
                'manager_id' => $manager === null ? null : $ids[$manager],
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
                'phone' => sprintf('+1 555 01%02d', $i + 1),
                'job_title' => $title,
                'employment_type' => $type,
                'hired_on' => $now->copy()->subDays(mt_rand(60, 2200))->toDateString(),
                'salary' => round(mt_rand(...$salaries[$type]), -2),
                'status' => $person[6] ?? 'active',
                'is_remote' => mt_rand(0, 3) === 0,
                'notes' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]);
            $ids[$i] = DB::table('hr_employees')->where('employee_number', $number)->value('id');

            foreach ($skills as $skill) {
                DB::table('hr_employee_skill')->updateOrInsert(['hr_employee_id' => $ids[$i], 'hr_skill_id' => $skillIds[$skill]], []);
            }
        }

        // [employee index, type, starts in N days, length in days, status]
        $leave = [
            [1, 'annual', -1, 5, 'approved'],   // away today
            [6, 'sick', 0, 2, 'approved'],      // away today
            [2, 'annual', 10, 7, 'pending'],
            [10, 'parental', 20, 60, 'pending'],
            [7, 'annual', 5, 3, 'pending'],
            [12, 'unpaid', -40, 3, 'approved'],
            [3, 'annual', -20, 4, 'rejected'],
            [14, 'sick', -8, 1, 'approved'],
        ];
        foreach ($leave as [$employee, $type, $startsIn, $days, $status]) {
            $startsOn = $now->copy()->addDays($startsIn)->toDateString();
            DB::table('hr_leave_requests')->updateOrInsert(['hr_employee_id' => $ids[$employee], 'starts_on' => $startsOn], [
                'type' => $type,
                'ends_on' => $now->copy()->addDays($startsIn + $days - 1)->toDateString(),
                'reason' => ucfirst($type).' leave',
                'status' => $status,
                'decided_at' => $status === 'pending' ? null : $now,
                'updated_at' => $now,
                'created_at' => $now,
            ]);
        }
    }
}
