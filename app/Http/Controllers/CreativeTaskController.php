<?php

namespace App\Http\Controllers;

use App\Models\CreativeTask;
use App\Models\User;
use App\Notifications\CreativeTaskAssigned;
use App\Notifications\CreativeTaskCancelled;
use App\Notifications\CreativeTaskDeliverableSubmitted;
use App\Notifications\CreativeTaskReassigned;
use App\Notifications\CreativeTaskUpdated;
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

    /** Hanya Strategi Pengembangan dan superadmin yang boleh membuat/mengubah/membatalkan tugas. */
    private function authorizeManager(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(['strategi_pengembangan', 'superadmin']),
            403,
            'Hanya Strategi Pengembangan yang dapat membuat atau mengelola tugas.'
        );
    }

    /** @return array<string,mixed> */
    private function validatedTask(Request $request): array
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

        return $data;
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
        $this->authorizeManager();
        $assignees = User::role(['designer', 'videographer'])->orderBy('name')->get();

        return view('creative-tasks.create', compact('assignees'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager();
        $data = $this->validatedTask($request);

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

    public function edit(CreativeTask $creativeTask)
    {
        $this->authorizeManager();
        $this->authorizeAssigner($creativeTask);
        abort_unless($creativeTask->isOpen(), 422, 'Tugas yang sudah selesai atau dibatalkan tidak dapat diubah.');

        $assignees = User::role(['designer', 'videographer'])->orderBy('name')->get();

        return view('creative-tasks.edit', ['task' => $creativeTask, 'assignees' => $assignees]);
    }

    public function update(Request $request, CreativeTask $creativeTask)
    {
        $this->authorizeManager();
        $this->authorizeAssigner($creativeTask);
        abort_unless($creativeTask->isOpen(), 422, 'Tugas yang sudah selesai atau dibatalkan tidak dapat diubah.');

        $data = $this->validatedTask($request);
        $oldAssignee = $creativeTask->assignee;
        $reassigned  = (int) $data['assigned_to'] !== (int) $creativeTask->assigned_to;

        $creativeTask->fill($data);
        $contentChanged = $creativeTask->isDirty(['title', 'brief', 'department_context', 'priority', 'due_date']);
        $creativeTask->save();
        $creativeTask->load('assignee', 'assigner');

        if ($reassigned) {
            $creativeTask->assignee->notify(new CreativeTaskAssigned($creativeTask));
            $oldAssignee?->notify(new CreativeTaskReassigned($creativeTask));
        } elseif ($contentChanged) {
            $creativeTask->assignee->notify(new CreativeTaskUpdated($creativeTask));
        }

        return redirect()->route('creative-tasks.show', $creativeTask)->with('success', 'Tugas berhasil diperbarui.');
    }

    public function cancel(Request $request, CreativeTask $creativeTask)
    {
        $this->authorizeManager();
        $this->authorizeAssigner($creativeTask);
        abort_unless($creativeTask->isOpen(), 422, 'Tugas ini sudah selesai atau sudah dibatalkan.');

        $data = $request->validate(
            ['cancel_reason' => ['required', 'string', 'max:1000']],
            ['cancel_reason.required' => 'Alasan pembatalan wajib diisi.']
        );

        $creativeTask->update([
            'status'        => 'cancelled',
            'cancel_reason' => $data['cancel_reason'],
            'cancelled_at'  => now(),
        ]);
        $creativeTask->assignee->notify(new CreativeTaskCancelled($creativeTask));

        return back()->with('success', 'Tugas dibatalkan.');
    }

    public function storeDeliverable(Request $request, CreativeTask $creativeTask)
    {
        $this->authorizeAssignee($creativeTask);
        abort_unless(in_array($creativeTask->status, ['in_progress', 'revision'], true), 422, 'Hasil kerja hanya bisa diunggah saat tugas In Progress atau Revisi.');

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
