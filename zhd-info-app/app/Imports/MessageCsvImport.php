<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\MessageCategory;
use App\Models\MessageTagMaster;
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

class MessageCsvImport implements
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
    private $organization5 = [];
    private $organization4 = [];
    private $organization3 = [];
    private $category_list = [];

    public function __construct($organization1, $organization)
    {
        $this->organization1 = $organization1;
        $this->organization = $organization;
        $this->brand = array_merge(array_column($this->organization, 'brand_name'), ["全て"]);
        // $this->organization5 = array_merge(array_column($this->organization, 'organization5_name'), ["全て"]);
        // $this->organization4 = array_merge(array_column($this->organization, 'organization4_name'), ["全て"]);
        // $this->organization3 = array_merge(array_column($this->organization, 'organization3_name'), ["全て"]);
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
            '0' => ['required'],
            '2' => ['nullable', Rule::in($this->category_list)],
            // '12' => ['nullable', new OrganizationRule(parameter: $this->brand)],
            // '13' => ['nullable', new OrganizationRule(parameter: $this->organization5)],
            // '14' => ['nullable', new OrganizationRule(parameter: $this->organization4)],
            // '15' => ['nullable', new OrganizationRule(parameter: $this->organization3)],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Noは必須です',
            '0.int' => 'Noは数値である必要があります',
            '2.in' => 'カテゴリの項目が間違っています'
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

    private function getOrg3All(Int $org1_id): array
    {
        return Shop::query()
            ->distinct('organization3.id')
            ->where('organization1_id', '=', $org1_id)
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->pluck('organization3.id')
            ->toArray();
    }

    private function getOrg4All(Int $org1_id): array
    {
        return Shop::query()
            ->distinct('organization4.id')
            ->where('organization1_id', '=', $org1_id)
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->pluck('organization4.id')
            ->toArray();
    }

    private function getOrg5All(Int $org1_id): array
    {
        return Shop::query()
            ->distinct('organization5.id')
            ->where('organization1_id', '=', $org1_id)
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->pluck('organization5.id')
            ->toArray();
    }

    private function targetUserParam($organizarions): array
    {
        $shops_id = [];
        $target_user_data = [];

        // organizationごとにshopを取得する
        if (isset($organizarions->organization['org5'])) {
            $_shops_id = Shop::select('id')
                ->whereIn('organization5_id', $organizarions->organization['org5'])
                ->whereIn('brand_id', $organizarions->brand)
                ->get()
                ->toArray();
            $shops_id = array_merge($shops_id, $_shops_id);
        }
        if (isset($organizarions->organization['org4'])) {
            $_shops_id = Shop::select('id')
                ->whereIn('organization4_id', $organizarions->organization['org4'])
                ->whereIn('brand_id', $organizarions->brand)
                ->get()
                ->toArray();
            $shops_id = array_merge($shops_id, $_shops_id);
        }
        if (isset($organizarions->organization['org3'])) {
            $_shops_id = Shop::select('id')
                ->whereIn('organization3_id', $organizarions->organization['org3'])
                ->whereIn('brand_id', $organizarions->brand)
                ->whereNull('organization4_id')
                ->whereNull('organization5_id')
                ->get()
                ->toArray();
            $shops_id = array_merge($shops_id, $_shops_id);
        }
        if (isset($organizarions->organization['org2'])) {
            $_shops_id = Shop::select('id')
                ->whereIn('organization2_id', $organizarions->organization['org2'])
                ->whereIn('brand_id', $organizarions->brand)
                ->whereNull('organization4_id')
                ->whereNull('organization5_id')
                ->get()
                ->toArray();
            $shops_id = array_merge($shops_id, $_shops_id);
        }

        // 取得したshopのリストからユーザーを取得する
        $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $organizarions->target_roll)->get()->toArray();
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
