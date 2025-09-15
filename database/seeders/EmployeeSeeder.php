<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = new Employee();
        $data->name = 'Rizki';
        $data->job_position = 'Teknisi';
        $data->save();

        $data = new Employee();
        $data->name = 'Fajri';
        $data->job_position = 'Kenek';
        $data->save();

        $data = new Employee();
        $data->name = 'Dinda';
        $data->job_position = 'Marketing';
        $data->save();
    }
}
