<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Shop;
use App\Models\User;
use App\Rules\Import\OrganizationRule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;

class MessageStoreCsvImport implements
    ToCollection,
    WithCalculatedFormulas,
    WithStartRow,
    WithValidation,
    WithCustomCsvSettings
{
    use Importable;

    private $organization1;
    private $shop_list = [];
    private $store_code = [];
    private $store_name = [];
    // private $category_list = [];

    public function __construct($organization1, $shop_list)
    {
        $this->organization1 = $organization1;
        $this->shop_list = $shop_list;
        $this->store_code = array_merge(array_column($this->shop_list, 'shop_code'));
        $this->store_name = array_merge(array_column($this->shop_list, 'display_name'));
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
            '0' => ['required'],
            '13' => ['nullable', new OrganizationRule(parameter: $this->store_code)],
            '14' => ['nullable', new OrganizationRule(parameter: $this->store_name)],
        ];
    }

    public function customValidationMessages()
    {
        return [
            // '0.required' => 'Noは必須です',
            // '0.int' => 'Noは数値である必要があります',
            '12.in' => '対象業態の項目が間違っています'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $failedRows = $validator->failed();

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
