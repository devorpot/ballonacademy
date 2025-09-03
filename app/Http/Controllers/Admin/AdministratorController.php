<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\CourseActivity;
use App\Models\EvaluationUser;
use App\Models\VideoActivity;
use App\Models\Student;
use App\Models\Activity;
use Illuminate\Support\Facades\Storage;

class AdministratorController extends Controller
{
    public function index()
    {
        // Actividad de cursos
        $courseActivities = CourseActivity::with([
                'course:id,title',
                'user:id,name,email',
                'user.profile:id,user_id,nickname,profile_image',
            ])
            ->latest('activity_date')
            ->take(6)
            ->get()
            ->map(fn ($it) => [
                'id'            => $it->id,
                'activity'      => $it->activity,
                'ended'         => (bool) $it->ended,
                'activity_date' => optional($it->activity_date)->toDateTimeString() ?? $it->created_at?->toDateTimeString(),
                'course'        => $it->course ? ['id' => $it->course->id, 'title' => $it->course->title] : null,
                'user'          => self::userTiny($it->user),
            ]);

        // Envíos de evaluaciones
        $evaluationUsers = EvaluationUser::with([
                'user:id,name,email',
                'user.profile:id,user_id,nickname,profile_image',
                'course:id,title',
                'video:id,title',
                'evaluation:id,title',
            ])
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn ($it) => [
                'id'         => $it->id,
                'status'     => $it->status?->value,
                'status_lbl' => $it->status_label ?? null,
                'updated_at' => $it->updated_at?->toDateTimeString(),
                'course'     => $it->course ? ['id' => $it->course->id, 'title' => $it->course->title] : null,
                'video'      => $it->video ? ['id' => $it->video->id, 'title' => $it->video->title] : null,
                'evaluation' => $it->evaluation ? ['id' => $it->evaluation->id, 'title' => $it->evaluation->title] : null,
                'user'       => self::userTiny($it->user),
            ]);

        // Videos vistos (event = ended)
        $videoActivities = VideoActivity::with([
                'user:id,name,email',
                'user.profile:id,user_id,nickname,profile_image',
                'course:id,title',
                'video:id,title',
            ])
            ->where('event', 'ended')
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn ($it) => [
                'id'         => $it->id,
                'updated_at' => $it->updated_at?->toDateTimeString(),
                'course'     => $it->course ? ['id' => $it->course->id, 'title' => $it->course->title] : null,
                'video'      => $it->video ? ['id' => $it->video->id, 'title' => $it->video->title] : null,
                'user'       => self::userTiny($it->user),
            ]);

        // Últimos alumnos
        $students = Student::with([
                'user:id,name,email',
                'user.profile:id,user_id,nickname,profile_image',
            ])
            ->latest('created_at')
            ->take(6)
            ->get()
            ->map(fn ($it) => [
                'id'         => $it->id,
                'created_at' => $it->created_at?->toDateTimeString(),
                'country'    => $it->country,
                'user'       => self::userTiny($it->user),
            ]);

        // Actividad general
        $activities = Activity::with([
                'user:id,name,email',
                'user.profile:id,user_id,nickname,profile_image',
                'course:id,title',
                'video:id,title',
                'evaluation:id,title',
            ])
            ->latest('created_at')
            ->take(8)
            ->get()
            ->map(fn ($it) => [
                'id'          => $it->id,
                'type'        => $it->type,
                'description' => $it->description,
                'created_at'  => $it->created_at?->toDateTimeString(),
                'course'      => $it->course ? ['id' => $it->course->id, 'title' => $it->course->title] : null,
                'video'       => $it->video ? ['id' => $it->video->id, 'title' => $it->video->title] : null,
                'evaluation'  => $it->evaluation ? ['id' => $it->evaluation->id, 'title' => $it->evaluation->title] : null,
                'user'        => self::userTiny($it->user),
            ]);

        return Inertia::render('Admin/Dashboard/Index', [
            'courseActivities' => $courseActivities,
            'evaluationUsers'  => $evaluationUsers,
            'videoActivities'  => $videoActivities,
            'students'         => $students,
            'activities'       => $activities,
        ]);
    }

    /**
     * Compacta los datos del usuario leyendo nickname y avatar desde Profile.
     */
    private static function userTiny($user)
    {
        if (!$user) return null;

        $profile  = $user->profile ?? null;
        $nickname = $profile->nickname ?? $user->name ?? (str_contains($user->email, '@') ? strstr($user->email, '@', true) : $user->email);

        // avatar desde profile_image (storage), o profile_photo_url (Jetstream/Fortify), o null
        $avatar = null;
        if ($profile?->profile_image) {
            $avatar = Storage::url($profile->profile_image);
        } elseif (property_exists($user, 'profile_photo_url') && $user->profile_photo_url) {
            $avatar = $user->profile_photo_url;
        }

        return [
            'id'       => $user->id,
            'nickname' => $nickname,
            'name'     => $user->name,
            'email'    => $user->email,
            'avatar'   => $avatar,
        ];
    }
}
