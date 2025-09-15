<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Branch;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::paginate(10);
        $branches = Branch::all();

        return view('employee.index', compact('employees', 'branches'));
    }

    public function create()
    {
        return view('employee.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required','string','max:255'],
            'job_position' => ['nullable','string','max:255'],
        ]);

        Employee::create($data);

        return redirect()->route('employee.index')
            ->with('success','Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee)
    {
        return view('employee.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employee.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name'         => ['required','string','max:255'],
            'job_position' => ['nullable','string','max:255'],
        ]);

        $employee->update($data);

        return redirect()->route('employee.index')
            ->with('success','Karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employee.index')
            ->with('success','Karyawan berhasil dihapus.');
    }
}
