<?php

namespace Database\Seeders;

use App\Models\ResponsibleUnit;
use Illuminate\Database\Seeder;

class ResponsibleUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [

            /*
            |--------------------------------------------------------------------------
            | Academic Colleges
            |--------------------------------------------------------------------------
            */

            ['name' => 'College of Arts and Sciences', 'acronym' => 'CAS', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_COLLEGE],
            ['name' => 'College of Business and Accountancy', 'acronym' => 'CBA', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_COLLEGE],
            ['name' => 'College of Education', 'acronym' => 'COED', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_COLLEGE],
            ['name' => 'College of Engineering, Architecture and Technology', 'acronym' => 'CETA', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_COLLEGE],
            ['name' => 'College of Criminal Justice Education', 'acronym' => 'CCJE', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_COLLEGE],
            ['name' => 'College of Allied Health Sciences', 'acronym' => 'CAHS', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_COLLEGE],
            ['name' => 'College of Maritime Education', 'acronym' => 'COME', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_COLLEGE],

            /*
            |--------------------------------------------------------------------------
            | Administrative & Support Offices
            |--------------------------------------------------------------------------
            */

            ['name' => 'Corporate Planning and Development', 'acronym' => 'CPAD', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Quality Management System Office', 'acronym' => 'QMSO', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Quality Management System', 'acronym' => 'QMS', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Facilities Management Department', 'acronym' => 'FMD', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Finance and Accounting Department', 'acronym' => 'FAD', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Information and Communications Technology Department', 'acronym' => 'ICTD', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Human Resource Department', 'acronym' => 'HRD', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Data Privacy Office', 'acronym' => 'DPO', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Deputy Quality Management Representative', 'acronym' => 'DQMR', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Internal Quality Audit', 'acronym' => 'IQA', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],
            ['name' => 'Arts and Culture Development', 'acronym' => 'ACD', 'category' => ResponsibleUnit::CATEGORY_ADMIN_SUPPORT],

            /*
            |--------------------------------------------------------------------------
            | Academic Support Units
            |--------------------------------------------------------------------------
            */

            ['name' => 'Student Records Management Department', 'acronym' => 'SRMD', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_SUPPORT],
            ['name' => 'Academic Resource Center', 'acronym' => 'ARC', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_SUPPORT],
            ['name' => 'Center for Innovative Education', 'acronym' => 'CIE', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_SUPPORT],
            ['name' => 'Center for Research and Innovation', 'acronym' => 'CRI', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_SUPPORT],
            ['name' => 'International Affairs Department', 'acronym' => 'IAD', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_SUPPORT],
            ['name' => 'Community Extension Office', 'acronym' => 'COMEX', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_SUPPORT],
            ['name' => 'Student Affairs and Services Center', 'acronym' => 'SASC', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_SUPPORT],
            ['name' => 'Alumni Affairs', 'acronym' => 'ALUMNI', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_SUPPORT],
            ['name' => 'University of the Visayas Alumni Association Inc.', 'acronym' => 'UVAAI', 'category' => ResponsibleUnit::CATEGORY_ACADEMIC_SUPPORT],

            /*
            |--------------------------------------------------------------------------
            | Satellite Campuses
            |--------------------------------------------------------------------------
            */

            ['name' => 'Pardo Campus', 'acronym' => null, 'category' => ResponsibleUnit::CATEGORY_SATELLITE_CAMPUS],
            ['name' => 'Compostela Campus', 'acronym' => null, 'category' => ResponsibleUnit::CATEGORY_SATELLITE_CAMPUS],
            ['name' => 'Minglanilla Campus', 'acronym' => null, 'category' => ResponsibleUnit::CATEGORY_SATELLITE_CAMPUS],
            ['name' => 'Toledo Campus', 'acronym' => null, 'category' => ResponsibleUnit::CATEGORY_SATELLITE_CAMPUS],
            ['name' => 'Dalaguete Campus', 'acronym' => null, 'category' => ResponsibleUnit::CATEGORY_SATELLITE_CAMPUS],

            /*
            |--------------------------------------------------------------------------
            | Common Groups (uncategorized — not a real office, kept for
            | backward compatibility with existing KPI/action-plan rows that
            | already reference these)
            |--------------------------------------------------------------------------
            */

            ['name' => 'Academic Units', 'acronym' => null, 'category' => null],
            ['name' => 'All Academic Units', 'acronym' => null, 'category' => null],
            ['name' => 'Academic Units (Per College)', 'acronym' => null, 'category' => null],
            ['name' => 'Academic Units (Per Program)', 'acronym' => null, 'category' => null],
            ['name' => 'Non-Academic Units', 'acronym' => null, 'category' => null],
            ['name' => 'Academic and Non-Academic Units', 'acronym' => null, 'category' => null],
            ['name' => 'Quality Circles', 'acronym' => null, 'category' => null],
        ];

        foreach ($units as $unit) {
            ResponsibleUnit::updateOrCreate(
                ['name' => $unit['name']],
                [
                    'acronym' => $unit['acronym'],
                    'category' => $unit['category'],
                ]
            );
        }
    }
}
