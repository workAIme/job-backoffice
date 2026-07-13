<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyCreateRequest;
use App\Http\Requests\CompanyUpdateRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    public array $industries = ['Technology', 'Finance', 'Healthcare', 'Education', 'Manufacturing', 'Retail', 'Other'];

    public function index(Request $request)
    {
        $query = Company::latest();

        if ($request->boolean('archived')) {
            $query->onlyTrashed();
        }

        $companies = $query->paginate(10)->onEachSide(1);

        return view('company.index', compact('companies'));
    }

    public function create()
    {
        $industries = $this->industries;

        return view('company.create', compact('industries'));
    }

    public function store(CompanyCreateRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $owner = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['owner_password']),
                'role' => 'company-owner',
            ]);

            Company::create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'industry' => $validated['industry'],
                'website' => $validated['website'],
                'ownerId' => $owner->id,
            ]);
        });

        return redirect()->route('companies.index')->with('success', 'Company created successfully!');
    }

    public function show(?string $id = null)
    {
        $company = $this->getCompany($id);

        return view('company.show', compact('company'));
    }

    public function edit(?string $id = null)
    {
        $company = $this->getCompany($id);
        $industries = $this->industries;

        return view('company.edit', compact('company', 'industries'));
    }

    public function update(CompanyUpdateRequest $request, ?string $id = null)
    {
        $validated = $request->validated();
        $company = $this->getCompany($id);

        DB::transaction(function () use ($company, $validated) {
            $company->update([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'industry' => $validated['industry'],
                'website' => $validated['website'],
            ]);

            $ownerData = ['name' => $validated['owner_name']];

            if (! empty($validated['owner_password'])) {
                $ownerData['password'] = Hash::make($validated['owner_password']);
            }

            $company->owner->update($ownerData);
        });

        if ($request->user()->role === 'company-owner') {
            return redirect()->route('my-company.show')->with('success', 'Company updated successfully!');
        }

        if ($request->query('redirectToList') === 'false') {
            return redirect()->route('companies.show', $company->id)->with('success', 'Company updated successfully!');
        }

        return redirect()->route('companies.index')->with('success', 'Company updated successfully!');
    }

    public function destroy(string $id)
    {
        Company::findOrFail($id)->delete();

        return redirect()->route('companies.index')->with('success', 'Company archived successfully!');
    }

    public function restore(string $id)
    {
        Company::withTrashed()->findOrFail($id)->restore();

        return redirect()
            ->route('companies.index', ['archived' => 'true'])
            ->with('success', 'Company restored successfully!');
    }

    private function getCompany(?string $id = null): Company
    {
        if (auth()->user()->role === 'company-owner') {
            return Company::where('ownerId', auth()->id())->firstOrFail();
        }

        abort_if($id === null, 404);

        return Company::findOrFail($id);
    }
}
