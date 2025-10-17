<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo_list extends Model
{
    use HasFactory;

    protected $fillable = ["title", "assignee", "due_date", "time_tracked", "status", "priority"];
}
