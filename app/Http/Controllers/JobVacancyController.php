<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobVacancyCreateRequest;
use App\Http\Requests\JobVacancyUpdateRequest;
use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JobVacancyController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->accessibleQuery($request->boolean('archived'));
        $jobVacancies = $query->latest()->paginate(10)->onEachSide(1);

        return view('job-vacancy.index', compact('jobVacancies'));
    }

    public function create()
    {
        $companies = $this->availableCompanies();
        $jobCategories = JobCategory::all();

        return view('job-vacancy.create', compact('companies', 'jobCategories'));
    }

    public function store(JobVacancyCreateRequest $request)
    {
        $validated = $request->validated();

        if ($request->user()->role === 'company-owner') {
            $validated['companyId'] = $this->ownerCompanyId();
        }

        JobVacancy::create($validated);

        return redirect()->route('job-vacancies.index')->with('success', 'Job vacancy created successfully');
    }

    public function show(string $id)
    {
        $jobVacancy = $this->findAccessible($id);

        return view('job-vacancy.show', compact('jobVacancy'));
    }

    public function edit(string $id)
    {
        $jobVacancy = $this->findAccessible($id);
        $companies = $this->availableCompanies();
        $jobCategories = JobCategory::all();

        return view('job-vacancy.edit', compact('jobVacancy', 'companies', 'jobCategories'));
    }

    public function update(JobVacancyUpdateRequest $request, string $id)
    {
        $jobVacancy = $this->findAccessible($id);
        $validated = $request->validated();

        if ($request->user()->role === 'company-owner') {
            $validated['companyId'] = $this->ownerCompanyId();
        }

        $jobVacancy->update($validated);

        if ($request->query('redirectToList') === 'false') {
            return redirect()->route('job-vacancies.show', $id)->with('success', 'Job vacancy updated successfully!');
        }

        return redirect()->route('job-vacancies.index')->with('success', 'Job vacancy updated successfully');
    }

    public function destroy(string $id)
    {
        $this->findAccessible($id)->delete();

        return redirect()->route('job-vacancies.index')->with('success', 'Job vacancy deleted successfully');
    }

    public function restore(string $id)
    {
        $this->findAccessible($id, true)->restore();

        return redirect()
            ->route('job-vacancies.index', ['archived' => 'true'])
            ->with('success', 'Job vacancy restored successfully');
    }

    private function accessibleQuery(bool $withTrashed = false): Builder
    {
        $query = $withTrashed ? JobVacancy::withTrashed() : JobVacancy::query();

        if (auth()->user()->role === 'company-owner') {
            $query->where('companyId', $this->ownerCompanyId());
        }

        return $query;
    }

    private function findAccessible(string $id, bool $withTrashed = false): JobVacancy
    {
        return $this->accessibleQuery($withTrashed)->findOrFail($id);
    }

    private function availableCompanies()
    {
        if (auth()->user()->role === 'company-owner') {
            return Company::whereKey($this->ownerCompanyId())->get();
        }

        return Company::all();
    }

    private function ownerCompanyId(): string
    {
        $companyId = auth()->user()->company?->id;

        abort_if($companyId === null, 403, 'No company is assigned to this account.');

        return $companyId;
    }
}
