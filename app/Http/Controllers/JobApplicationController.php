<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationUpdateRequest;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->accessibleQuery($request->boolean('archived'));
        $jobApplications = $query->latest()->paginate(10)->onEachSide(1);

        return view('job-application.index', compact('jobApplications'));
    }

    public function show(string $id)
    {
        $jobApplication = $this->findAccessible($id);

        return view('job-application.show', compact('jobApplication'));
    }

    public function edit(string $id)
    {
        $jobApplication = $this->findAccessible($id);

        return view('job-application.edit', compact('jobApplication'));
    }

    public function update(JobApplicationUpdateRequest $request, string $id)
    {
        $jobApplication = $this->findAccessible($id);
        $jobApplication->update([
            'status' => $request->validated('status'),
        ]);

        if ($request->query('redirectToList') === 'false') {
            return redirect()->route('job-applications.show', $id)->with('success', 'Applicant status updated successfully!');
        }

        return redirect()->route('job-applications.index')->with('success', 'Applicant status updated successfully');
    }

    public function destroy(string $id)
    {
        $this->findAccessible($id)->delete();

        return redirect()->route('job-applications.index')->with('success', 'Applicant archived successfully');
    }

    public function restore(string $id)
    {
        $this->findAccessible($id, true)->restore();

        return redirect()
            ->route('job-applications.index', ['archived' => 'true'])
            ->with('success', 'Applicant restored successfully');
    }

    private function accessibleQuery(bool $withTrashed = false): Builder
    {
        $query = $withTrashed ? JobApplication::withTrashed() : JobApplication::query();

        if (auth()->user()->role === 'company-owner') {
            $companyId = auth()->user()->company?->id;
            abort_if($companyId === null, 403, 'No company is assigned to this account.');

            $query->whereHas('jobVacancy', function (Builder $jobQuery) use ($companyId) {
                $jobQuery->where('companyId', $companyId);
            });
        }

        return $query;
    }

    private function findAccessible(string $id, bool $withTrashed = false): JobApplication
    {
        return $this->accessibleQuery($withTrashed)->findOrFail($id);
    }
}
