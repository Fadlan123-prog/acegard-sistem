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
        $data->name = 'Dhani';
        $data->job_position = 'Teknisi';
        $data->save();

        $data = new Employee();
        $data->name = 'Ferdi';
        $data->job_position = 'Teknisi';
        $data->save();

        $data = new Employee();
        $data->name = 'Agil';
        $data->job_position = 'Teknisi';
        $data->save();

        $data = new Employee();
        $data->name = 'Asep';
        $data->job_position = 'Teknisi';
        $data->save();

        $data = new Employee();
        $data->name = 'Rizan';
        $data->job_position = 'Teknisi';
        $data->save();

        $data = new Employee();
        $data->name = 'Asep K';
        $data->job_position = 'Teknisi';
        $data->save();

        $data = new Employee();
        $data->name = 'Imam';
        $data->job_position = 'Teknisi';
        $data->save();

        $data = new Employee();
        $data->name = 'Fajri';
        $data->job_position = 'Kenek';
        $data->save();

        $data = new Employee();
        $data->name = 'Alam';
        $data->job_position = 'Kenek';
        $data->save();
        $data = new Employee();
        $data->name = 'Ramdan';
        $data->job_position = 'Kenek';
        $data->save();
        $data = new Employee();
        $data->name = 'Faisal';
        $data->job_position = 'Kenek';
        $data->save();
        $data = new Employee();
        $data->name = 'Denny';
        $data->job_position = 'Kenek';
        $data->save();
        $data = new Employee();
        $data->name = 'Hendi';
        $data->job_position = 'Kenek';
        $data->save();

        $data = new Employee();
        $data->name = 'Dinda';
        $data->job_position = 'Marketing';
        $data->save();

        $data = new Employee();
        $data->name = 'Fadlan';
        $data->job_position = 'Marketing';
        $data->save();

        $data = new Employee();
        $data->name = 'Zidan';
        $data->job_position = 'Marketing';
        $data->save();

        $data = new Employee();
        $data->name = 'Adit';
        $data->job_position = 'Marketing';
        $data->save();
    }
}
