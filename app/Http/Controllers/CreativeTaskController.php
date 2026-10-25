<?php

namespace App\Http\Controllers;

use App\Models\CreativeTask;
use App\Models\User;
use App\Notifications\CreativeTaskAssigned;
use App\Notifications\CreativeTaskDeliverableSubmitted;
use App\Notifications\CreativeTaskRevisionRequested;
use Illuminate\Http\Request;

class CreativeTaskController extends Controller
{
    private function authorizeAssigner(CreativeTask $task): void
    {
        abort_unless(
            auth()->user()->hasRole('superadmin') || (int) $task->assigned_by === (int) auth()->id(),
            403,
            'Hanya pemberi tugas yang bisa melakukan ini.'
        );
    }

    private function authorizeAssignee(CreativeTask $task): void
    {
        abort_unless(
            auth()->user()->hasRole('superadmin') || (int) $task->assigned_to === (int) auth()->id(),
            403,
            'Tugas ini bukan milik Anda.'
        );
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = CreativeTask::with(['assigner', 'assignee']);

        if ($user->hasRole('strategi_pengembangan') && !$user->hasRole('superadmin')) {
            $query->where('assigned_by', $user->id);
        } elseif ($user->hasAnyRole(['designer', 'videographer']) && !$user->hasRole('superadmin')) {
            $query->where('assigned_to', $user->id);
        }

        $tasks = $query->latest()->paginate(20);
        $tasksByStatus = $tasks->getCollection()->groupBy('status');

        return view('creative-tasks.index', compact('tasks', 'tasksByStatus'));
    }

    public function create()
    {
        $assignees = User::role(['designer', 'videographer'])->orderBy('name')->get();

        return view('creative-tasks.create', compact('assignees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'assigned_to'         => ['required', 'exists:users,id'],
            'title'               => ['required', 'string', 'max:255'],
            'brief'               => ['required', 'string'],
            'department_context'  => ['nullable', 'string', 'max:255'],
            'priority'            => ['required', 'in:low,normal,high'],
            'due_date'            => ['nullable', 'date'],
        ]);

        abort_unless(
            User::whereKey($data['assigned_to'])->role(['designer', 'videographer'])->exists(),
            422,
            'Tugas hanya bisa ditujukan ke Designer atau Videographer.'
        );

        $task = CreativeTask::create($data + [
            'assigned_by' => $request->user()->id,
            'status'      => 'in_progress',
        ]);

        $task->assignee->notify(new CreativeTaskAssigned($task));

        return redirect()->route('creative-tasks.show', $task)->with('success', 'Tugas berhasil dibuat dan ditugaskan.');
    }

    public function show(CreativeTask $creativeTask)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('superadmin')
                || (int) $creativeTask->assigned_by === (int) $user->id
                || (int) $creativeTask->assigned_to === (int) $user->id,
            403
        );

        $creativeTask->load(['assigner', 'assignee', 'deliverables.uploader']);

        return view('creative-tasks.show', ['task' => $creativeTask]);
    }

    public function storeDeliverable(Request $request, CreativeTask $creativeTask)
    {
        $this->authorizeAssignee($creativeTask);

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov', 'max:51200'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $path = $request->file('file')->store('creative-deliverables', 'public');

        $creativeTask->deliverables()->create([
            'version'     => $creativeTask->nextVersion(),
            'file_path'   => $path,
            'note'        => $data['note'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        $creativeTask->update(['status' => 'review']);
        $creativeTask->assigner->notify(new CreativeTaskDeliverableSubmitted($creativeTask));

        return back()->with('success', 'Deliverable berhasil diunggah, menunggu review.');
    }

    public function approve(CreativeTask $creativeTask)
    {
        $this->authorizeAssigner($creativeTask);
        abort_unless($creativeTask->status === 'review', 422, 'Tugas ini tidak sedang menunggu review.');

        $creativeTask->update(['status' => 'done']);

        return back()->with('success', 'Tugas ditandai selesai.');
    }

    public function requestRevision(Request $request, CreativeTask $creativeTask)
    {
        $this->authorizeAssigner($creativeTask);
        abort_unless($creativeTask->status === 'review', 422, 'Tugas ini tidak sedang menunggu review.');

        $data = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ], [
            'note.required' => 'Catatan revisi wajib diisi.',
        ]);

        $creativeTask->update(['status' => 'revision']);
        $creativeTask->assignee->notify(new CreativeTaskRevisionRequested($creativeTask, $data['note']));

        return back()->with('success', 'Revisi diminta ke ' . $creativeTask->assignee->name . '.');
    }
}
