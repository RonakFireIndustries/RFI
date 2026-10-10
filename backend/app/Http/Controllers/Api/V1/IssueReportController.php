<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\IssueReport;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\Access;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class IssueReportController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('issue-reports.view');

        $user = $request->user();
        $canManage = $this->canManage($user);

        $query = IssueReport::with(['user', 'screenshots']);

        if (! $canManage) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        $reports = $query->latest()->paginate($perPage);

        return $this->success('Issue reports retrieved', [
            'issue_reports' => $reports->items(),
            'pagination' => [
                'total' => $reports->total(),
                'per_page' => $reports->perPage(),
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('issue-reports.create');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'in:bug,ui,performance,data,access,other'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,critical'],
            'page_url' => ['nullable', 'string', 'max:2048'],
            'screenshots' => ['nullable', 'array', 'max:10'],
            'screenshots.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        $report = DB::transaction(function () use ($validated, $request) {
            $report = IssueReport::create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? 'other',
                'priority' => $validated['priority'] ?? 'medium',
                'page_url' => $validated['page_url'] ?? null,
                'status' => 'open',
            ]);

            if ($request->hasFile('screenshots')) {
                foreach ($request->file('screenshots') as $file) {
                    $path = $file->store('issue-reports/' . $report->id, 'public');
                    $report->screenshots()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => $request->user()->id,
                    ]);
                }
            }

            return $report;
        });

        $this->notifyReviewers($report);

        return $this->success('Issue reported', ['issue_report' => $report->load(['user', 'screenshots'])], [], 201);
    }

    public function show(Request $request, IssueReport $issueReport): JsonResponse
    {
        $this->authorize('issue-reports.view');

        $this->ensureCanView($request->user(), $issueReport);

        return $this->success('Issue report retrieved', [
            'issue_report' => $issueReport->load(['user', 'screenshots', 'resolver']),
        ]);
    }

    public function update(Request $request, IssueReport $issueReport): JsonResponse
    {
        $this->authorize('issue-reports.update');

        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:open,in_progress,resolved,closed'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high,critical'],
            'category' => ['sometimes', 'string', 'in:bug,ui,performance,data,access,other'],
            'admin_notes' => ['nullable', 'string', 'max:10000'],
        ]);

        if (($validated['status'] ?? null) === 'resolved') {
            $validated['resolved_at'] = now();
            $validated['resolved_by'] = $request->user()->id;
        }

        $issueReport->update($validated);

        return $this->success('Issue report updated', [
            'issue_report' => $issueReport->fresh()->load(['user', 'screenshots', 'resolver']),
        ]);
    }

    public function destroy(Request $request, IssueReport $issueReport): JsonResponse
    {
        $this->authorize('issue-reports.delete');

        $user = $request->user();
        if (! $this->canManage($user) && $issueReport->user_id !== $user->id) {
            return $this->error('You can only delete your own reports', [], 403);
        }

        foreach ($issueReport->screenshots as $screenshot) {
            Storage::disk('public')->delete($screenshot->file_path);
        }
        $issueReport->delete();

        return $this->success('Issue report deleted');
    }

    /**
     * A user can manage (list/update all) reports when they are a super admin
     * or have been granted issue-reports.update through the Access Control panel.
     */
    protected function canManage(User $user): bool
    {
        if (Access::isSuperAdmin($user)) {
            return true;
        }

        try {
            return $user->hasPermissionTo('issue-reports.update');
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    protected function ensureCanView(User $user, IssueReport $report): void
    {
        if ($this->canManage($user) || $report->user_id === $user->id) {
            return;
        }

        abort(403, 'You can only view your own reports');
    }

    protected function notifyReviewers(IssueReport $report): void
    {
        try {
            $reviewers = User::permission('issue-reports.update')->get();
        } catch (\Throwable $e) {
            $reviewers = collect();
        }

        foreach ($reviewers->unique('id') as $reviewer) {
            if ($reviewer->id === $report->user_id) {
                continue;
            }

            NotificationService::create(
                $reviewer->id,
                'New issue report',
                $report->title,
                'warning',
                '/dashboard/issue-reports',
            );
        }
    }
}
