<?php

namespace App\Http\Controllers;

use App\Exports\TodoListExport;
use App\Http\Requests\TodoListRequest;
use App\Models\Todo_list;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TodoListController extends Controller
{
    function store(TodoListRequest $request)
    {

        if (Date::parse($request->due_date) < Date::now()) {
            return response()->json(['message' => "Date can't be lower than today"], 400);
        }

        if ($request->status === null) {
            $request["status"] = "pending";
        }

        if ($request->time_tracked === null) {
            $request["time_tracked"] = 0;
        }

        $todo = Todo_list::create($request->all());
        return response()->json(['message' => 'Success', 'data' => $todo], 200);
    }

    function getChartData(Request $request)
    {
        $queryParam = $request->filled('type') ? $request->input('type') : '';


        if ($queryParam === 'status') {
            $query = Todo_list::groupBy('status')->select('status', DB::raw('count(*) as total'))->pluck('total', 'status')->toArray();
            $enumStatus = ["pending", "open", "in_progress", "completed"];
            $statusSummary = [];

            foreach ($enumStatus as $enum) {
                $statusSummary[$enum] = $query[$enum] ?? 0;
            }

            return response()->json(['status_summary' => $statusSummary], 200);
        }

        if ($queryParam === 'priority') {
            $query = Todo_list::groupBy('priority')->select('priority', DB::raw('count(*) as total'))->pluck('total', 'priority')->toArray();

            $enumPriority = ["low", "medium", "high"];
            $prioritySummary = [];

            foreach ($enumPriority as $enum) {
                $prioritySummary[$enum] = $query[$enum] ?? 0;
            }

            return response()->json(['priority_summary' => $prioritySummary], 200);
        }

        if ($queryParam === 'assignee') {
            $query = Todo_list::groupBy('assignee')
                ->select(
                    'assignee',
                    DB::raw('count(*) as total_todos'),
                    DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as total_pending_todos'),
                    DB::raw('SUM(CASE WHEN status = "completed" THEN time_tracked ELSE 0 END) as total_timetracked_completed_todos')
                )
                ->get()->toArray();
            $assigneeSummary = [];

            foreach ($query as $key => $value) {
                $assigneeSummary[$value['assignee']] = [
                    'total_todos' => $value['total_todos'],
                    'total_pending_todos' => $value['total_pending_todos'],
                    'total_timetracked_completed_todos' => $value['total_timetracked_completed_todos']
                ];
            }

            return response()->json(['assignee_summary' => $assigneeSummary], 200);
        }
    }

    function export(Request $request)
    {
        $query = Todo_list::query();

        if ($request->filled('title')) {
            $title = $request->input('title');
            $query->where('title', 'like', '%' . $title . '%');
        }

        if ($request->filled('assignee')) {
            $getAssignee = array_filter(array_map('trim', explode(',', $request->input('assignee'))));
            if (!empty($getAssignee)) {
                $query->whereIn('assignee', $getAssignee);
            }
        }

        if ($request->filled('start') || $request->filled('end')) {
            $start = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : null;
            $end = $request->input('end') ? Carbon::parse($request->input('end'))->startOfDay() : null;

            if ($start && $end) {
                $query->whereBetween('due_date', [$start, $end]);
            } elseif ($start) {
                $query->where('due_date', '>=', $start);
            } elseif ($end) {
                $query->where('due_date', '<=', $end);
            }
        }

        if ($request->filled('min') || $request->filled('max')) {
            $min = $request->input('min') !== null ? $request->input('min') : null;
            $max = $request->input('max') !== null ? $request->input('max') : null;

            if (!is_null($min) && !is_null($max)) {
                $query->whereBetween('time_tracked', [$min, $max]);
            } elseif (!is_null($min)) {
                $query->where('time_tracked', '>=', $min);
            } elseif (!is_null($max)) {
                $query->where('time_tracked', '<=', $max);
            }
        }

        if ($request->filled('status')) {
            $getStatus = array_filter(array_map('trim', explode(',', $request->input('status'))));
            if (!empty($getStatus)) {
                $query->whereIn('status', $getStatus);
            }
        }

        if ($request->filled('priority')) {
            $getPriority = array_filter(array_map('trim', explode(',', $request->input('priority'))));
            if (!empty($getPriority)) {
                $query->whereIn('priority', $getPriority);
            }
        }

        $getTodos = $query->get(['title', 'assignee', 'due_date', 'time_tracked', 'status', 'priority']);

        $totalCount = $getTodos->count();
        $totalTime = $getTodos->sum('time_tracked');

        //handle export
        $rows = $getTodos->map(function ($todo) {
            return [
                $todo->title,
                $todo->assignee,
                Carbon::parse($todo->due_date)->format('Y-m-d'),
                $todo->time_tracked ? $todo->time_tracked : 0,
                $todo->status,
                $todo->priority,
            ];
        })->toArray();

        $rows[] = [
            "Total Todos: {$totalCount}",
            "",
            "",
            $totalTime,
            "",
            "",
        ];

        $headings = ['Title', 'Assignee', 'Due Date', 'Time Tracked', 'Status', 'Priority'];

        return Excel::download(new TodoListExport($rows, $headings), 'todo_report.xlsx');
    }
}
