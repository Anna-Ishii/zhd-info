<?php

namespace App\Console\Commands;

use App\Models\AdminRecipient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AdminRecipientsUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:admin-recipients-update-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '本部従業員のメールアドレスを追加するコマンド';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('start');

        // JP
        $admin_recipients_jp = [
            [
                'employee_number' => '1010910716',
                'name' => '北川　博基',
                'organization1_id' => 1,
                'email' => 'hiroki.kitagawa@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010097035',
                'name' => '馬場　康久',
                'organization1_id' => 1,
                'email' => 'yasuhisa.baba@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010091885',
                'name' => '萩原　浩太',
                'organization1_id' => 1,
                'email' => 'kouta.hagiwara@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010099179',
                'name' => '加藤　真人',
                'organization1_id' => 1,
                'email' => 'masato.kato@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '3401095014',
                'name' => '根岸　弘一',
                'organization1_id' => 1,
                'email' => '1095014@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3200004386',
                'name' => '尾上　和浩',
                'organization1_id' => 1,
                'email' => 'kazuhiro.ogami@jolly-pasta.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3200004654',
                'name' => '棚橋　邦生',
                'organization1_id' => 1,
                'email' => 'kunio.tanahashi@jolly-pasta.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3200005685',
                'name' => '新原　亜佳理',
                'organization1_id' => 1,
                'email' => 'akari.niihara@jolly-pasta.co.jp',
                'target' => false,
            ]
        ];

        // BB
        $admin_recipients_bb = [
            [
                'employee_number' => '1010910716',
                'name' => '北川　博基',
                'organization1_id' => 2,
                'email' => 'hiroki.kitagawa@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010097035',
                'name' => '馬場　康久',
                'organization1_id' => 2,
                'email' => 'yasuhisa.baba@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010091885',
                'name' => '萩原　浩太',
                'organization1_id' => 2,
                'email' => 'kouta.hagiwara@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010099179',
                'name' => '加藤　真人',
                'organization1_id' => 2,
                'email' => 'masato.kato@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '3401095014',
                'name' => '根岸　弘一',
                'organization1_id' => 2,
                'email' => '1095014@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3030001302',
                'name' => '高橋　あきほ',
                'organization1_id' => 2,
                'email' => 'akiho.takahashi@bigboyjapan.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3030000313',
                'name' => '横山　譲',
                'organization1_id' => 2,
                'email' => 'yokoyama@bigboyjapan.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3000400320',
                'name' => '田中　あき',
                'organization1_id' => 2,
                'email' => 'aki.tanaka@bigboyjapan.co.jp',
                'target' => false,
            ]
        ];

        // TAG
        $admin_recipients_tag = [
            [
                'employee_number' => '1010910716',
                'name' => '北川　博基',
                'organization1_id' => 3,
                'email' => 'hiroki.kitagawa@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010097035',
                'name' => '馬場　康久',
                'organization1_id' => 3,
                'email' => 'yasuhisa.baba@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010091885',
                'name' => '萩原　浩太',
                'organization1_id' => 3,
                'email' => 'kouta.hagiwara@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010099179',
                'name' => '加藤　真人',
                'organization1_id' => 3,
                'email' => 'masato.kato@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '3401095014',
                'name' => '根岸　弘一',
                'organization1_id' => 3,
                'email' => '1095014@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3600003159',
                'name' => '平川　守',
                'organization1_id' => 3,
                'email' => 'mamoru.hirakawa@tag-1.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3600093605',
                'name' => '中口　真弥',
                'organization1_id' => 3,
                'email' => 'shinya.nakaguchi@tag-1.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3600094248',
                'name' => '井波　雅輝',
                'organization1_id' => 3,
                'email' => 'masaki.inami@tag-1.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3600095788',
                'name' => '津吉　雄大',
                'organization1_id' => 3,
                'email' => 'yuudai.tsuyoshi@tag-1.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3600003135',
                'name' => '佐藤　学',
                'organization1_id' => 3,
                'email' => 'manabu.satou@tag-1.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3600093630',
                'name' => '森田　眞豊',
                'organization1_id' => 3,
                'email' => 'masatoyo.morita@tag-1.jp',
                'target' => false,
            ],
        ];

        // HY
        $admin_recipients_hy = [
            [
                'employee_number' => '1010910716',
                'name' => '北川　博基',
                'organization1_id' => 4,
                'email' => 'hiroki.kitagawa@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010097035',
                'name' => '馬場　康久',
                'organization1_id' => 4,
                'email' => 'yasuhisa.baba@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010091885',
                'name' => '萩原　浩太',
                'organization1_id' => 4,
                'email' => 'kouta.hagiwara@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010099179',
                'name' => '加藤　真人',
                'organization1_id' => 4,
                'email' => 'masato.kato@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '3401095014',
                'name' => '根岸　弘一',
                'organization1_id' => 4,
                'email' => '1095014@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3401081019',
                'name' => '佐藤　聖二',
                'organization1_id' => 4,
                'email' => 'ssatoh@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3401900031',
                'name' => '村松　昭雄',
                'organization1_id' => 4,
                'email' => 'akio.muramatsu@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '3401093043',
                'name' => '八島　利裕',
                'organization1_id' => 4,
                'email' => 'yashima@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3401078060',
                'name' => '加野岡　僚太郎',
                'organization1_id' => 4,
                'email' => 'ryoutarou.kanooka@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3401087106',
                'name' => '檜垣　栄一',
                'organization1_id' => 4,
                'email' => 'e.higaki@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3401085069',
                'name' => '高野　涼子',
                'organization1_id' => 4,
                'email' => 'rtakano@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3401082034',
                'name' => '平野　嘉一',
                'organization1_id' => 4,
                'email' => 'yhirano@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3401091032',
                'name' => '仁井田　亮',
                'organization1_id' => 4,
                'email' => '1091032@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3401088131',
                'name' => '舩津　貴義',
                'organization1_id' => 4,
                'email' => 't.funatsu@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3401088122',
                'name' => '篠原　義孝',
                'organization1_id' => 4,
                'email' => 'yshinohara@hanayayohei.co.jp',
                'target' => false,
            ],
        ];

        // ON
        $admin_recipients_on = [
            [
                'employee_number' => '1010910716',
                'name' => '北川　博基',
                'organization1_id' => 5,
                'email' => 'hiroki.kitagawa@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010097035',
                'name' => '馬場　康久',
                'organization1_id' => 5,
                'email' => 'yasuhisa.baba@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010091885',
                'name' => '萩原　浩太',
                'organization1_id' => 5,
                'email' => 'kouta.hagiwara@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '1010099179',
                'name' => '加藤　真人',
                'organization1_id' => 5,
                'email' => 'masato.kato@zensho.com',
                'target' => false,
            ],
            [
                'employee_number' => '3401095014',
                'name' => '根岸　弘一',
                'organization1_id' => 5,
                'email' => '1095014@hanayayohei.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '2G00005184',
                'name' => '岡田　宏幸',
                'organization1_id' => 5,
                'email' => 'hiroyuki.okada@olivenooka.jp',
                'target' => false,
            ],
            [
                'employee_number' => '2G00005280',
                'name' => '上部　雅弘',
                'organization1_id' => 5,
                'email' => 'masahiro.uwabe@olivenooka.jp',
                'target' => false,
            ],
        ];

        // NSS
        $admin_recipients_nss = [
            [
                'employee_number' => '1111111111',
                'name' => '本多　優士',
                'organization1_id' => 1,
                'email' => 'yhonda@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '2222222222',
                'name' => '尾関　知紘',
                'organization1_id' => 1,
                'email' => 'cozeki@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3333333333',
                'name' => '小嶺　賜恩',
                'organization1_id' => 1,
                'email' => 'skomine@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '1111111111',
                'name' => '本多　優士',
                'organization1_id' => 2,
                'email' => 'yhonda@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '2222222222',
                'name' => '尾関　知紘',
                'organization1_id' => 2,
                'email' => 'cozeki@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3333333333',
                'name' => '小嶺　賜恩',
                'organization1_id' => 2,
                'email' => 'skomine@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '1111111111',
                'name' => '本多　優士',
                'organization1_id' => 3,
                'email' => 'yhonda@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '2222222222',
                'name' => '尾関　知紘',
                'organization1_id' => 3,
                'email' => 'cozeki@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3333333333',
                'name' => '小嶺　賜恩',
                'organization1_id' => 3,
                'email' => 'skomine@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '1111111111',
                'name' => '本多　優士',
                'organization1_id' => 4,
                'email' => 'yhonda@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '2222222222',
                'name' => '尾関　知紘',
                'organization1_id' => 4,
                'email' => 'cozeki@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3333333333',
                'name' => '小嶺　賜恩',
                'organization1_id' => 4,
                'email' => 'skomine@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '1111111111',
                'name' => '本多　優士',
                'organization1_id' => 5,
                'email' => 'yhonda@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '2222222222',
                'name' => '尾関　知紘',
                'organization1_id' => 5,
                'email' => 'cozeki@nssx.co.jp',
                'target' => false,
            ],
            [
                'employee_number' => '3333333333',
                'name' => '小嶺　賜恩',
                'organization1_id' => 5,
                'email' => 'skomine@nssx.co.jp',
                'target' => false,
            ]
        ];

        try {
            DB::beginTransaction();

            // JP
            foreach ($admin_recipients_jp as $admin_recipient) {
                AdminRecipient::updateOrCreate(
                    ['employee_number' => $admin_recipient['employee_number'],
                        'organization1_id' => $admin_recipient['organization1_id']
                    ],
                    [
                        'name' => $admin_recipient['name'],
                        'email' => $admin_recipient['email'],
                        'target' => $admin_recipient['target']
                    ]
                );
            }

            // BB
            foreach ($admin_recipients_bb as $admin_recipient) {
                AdminRecipient::updateOrCreate(
                    ['employee_number' => $admin_recipient['employee_number'],
                        'organization1_id' => $admin_recipient['organization1_id']
                    ],
                    [
                        'name' => $admin_recipient['name'],
                        'email' => $admin_recipient['email'],
                        'target' => $admin_recipient['target']
                    ]
                );
            }

            // TAG
            foreach ($admin_recipients_tag as $admin_recipient) {
                AdminRecipient::updateOrCreate(
                    ['employee_number' => $admin_recipient['employee_number'],
                        'organization1_id' => $admin_recipient['organization1_id']
                    ],
                    [
                        'name' => $admin_recipient['name'],
                        'email' => $admin_recipient['email'],
                        'target' => $admin_recipient['target']
                    ]
                );
            }

            // HY
            foreach ($admin_recipients_hy as $admin_recipient) {
                AdminRecipient::updateOrCreate(
                    ['employee_number' => $admin_recipient['employee_number'],
                        'organization1_id' => $admin_recipient['organization1_id']
                    ],
                    [
                        'name' => $admin_recipient['name'],
                        'email' => $admin_recipient['email'],
                        'target' => $admin_recipient['target']
                    ]
                );
            }

            // ON
            foreach ($admin_recipients_on as $admin_recipient) {
                AdminRecipient::updateOrCreate(
                    ['employee_number' => $admin_recipient['employee_number'],
                        'organization1_id' => $admin_recipient['organization1_id']
                    ],
                    [
                        'name' => $admin_recipient['name'],
                        'email' => $admin_recipient['email'],
                        'target' => $admin_recipient['target']
                    ]
                );
            }

            // NSS
            foreach ($admin_recipients_nss as $admin_recipient) {
                AdminRecipient::updateOrCreate(
                    ['employee_number' => $admin_recipient['employee_number'],
                        'organization1_id' => $admin_recipient['organization1_id']
                    ],
                    [
                        'name' => $admin_recipient['name'],
                        'email' => $admin_recipient['email'],
                        'target' => $admin_recipient['target']
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->info('データベースエラーです。');
            $th_msg  = $th->getMessage();
            $this->info("$th_msg");
        }

        $this->info('end');
    }
}
