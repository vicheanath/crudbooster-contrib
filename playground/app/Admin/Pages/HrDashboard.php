<?php

namespace App\Admin\Pages;

use App\Admin\Resources\Hr\DepartmentResource;
use App\Admin\Resources\Hr\EmployeeResource;
use App\Admin\Resources\Hr\LeaveRequestResource;
use App\Models\HrDepartment;
use App\Models\HrEmployee;
use App\Models\HrLeaveRequest;
use CrudBooster\Pages\Page;

/**
 * HR example: headcount, who is away today, requests waiting for a decision and the size of
 * each department.
 */
class HrDashboard extends Page
{
    protected static ?string $title = 'HR Dashboard';

    protected static ?string $navigationIcon = 'fa fa-pie-chart';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = -1;

    protected static string $view = 'admin.pages.hr-dashboard';

    public function data(): array
    {
        $today = now()->toDateString();
        $awayToday = HrLeaveRequest::query()->with('employee')->where('status', 'approved')
            ->whereDate('starts_on', '<=', $today)->whereDate('ends_on', '>=', $today)->get();

        return [
            'stats' => [
                ['label' => 'Employees', 'value' => HrEmployee::query()->where('status', '!=', 'terminated')->count(), 'icon' => 'fa fa-users', 'color' => 'primary', 'url' => EmployeeResource::url()],
                ['label' => 'Away today', 'value' => $awayToday->count(), 'icon' => 'fa fa-plane', 'color' => 'info', 'url' => LeaveRequestResource::url(parameters: ['filters' => ['status' => 'approved']])],
                ['label' => 'To approve', 'value' => HrLeaveRequest::query()->where('status', 'pending')->count(), 'icon' => 'fa fa-hourglass-half', 'color' => 'warning', 'url' => LeaveRequestResource::url(parameters: ['filters' => ['status' => 'pending']])],
                ['label' => 'Remote', 'value' => HrEmployee::query()->where('is_remote', true)->where('status', '!=', 'terminated')->count(), 'icon' => 'fa fa-home', 'color' => 'success', 'url' => EmployeeResource::url(parameters: ['filters' => ['is_remote' => 1]])],
            ],
            'awayToday' => $awayToday,
            'departments' => HrDepartment::query()
                ->withCount(['employees' => fn ($query) => $query->where('status', '!=', 'terminated')])
                ->orderByDesc('employees_count')->get(),
            'departmentUrl' => fn (HrDepartment $department) => EmployeeResource::url(parameters: ['filters' => ['department' => $department->id]]),
            'allDepartmentsUrl' => DepartmentResource::url(),
        ];
    }
}
