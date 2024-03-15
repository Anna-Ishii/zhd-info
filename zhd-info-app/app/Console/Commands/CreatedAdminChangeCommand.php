<?php

namespace App\Console\Commands;

use App\Models\Manual;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreatedAdminChangeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:created-admin-change-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'アカウント統一化による作成者・更新者を変更';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('start');
        
        // 


        // messageを更新
        $messages = Message::all();
        $manual = Manual::all();
        DB::beginTransaction();
        try {
            $this->info('アカウントと業態を紐付けます');
            DB::insert('insert into admin_organization1 (admin_id, organization1_id, created_at)
                            select min_id, admin.organization1_id, ?
                            from (
                                SELECT min(id) as min_id , employee_code

                                FROM admin

                                GROUP BY employee_code
                            ) as table1
                            left join admin on admin.employee_code = table1.employee_code
                        ', [now()]);
            
            $this->info('業務連絡の作成者・更新者を変更します。');

            $sub = DB::table('admin')
                ->select([
                    'a_o1.admin_id as a_o1_id',
                    'employee_code'
                ])
                ->join('admin_organization1 as a_o1', 'a_o1.admin_id', '=', 'admin.id');

            foreach ($messages as $key => $ms) {
                $created_admin = $ms->create_user;
                $updated_admin = $ms->updated_user;

                if($created_admin) {
                    $after_created_admin = DB::table('admin')
                                            ->joinSub($sub, 'a_o1', 'admin.employee_code', 'a_o1.employee_code')
                                            ->where('admin.id', '=', $created_admin->id)
                                            ->value('a_o1.a_o1_id');
                    $ms->create_admin_id = $after_created_admin;
                }
                
                if ($updated_admin) {
                    $after_updated_admin = DB::table('admin')
                                            ->joinSub($sub, 'a_o1', 'admin.employee_code', 'a_o1.employee_code')
                                            ->where('admin.id', '=', $updated_admin->id)
                                            ->value('a_o1.a_o1_id');
                    $ms->updated_admin_id = $after_updated_admin;
                }
                $ms->save();
            }

            $this->info('マニュアルの作成者・更新者を変更します。');

            foreach ($manual as $key => $ml) {
                $created_admin = $ml->create_user;
                $updated_admin = $ml->updated_user;

                if ($created_admin) {
                    $after_created_admin = DB::table('admin')
                        ->joinSub($sub, 'a_o1', 'admin.employee_code', 'a_o1.employee_code')
                        ->where('admin.id', '=', $created_admin->id)
                        ->value('a_o1.a_o1_id');
                    $ml->create_admin_id = $after_created_admin;
                }

                if ($updated_admin) {
                    $after_updated_admin = DB::table('admin')
                        ->joinSub($sub, 'a_o1', 'admin.employee_code', 'a_o1.employee_code')
                        ->where('admin.id', '=', $updated_admin->id)
                        ->value('a_o1.a_o1_id');
                    $ml->updated_admin_id = $after_updated_admin;
                }
                $ml->save();
            }

            $this->info('重複していたアカウントを削除します。');
            DB::delete('delete from admin 
                        where admin.id not in (
	                        select admin_id from admin_organization1
                            )
                        ');

            $this->info('完了しました');
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }

        $this->info('end');
    }
}
