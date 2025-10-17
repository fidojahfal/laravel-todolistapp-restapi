<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoListRequest;
use App\Models\Todo_list;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

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
}
