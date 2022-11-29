<?php

namespace App\Http\Controllers\Admin;

use App\Advertisement;
use App\Category;
use App\Content;
use App\Guest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Reporter;
use App\Tag;
use App\Team;
use App\User;
use App\UserLog;
use Analytics;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Analytics\Period;
use Spatie\Sitemap\SitemapGenerator;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function index()
    {
        // Permission::create(['name' => 'browse_roles', 'table_name' => 'tbl_roles']);
        // Permission::create(['name' => 'create_roles', 'table_name' => 'tbl_roles']);
        // Permission::create(['name' => 'read_roles', 'table_name' => 'tbl_roles']);
        // Permission::create(['name' => 'update_roles', 'table_name' => 'tbl_roles']);
        // Permission::create(['name' => 'delete_roles', 'table_name' => 'tbl_roles']);

        $totalRoles = Role::count();
        $totalUsers = User::count();
        $totalUserLogs = UserLog::count();
        $totalNews = Content::count();
        $totalNewsCategories = Category::count();
        $totalTags = Tag::count();
        $totalReporters = Reporter::count();
        $totalGuests = Guest::count();
        $totalTeams = Team::count();
        $totalAds = Advertisement::count();

        return view('admin.pages.dashboard', compact(
            'totalRoles',
            'totalUsers',
            'totalUserLogs',
            'totalNews',
            'totalNewsCategories',
            'totalTags',
            'totalReporters',
            'totalGuests',
            'totalTeams',
            'totalAds'
        ));
    }

    public function analytics()
    {
        $analyticsData = Analytics::fetchVisitorsAndPageViews(Period::days(7));
        $todayAnalyticsData = Analytics::fetchVisitorsAndPageViews(Period::days(0));
        $weekAnalyticsData = Analytics::fetchVisitorsAndPageViews(Period::days(7));
        $monthAnalyticsData = Analytics::fetchVisitorsAndPageViews(Period::days(30));

        $weeklyVisitors = 0;
        $weeklyPageViews = 0;
        foreach ($weekAnalyticsData as $k => $subArray) {
            $weeklyVisitors += $subArray['visitors'];
            $weeklyPageViews += $subArray['pageViews'];
        }

        $monthlyVisitors = 0;
        $monthlyPageViews = 0;
        foreach ($monthAnalyticsData as $k => $subArray) {
            $monthlyVisitors += $subArray['visitors'];
            $monthlyPageViews += $subArray['pageViews'];
        }

        return view('admin.pages.analytics', compact('analyticsData', 'todayAnalyticsData', 'weeklyVisitors', 'weeklyPageViews', 'monthlyPageViews', 'monthlyVisitors'));
    }
}
