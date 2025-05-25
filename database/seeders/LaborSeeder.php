<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Labour;
use App\Models\LaborDayWorking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaborSeeder extends Seeder
{
    public function run()
    {
        // Fetch a user with 'User' account_type
        $user_id = User::where('account_type', 'User')->pluck('uuid')->first();
        DB::beginTransaction();
        try {
            // Create or update a labor record
            $create = [
                'user_id' => $user_id,
                'work_title' => 'Mistry',
                'labor_name' => 'Rahul Kumar',
                'phone' => 9874563210,
                'status' => 'Active',
                'description' => 'Create dummy labor day working records',
                // 'image_url',
                // 'image_public_id',
            ];
            
            $labor = Labour::updateOrCreate(
                ['user_id' => $user_id],
                $create
            );
            
            $day_list = [
                ['day_name' => 'Sunday', 'day_number' => 0, 'start_time' => '08:00', 'end_time' => '17:20', 'break_minute' => 60, 'per_hour_amount' => 100],
                ['day_name' => 'Monday', 'day_number' => 1, 'start_time' => '09:00', 'end_time' => '17:40', 'break_minute' => 45, 'per_hour_amount' => 80],
                ['day_name' => 'Tuesday', 'day_number' => 2, 'start_time' => '09:30', 'end_time' => '18:30', 'break_minute' => 75, 'per_hour_amount' => 75],
            ];
            
            foreach ($day_list as $info) {
                $per_mint_amount = $info['per_hour_amount'] / 60;
                
                $start = Carbon::createFromFormat('H:i', $info['start_time']);
                $end = Carbon::createFromFormat('H:i', $info['end_time']);
                $total_minutes = $end->diffInMinutes($start);
                $working_minutes = ($total_minutes - $info['break_minute']);
                $working_hour = (int) ($working_minutes / 60) . ':' . (int) $working_minutes - ((int)($working_minutes / 60) * 60);
                $day_amount = $working_minutes * $per_mint_amount;
                
                $create =[
                    'labour_id' => $labor->id,
                    'day_number' => $info['day_number'],
                    'day_name' => $info['day_name'],
                    'start_time' => $info['start_time'],
                    'end_time' => $info['end_time'],
                    'break_minute' => $info['break_minute'],
                    'working_hour' => $working_hour,
                    'per_hour_amount' => $info['per_hour_amount'],
                    'day_amount' => round($day_amount),
                ];
                
                $working =  LaborDayWorking::updateOrCreate(['labour_id' => $labor->id ,'day_number' => $info['day_number']], $create);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}
