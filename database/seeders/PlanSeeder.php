<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userPlans = [
            ['uuid'=>Str::uuid()->toString(),'stripe_plan_id'=>'','plan_name'=>'Siler Monthly','plan_price'=>99.99,'plan_type'=>1,'status'=>1,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s")],
            ['uuid'=>Str::uuid()->toString(),'stripe_plan_id'=>'','plan_name'=>'Gold Yearly','plan_price'=>399.99,'plan_type'=>2,'status'=>1,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s")]
        ];

        if(plan::count() == 0){

            plan::insert($userPlans);
        }
    }
}
