<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseActivity extends Model
{
   protected $fillable = [
    'course_id', 'user_id', 'activity', 'ended', 'activity_date',
    ];

}
