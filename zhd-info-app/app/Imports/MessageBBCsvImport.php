<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\MessageCategory;
use App\Models\MessageTagMaster;
use App\Models\Shop;
use App\Models\User;
use App\Rules\Import\BrandRule;
use App\Rules\Import\ShopRule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MessageBBCsvImport implements
    ToCollection,
    WithCalculatedFormulas,
    WithStartRow,
    WithValidation,
    WithCustomCsvSettings
{
    use Importable;

    private $organization1;
    private $organization = [];
    private $brand = [];
    private $shop = [];
    private $category_list = [];

    public function __construct($organization1, $organization, $shop_list)
    {
        $this->organization1 = $organization1;
        $this->organization = $organization;
        $this->brand = array_merge(array_column($this->organization, 'brand_name'), ["全て"]);
        $this->shop = array_merge(array_column($shop_list, 'display_name'), ["全店"]);
        $this->category_list = MessageCategory::pluck('name')->toArray();
    }

    public function collection(Collection $rows)
    {

    }

    public function headingRow(): int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            '2' => ['required', Rule::in($this->category_list)],
            '3' => ['required'],
            '13' => ['required', new BrandRule(parameter: $this->brand)],
            '14' => ['required', new ShopRule(parameter: $this->shop)],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.int' => 'Noは数値である必要があります',
            '2.required' => 'カテゴリは必須項目です',
            '2.in' => 'カテゴリの項目が間違っています',
            '3.required' => 'タイトルは必須項目です',
            '13.required' => '対象業態は必須項目です',
            '14.required' => '配信店舗は必須項目です',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $failedRows = $validator->failed();

            // Noがない場合に業連ファイルが必須であることを確認
            foreach ($validator->getData() as $rowIndex => $row) {
                if (!isset($row[0]) && empty($row['4'])) {
                    $validator->errors()->add($rowIndex, '業連ファイルは必須項目です');
                }
            }

            // バリデーションエラーが発生した行番号をキューに追加
            $this->failed($failedRows);
        });
    }

    protected function failed($failedRows)
    {
        // キューにエラー情報を追加するなどの処理を実装
        // 例えば、キューに追加して別のジョブでエラー処理を行うなど
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => false,
            'input_encoding' => 'CP932'
        ];
    }

    private function getOrganizationForm($organization1_id)
    {
        return Shop::query()
            ->leftjoin('brands', 'brand_id', '=', 'brands.id')
            ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->distinct('brand_id')
            ->distinct('organization2_id')
            ->distinct('organization3_id')
            ->distinct('organization4_id')
            ->distinct('organization5_id')
            ->select(
                'brand_id',
                'brands.name as brand_name',
                'organization2_id',
                'organization2.name as organization2_name',
                'organization3_id',
                'organization3.name as organization3_name',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization5_id',
                'organization5.name as organization5_name'
            )
            ->where('shops.organization1_id', $organization1_id)
            ->get()
            ->toArray();
    }

    private function getShopForm($organization1_id)
    {
        return Shop::query()
            ->select([
                'shops.*',
                DB::raw("GROUP_CONCAT(brands.name SEPARATOR ',') as brand_names")
            ])
            ->join('brands', function ($join) {
                $join->on('shops.organization1_id', '=', 'brands.organization1_id')
                    ->on('shops.brand_id', '=', 'brands.id');
            })
            ->where('shops.organization1_id', $organization1_id)
            ->groupBy('shops.id')
            ->get()
            ->toArray();
    }

    private function tagImportParam(?array $tags): array
    {
        if (!isset($tags)) return [];

        $tags_pram = [];
        foreach ($tags as $key => $tag_name) {
            if (!isset($tag_name)) continue;
            $tag = MessageTagMaster::firstOrCreate(['name' => trim($tag_name, "\"")]);
            $tags_pram[] = $tag->id;
        }
        return $tags_pram;
    }

    private  function strToArray(?String $str): array
    {
        if (!isset($str)) return [];

        $array = explode(',', $str);

        $returnArray = [];
        foreach ($array as $key => $value) {
            $returnArray[] = trim($value, "\"");
        }

        return $returnArray;
    }

    private function getBrandAll(Int $org1_id): array
    {
        return Brand::query()
            ->where('organization1_id', '=', $org1_id)
            ->pluck('id')
            ->toArray();
    }

    private function targetUserParam($organizations): array
    {
        $shops_id = [];
        $target_user_data = [];

        // shopを取得する
        if (isset($organizations->organization_shops)) {
            $organization_shops = explode(',', $organizations->organization_shops);
            foreach ($organization_shops as $_shop_id) {
                foreach ($organizations->brand as $brand) {
                    $_shops_id = Shop::select('id')
                        ->where('id', $_shop_id)
                        ->where('brand_id', $brand)
                        ->get()
                        ->toArray();
                    $shops_id = array_merge($shops_id, $_shops_id);
                }
            }
        }

        // 取得したshopのリストからユーザーを取得する
        $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $organizations->target_roll)->get()->toArray();
        // ユーザーに業務連絡の閲覧権限を与える
        foreach ($target_users as $target_user) {
            $target_user_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
        }

        return $target_user_data;
    }

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

}
